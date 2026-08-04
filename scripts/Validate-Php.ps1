[CmdletBinding()]
param(
    [string]$ProjectRoot,
    [switch]$SkipLint
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
if ($PSVersionTable.PSVersion.Major -ge 5) {
    [Console]::OutputEncoding = [System.Text.UTF8Encoding]::new($false)
}
$dotSourced = $MyInvocation.InvocationName -eq '.'
$scriptDirectory = if (-not [string]::IsNullOrWhiteSpace($PSScriptRoot)) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Path }
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $scriptDirectory
}
$failures = 0
$warnings = 0

function Write-Result {
    param([string]$Name, [bool]$Passed, [string]$Message, [switch]$Warning)

    if ($Passed) {
        Write-Host "[OK]   $Name - $Message" -ForegroundColor Green
    } elseif ($Warning) {
        $script:warnings++
        Write-Host "[WARN] $Name - $Message" -ForegroundColor Yellow
    } else {
        $script:failures++
        Write-Host "[FAIL] $Name - $Message" -ForegroundColor Red
    }
}

$phpFiles = @(Get-ChildItem -LiteralPath (Join-Path $ProjectRoot 'app') -Filter '*.php' -Recurse -File -ErrorAction SilentlyContinue)
$phpFiles += @(Get-ChildItem -LiteralPath (Join-Path $ProjectRoot 'public') -Filter '*.php' -Recurse -File -ErrorAction SilentlyContinue)

Write-Result 'Archivos PHP encontrados' ($phpFiles.Count -gt 0) "$($phpFiles.Count) archivo(s)."

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$phpPath = if ($null -ne $phpCommand) { $phpCommand.Source } elseif (Test-Path -LiteralPath 'C:\wamp64\bin\php\php8.2.29\php.exe') { 'C:\wamp64\bin\php\php8.2.29\php.exe' } else { $null }
if ($SkipLint) {
    Write-Result 'PHP lint' $true 'Omitido por parámetro.' -Warning
} elseif ($null -eq $phpPath) {
    Write-Result 'PHP lint' $true 'PHP 8.2.29 no está disponible; se mantiene validación estática.' -Warning
} else {
    foreach ($file in $phpFiles) {
        & $phpPath -l $file.FullName *> $null
        Write-Result "PHP lint: $($file.Name)" ($LASTEXITCODE -eq 0) 'Sintaxis válida.'
    }
}

$bootstrap = Join-Path $ProjectRoot 'app/bootstrap.php'
$bootstrapText = if (Test-Path $bootstrap) { Get-Content -Raw -LiteralPath $bootstrap } else { '' }
$composerPath = Join-Path $ProjectRoot 'composer.json'
$composer = if (Test-Path $composerPath) {
    Get-Content -Raw -LiteralPath $composerPath | ConvertFrom-Json
} else {
    $null
}
Write-Result 'Bootstrap usa autoload de Composer' ($bootstrapText -match [regex]::Escape('vendor/autoload.php')) 'Referencia encontrada.'
Write-Result 'Autoload PSR-4 AgroPCC configurado' ($null -ne $composer -and $composer.autoload.'psr-4'.'AgroPCC\' -eq 'app/') 'Mapeo AgroPCC\\ a app/ encontrado.'

$setupController = Join-Path $ProjectRoot 'app/Controllers/SetupController.php'
$setupView = Join-Path $ProjectRoot 'app/Views/setup.php'
$setupText = if (Test-Path $setupController) { Get-Content -Raw -LiteralPath $setupController } else { '' }
$viewText = if (Test-Path $setupView) { Get-Content -Raw -LiteralPath $setupView } else { '' }

Write-Result 'SetupController procesa POST' ($setupText -match "REQUEST_METHOD.*POST") 'Entrada POST encontrada.'
Write-Result 'SetupController valida CSRF' ($setupText -match 'hash_equals' -and $setupText -match "\['csrf'\]") 'Validación CSRF encontrada.'
Write-Result 'Vista setup contiene formulario multipart' ($viewText -match 'method="post"' -and $viewText -match 'multipart/form-data') 'Formulario de instalación encontrado.'
Write-Result 'Vista setup conserva errores' ($viewText -match 'htmlspecialchars\(\$error') 'Salida escapada de errores encontrada.'

$unsafePatterns = @(
    'eval\(',
    'shell_exec\(',
    'system\(',
    'passthru\(',
    '(?<!->)\bexec\s*\('
)
foreach ($pattern in $unsafePatterns) {
    $matches = @($phpFiles | Select-String -Pattern $pattern -AllMatches)
    Write-Result "Patrón peligroso: $pattern" ($matches.Count -eq 0) 'No encontrado.'
}

if ($failures -gt 0) {
    Write-Host "Validación PHP finalizada con $failures fallo(s) y $warnings advertencia(s)." -ForegroundColor Red
    if ($dotSourced) { return }
    exit 1
}

Write-Host "Validación PHP finalizada correctamente con $warnings advertencia(s)." -ForegroundColor Green
if ($dotSourced) { return }
exit 0
