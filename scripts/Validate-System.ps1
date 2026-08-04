[CmdletBinding()]
param(
    [string]$ProjectRoot = '',
    [switch]$SkipComposer
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $scriptRoot = $PSScriptRoot
    if ([string]::IsNullOrWhiteSpace($scriptRoot)) {
        $scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
    }
    if ([string]::IsNullOrWhiteSpace($scriptRoot)) {
        $scriptRoot = Join-Path (Get-Location) 'scripts'
    }
    $ProjectRoot = Split-Path -Parent $scriptRoot
}

$failures = 0
$warnings = 0
$pendingTasks = [System.Collections.Generic.List[string]]::new()

function Write-Result {
    param(
        [string]$Name,
        [bool]$Passed,
        [string]$Message,
        [switch]$Warning
    )

    if ($Passed) {
        Write-Host "[OK]   $Name - $Message" -ForegroundColor Green
        return
    }

    if ($Warning) {
        $script:warnings++
        Write-Host "[WARN] $Name - $Message" -ForegroundColor Yellow
        return
    }

    $script:failures++
    Write-Host "[FAIL] $Name - $Message" -ForegroundColor Red
}

function Add-PendingTask {
    param([string]$Task)

    if (-not $script:pendingTasks.Contains($Task)) {
        $script:pendingTasks.Add($Task)
    }
}

function Get-ProjectPath {
    param([string]$RelativePath)

    Join-Path $ProjectRoot $RelativePath
}

if (-not (Test-Path -LiteralPath $ProjectRoot)) {
    throw "No existe el proyecto: $ProjectRoot"
}

$requiredPaths = @(
    'app/bootstrap.php',
    'public/index.php',
    'composer.json',
    'config/config.example.php',
    'database/schema.sql',
    'scripts/Validate-System.ps1'
)
foreach ($requiredPath in $requiredPaths) {
    Write-Result "Ruta requerida: $requiredPath" (Test-Path -LiteralPath (Get-ProjectPath $requiredPath)) 'Ruta verificada.'
}

$composerPath = Get-ProjectPath 'composer.json'
$composer = $null
try {
    $composer = Get-Content -Raw -LiteralPath $composerPath | ConvertFrom-Json
    Write-Result 'composer.json válido' $true 'JSON válido.'
} catch {
    Write-Result 'composer.json válido' $false $_.Exception.Message
}

