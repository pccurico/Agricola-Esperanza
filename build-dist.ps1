[CmdletBinding()]
param(
    [string]$Version = 'v1.7.02',
    [switch]$SkipValidation,
    [switch]$SkipComposer
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$distRoot = Join-Path $projectRoot 'dist'
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$distributionName = "pccurico-agricola-$Version-$timestamp"
$distributionRoot = Join-Path $distRoot $distributionName

if ($Version -notmatch '^v\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$') {
    throw 'Version inválida. Usa el formato vMAJOR.MINOR.PATCH, por ejemplo v1.7.02.'
}

if (-not $SkipValidation) {
    & (Join-Path $projectRoot 'scripts/Validate-Project.ps1')
    if ($LASTEXITCODE -ne 0) {
        throw 'El build de producción se canceló: las validaciones del proyecto fallaron.'
    }
}

if (-not $SkipComposer) {
    $composerCommand = Get-Command composer -ErrorAction SilentlyContinue
    if ($null -eq $composerCommand) {
        throw 'Composer es requerido para generar una distribución de producción. Usa -SkipComposer solo si vendor fue generado previamente con --no-dev.'
    }
    Push-Location $projectRoot
    try {
        & $composerCommand.Source install --no-dev --prefer-dist --optimize-autoloader --no-interaction
        if ($LASTEXITCODE -ne 0) {
            throw 'Composer no pudo preparar las dependencias de producción.'
        }
    } finally {
        Pop-Location
    }
}

New-Item -ItemType Directory -Path $distRoot -Force | Out-Null
while (Test-Path $distributionRoot) {
    $timestamp = Get-Date -Format 'yyyyMMdd-HHmmss-fff'
    $distributionName = "pccurico-agricola-$Version-$timestamp"
    $distributionRoot = Join-Path $distRoot $distributionName
}

function Copy-DistributionItem {
    param(
        [Parameter(Mandatory)] [string]$RelativePath,
        [Parameter(Mandatory)] [string]$DestinationPath
    )

    $sourcePath = Join-Path $projectRoot $RelativePath
    if (-not (Test-Path $sourcePath)) {
        throw "No se encontró el archivo o directorio requerido: $RelativePath"
    }

    Copy-Item -Path $sourcePath -Destination $DestinationPath -Recurse -Force
}

New-Item -ItemType Directory -Path $distributionRoot -Force | Out-Null

Copy-DistributionItem '.htaccess' $distributionRoot
Copy-DistributionItem 'index.php' $distributionRoot
Copy-DistributionItem 'composer.json' $distributionRoot
Copy-DistributionItem 'vendor' $distributionRoot
Copy-DistributionItem 'app' $distributionRoot
Copy-DistributionItem 'public' $distributionRoot
Copy-DistributionItem 'database' $distributionRoot

$requiredDatabaseFiles = @('database/schema.sql', 'database/seeds/001_permissions.sql', 'database/seeds/002_system_catalogs.sql', 'database/seeds/003_catalog_values.sql', 'database/migrations/024_purchase_invoices.sql')
foreach ($relativePath in $requiredDatabaseFiles) {
    if (-not (Test-Path -LiteralPath (Join-Path $distributionRoot $relativePath))) {
        throw "El paquete no contiene el recurso de base de datos requerido: $relativePath"
    }
}

$configDestination = Join-Path $distributionRoot 'config'
New-Item -ItemType Directory -Path $configDestination -Force | Out-Null
Copy-DistributionItem 'config/.htaccess' $configDestination
Copy-DistributionItem 'config/config.example.php' $configDestination

$storageDestination = Join-Path $distributionRoot 'storage'
$logsDestination = Join-Path $storageDestination 'logs'
$uploadsDestination = Join-Path $storageDestination 'uploads'
New-Item -ItemType Directory -Path $logsDestination, $uploadsDestination -Force | Out-Null

$denyRules = @'
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Deny from all
</IfModule>
'@
Set-Content -Path (Join-Path $storageDestination '.htaccess') -Value $denyRules -Encoding ASCII
Set-Content -Path (Join-Path $logsDestination '.htaccess') -Value $denyRules -Encoding ASCII
Set-Content -Path (Join-Path $uploadsDestination '.htaccess') -Value $denyRules -Encoding ASCII

$forbiddenReleaseFiles = @('config/config.php', 'config/development.php', '.git', '.env', 'docs', 'scripts')
foreach ($relativePath in $forbiddenReleaseFiles) {
    if (Test-Path -LiteralPath (Join-Path $distributionRoot $relativePath)) {
        throw "El paquete de producción contiene un recurso no distribuible: $relativePath"
    }
}

$manifestPath = Join-Path $distributionRoot 'MANIFEST.sha256'
$manifest = Get-ChildItem -LiteralPath $distributionRoot -Recurse -File | Where-Object { $_.FullName -ne $manifestPath } | Sort-Object FullName | ForEach-Object {
    $relativePath = $_.FullName.Substring($distributionRoot.Length + 1).Replace('\', '/')
    "$((Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash)  $relativePath"
}
Set-Content -LiteralPath $manifestPath -Value $manifest -Encoding ASCII

$tarCommand = Get-Command tar -ErrorAction SilentlyContinue
if ($null -eq $tarCommand) {
    throw 'No se encontró tar. Instala Git Bash, WSL o una distribución de tar compatible para generar el paquete con permisos Unix.'
}

$archivePath = Join-Path $distRoot "$distributionName.tar.gz"
$tarHelp = (& $tarCommand.Source --help 2>&1 | Out-String)
$tarArguments = @('-czf', $archivePath, '-C', $distributionRoot, '.')
if ($tarHelp -match '--mode') {
    $tarArguments = @('--mode=a+rX,u+w') + $tarArguments
} else {
    Write-Warning 'La versión de tar disponible no admite --mode; cPanel aplicará sus permisos predeterminados al extraer el paquete.'
}
& $tarCommand.Source @tarArguments
if ($LASTEXITCODE -ne 0) {
    throw "No fue posible crear el archivo tar.gz. Código de salida: $LASTEXITCODE"
}

Write-Host "Distribución creada: $distributionRoot"
Write-Host "TAR.GZ creado: $archivePath"
Write-Host ''
Write-Host 'Permisos recomendados en cPanel:'
Write-Host '  El archivo tar.gz conserva permisos Unix cuando tar admite --mode.'
Write-Host '  Directorios esperados: 755 (incluido public/assets)'
Write-Host '  Archivos PHP, CSS, JS, SQL y .htaccess: 644'
Write-Host '  config y storage quedan en 755 para permitir la instalación inicial'
Write-Host '  No subas config/config.php: el instalador lo genera en el primer acceso.'
