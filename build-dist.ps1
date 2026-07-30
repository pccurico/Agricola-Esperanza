[CmdletBinding()]
param(
    [string]$Version = 'v1.7.01'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$distRoot = Join-Path $projectRoot 'dist'
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$distributionName = "pccurico-agricola-$Version-$timestamp"
$distributionRoot = Join-Path $distRoot $distributionName

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