if ($null -ne $composer) {
    Write-Result 'Nombre del paquete' ($composer.name -eq 'pccurico/sistema-gestion-agricola') 'Identidad PCCURICO configurada.'
    Write-Result 'PHP requerido' ($composer.require.php -eq '^8.2') 'Requisito PHP 8.2 configurado.'
    Write-Result 'PSR-4 configurado' ($composer.autoload.'psr-4'.'AgroPCC\' -eq 'app/') 'Namespace existente conservado para compatibilidad.'
}

$bootstrapPath = Get-ProjectPath 'app/bootstrap.php'
$bootstrap = Get-Content -Raw -LiteralPath $bootstrapPath
Write-Result 'Bootstrap usa Composer' ($bootstrap -match [regex]::Escape('vendor/autoload.php')) 'Autoload de Composer encontrado.'
Write-Result 'Sesión PCCURICO' ($bootstrap -match [regex]::Escape('pccurico_session')) 'Identificador técnico actualizado.'

$phpFiles = @(Get-ChildItem -Path (Get-ProjectPath 'app') -Recurse -Filter *.php)
$psr4Errors = [System.Collections.Generic.List[string]]::new()
foreach ($file in $phpFiles) {
    $source = Get-Content -Raw -LiteralPath $file.FullName
    $namespace = [regex]::Match($source, '(?m)^namespace\s+([^;]+);')
    $class = [regex]::Match($source, '(?m)^\s*(?:final\s+|abstract\s+)?class\s+(\w+)')
    if ($namespace.Success -and $class.Success -and $namespace.Groups[1].Value.StartsWith('AgroPCC\')) {
        $expected = $namespace.Groups[1].Value.Substring(9).Replace('\', '/') + '/' + $class.Groups[1].Value + '.php'
        $actual = $file.FullName.Substring((Get-ProjectPath 'app').Length + 1).Replace('\', '/')
        if ($expected -cne $actual) {
            $psr4Errors.Add("$actual (esperado $expected)")
        }
    }
}
Write-Result 'Rutas PSR-4' ($psr4Errors.Count -eq 0) $(if ($psr4Errors.Count) { $psr4Errors -join '; ' } else { "$($phpFiles.Count) archivos revisados." })

$sourceExtensions = @('.php', '.js', '.json', '.ps1', '.md', '.sql', '.css', '.html', '.txt')
$sourceFiles = @(Get-ChildItem -Path $ProjectRoot -Recurse -File -Force | Where-Object {
    $_.Extension -in $sourceExtensions -and
    $_.FullName -notmatch '[\\/](\.git|dist|vendor|node_modules)[\\/]' -and
    $_.Name -ne 'Validate-System.ps1'
})

$forbiddenTechnicalIdentifiers = 'AgroPCC_session|AgroPCC-navigation|AgroPCC-'
$technicalMatches = @($sourceFiles | Select-String -Pattern $forbiddenTechnicalIdentifiers -CaseSensitive:$false)
Write-Result 'Identificadores técnicos anteriores' ($technicalMatches.Count -eq 0) $(if ($technicalMatches.Count) { 'Se encontraron identificadores obsoletos.' } else { 'No se encontraron.' })

$visibleBrandMatches = @($sourceFiles | Select-String -Pattern '<title>[^<]*(AgroPCC|Esperanza|Agricola-Esperanza)' -CaseSensitive:$false)
Write-Result 'Marca visible anterior' ($visibleBrandMatches.Count -eq 0) $(if ($visibleBrandMatches.Count) { 'Se encontraron títulos con la marca anterior.' } else { 'No se encontraron.' })

$customerMatches = @($sourceFiles | Where-Object { $_.FullName -notmatch '[\\/]config[\\/]config\.php$' } | Select-String -Pattern 'Agr[ií]cola Esperanza|Agricola-Esperanza|laesperanza' -CaseSensitive:$false)
Write-Result 'Referencias de cliente en código y documentación' ($customerMatches.Count -eq 0) $(if ($customerMatches.Count) { 'Se encontraron referencias de cliente.' } else { 'No se encontraron.' })

$privateConfig = Get-ProjectPath 'config/config.php'
if (Test-Path -LiteralPath $privateConfig) {
    $privateConfigText = Get-Content -Raw -LiteralPath $privateConfig
    if ($privateConfigText -match 'laesperanza|esperanza') {
        Add-PendingTask 'Actualizar manualmente config/config.php con la URL y base de datos de la instalación destino; cambiarlo automáticamente puede romper la conexión actual.'
        Write-Result 'Configuración local independiente del cliente' $false 'config/config.php conserva datos de una instalación local.' -Warning
    } else {
        Write-Result 'Configuración local independiente del cliente' $true 'No contiene referencias de cliente.'
    }
}

$legacyDist = @(Get-ChildItem -Path (Get-ProjectPath 'dist') -Force -ErrorAction SilentlyContinue | Where-Object { $_.Name -match '(?i)AgroPCC|agricola-?esperanza|esperanza' })
if ($legacyDist.Count -gt 0) {
    Add-PendingTask 'Eliminar los artefactos históricos de dist con marca anterior antes de distribuir el producto.'
    Write-Result 'Artefactos de distribución históricos' $false "$($legacyDist.Count) artefacto(s) requieren retiro manual." -Warning
} else {
    Write-Result 'Artefactos de distribución históricos' $true 'No se encontraron.'
}

$vendorAutoload = Get-ProjectPath 'vendor/autoload.php'
if (-not (Test-Path -LiteralPath $vendorAutoload)) {
    Add-PendingTask 'Ejecutar composer install --no-dev --optimize-autoloader antes de crear o subir una distribución.'
    Write-Result 'Autoload generado' $false 'vendor/autoload.php no existe.' -Warning
} else {
    Write-Result 'Autoload generado' $true 'vendor/autoload.php encontrado.'
}

if (-not $SkipComposer) {
    $composerCommand = Get-Command composer -ErrorAction SilentlyContinue
    if ($null -eq $composerCommand) {
        Add-PendingTask 'Instalar o habilitar Composer y PHP para ejecutar composer validate y composer dump-autoload -o.'
        Write-Result 'Composer disponible' $false 'Composer no está disponible en PATH.' -Warning
    } else {
        Push-Location $ProjectRoot
        try {
            & $composerCommand.Source validate
            Write-Result 'composer validate' ($LASTEXITCODE -eq 0) 'Comando ejecutado.'
            & $composerCommand.Source dump-autoload -o
            Write-Result 'composer dump-autoload -o' ($LASTEXITCODE -eq 0) 'Comando ejecutado.'
        } finally {
            Pop-Location
        }
    }
}

if ($pendingTasks.Count) {
    Write-Host ''
    Write-Host 'Tareas pendientes generadas:' -ForegroundColor Yellow
    foreach ($task in $pendingTasks) {
        Write-Host "- $task"
    }
}

Write-Host ''
if ($failures -gt 0) {
    Write-Host "Validación finalizada con $failures fallo(s) y $warnings advertencia(s)." -ForegroundColor Red
    exit 1
}

Write-Host "Validación finalizada correctamente con $warnings advertencia(s)." -ForegroundColor Green
