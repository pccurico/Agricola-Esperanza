[CmdletBinding()]
param(
    [string]$ProjectRoot,
    [string]$WampRoot = 'C:\wamp64'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
if ($PSVersionTable.PSVersion.Major -ge 5) {
    [Console]::OutputEncoding = [System.Text.UTF8Encoding]::new($false)
}
$dotSourced = $MyInvocation.InvocationName -eq '.'
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $scriptDirectory = if (-not [string]::IsNullOrWhiteSpace($PSScriptRoot)) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Path }
    $ProjectRoot = Split-Path -Parent $scriptDirectory
}

$failures = 0

function Write-Check {
    param([string]$Name, [bool]$Passed, [string]$Message)
    if ($Passed) {
        Write-Host "[OK]   $Name - $Message" -ForegroundColor Green
    } else {
        $script:failures++
        Write-Host "[FAIL] $Name - $Message" -ForegroundColor Red
    }
}

function Complete-Validation {
    param([int]$Code)
    if ($dotSourced) { return }
    exit $Code
}

$developmentConfig = Join-Path $ProjectRoot 'config/development.php'
Write-Check 'Configuración de desarrollo' (Test-Path -LiteralPath $developmentConfig) 'config/development.php encontrado.'
Write-Check 'Raíz del proyecto' (Test-Path -LiteralPath $ProjectRoot) $ProjectRoot
Write-Check 'WampServer64 instalado' (Test-Path -LiteralPath $WampRoot) $WampRoot
Write-Check 'Directorio PHP de WAMP' (Test-Path -LiteralPath (Join-Path $WampRoot 'bin/php')) (Join-Path $WampRoot 'bin/php')
Write-Check 'Directorio Apache de WAMP' (Test-Path -LiteralPath (Join-Path $WampRoot 'bin/apache')) (Join-Path $WampRoot 'bin/apache')
Write-Check 'Directorio MySQL de WAMP' (Test-Path -LiteralPath (Join-Path $WampRoot 'bin/mysql')) (Join-Path $WampRoot 'bin/mysql')

$phpExecutable = Join-Path $WampRoot 'bin\php\php8.2.29\php.exe'
$php = if (Test-Path -LiteralPath $phpExecutable) { Get-Item -LiteralPath $phpExecutable } else { Get-Command php -ErrorAction SilentlyContinue }
if ($null -eq $php) {
    Write-Check 'PHP 8.2.29 disponible' $false "No se encontró $phpExecutable ni php.exe en PATH."
} else {
    $phpPath = if ($php -is [System.Management.Automation.ApplicationInfo]) { $php.Source } else { $php.FullName }
    $versionOutput = (& $phpPath -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "." . PHP_RELEASE_VERSION;' 2>$null).Trim()
    Write-Check 'PHP 8.2.29 disponible' ($versionOutput -eq '8.2.29') $(if ($versionOutput) { "Versión detectada: $versionOutput" } else { 'No fue posible obtener la versión.' })
    $phpModules = @(& $phpPath -m 2>$null)
    $hasPdoMysql = @($phpModules | Where-Object { $_ -match '^pdo_mysql$' }).Count -gt 0
    $hasFileinfo = @($phpModules | Where-Object { $_ -match '^fileinfo$' }).Count -gt 0
    Write-Check 'Extensión PDO MySQL' $hasPdoMysql 'Extensión detectada.'
    Write-Check 'Extensión Fileinfo' $hasFileinfo 'Extensión detectada.'
}

$apacheService = Get-Service -Name 'wampapache64' -ErrorAction SilentlyContinue
$mysqlService = Get-Service -Name 'wampmysqld64' -ErrorAction SilentlyContinue
Write-Check 'Servicio Apache WAMP ejecutándose' ($null -ne $apacheService -and $apacheService.Status -eq 'Running') $(if ($null -eq $apacheService) { 'Servicio wampapache64 no encontrado.' } else { "Estado: $($apacheService.Status)" })
Write-Check 'Servicio MySQL WAMP ejecutándose' ($null -ne $mysqlService -and $mysqlService.Status -eq 'Running') $(if ($null -eq $mysqlService) { 'Servicio wampmysqld64 no encontrado.' } else { "Estado: $($mysqlService.Status)" })

$requiredPaths = @('app', 'public', 'config', 'database', 'storage')
foreach ($relativePath in $requiredPaths) {
    $path = Join-Path $ProjectRoot $relativePath
    Write-Check "Ruta del proyecto: $relativePath" (Test-Path -LiteralPath $path) $path
}

if ($failures -gt 0) {
    Write-Host "Entorno de desarrollo no validado: $failures fallo(s)." -ForegroundColor Red
    Complete-Validation 1
    return
}

Write-Host 'Entorno WampServer64 validado correctamente.' -ForegroundColor Green
Complete-Validation 0
