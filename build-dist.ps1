[CmdletBinding()]
param(
    [string]$Version = '',
    [switch]$SkipValidation,
    [switch]$SkipComposer
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Assert-ProjectFile {
    param(
        [Parameter(Mandatory)] [string]$RelativePath,
        [string]$Description = ''
    )

    $sourcePath = Join-Path $projectRoot $RelativePath
    if (-not (Test-Path -LiteralPath $sourcePath)) {
        $message = "Falta el recurso crítico de producción: $RelativePath"
        if (-not [string]::IsNullOrWhiteSpace($Description)) {
            $message += " ($Description)"
        }
        throw $message
    }
}

function Assert-Directory {
    param(
        [Parameter(Mandatory)] [string]$RelativePath,
        [string]$Description = ''
    )

    $sourcePath = Join-Path $projectRoot $RelativePath
    if (-not (Test-Path -LiteralPath $sourcePath -PathType Container)) {
        $message = "Falta el directorio crítico de producción: $RelativePath"
        if (-not [string]::IsNullOrWhiteSpace($Description)) {
            $message += " ($Description)"
        }
        throw $message
    }
}

function Resolve-BuildVersion {
    param(
        [string]$Version
    )

    if (-not [string]::IsNullOrWhiteSpace($Version)) {
        return $Version
    }

    $configPaths = @(
        'config/config.php',
        'config/config.example.php'
    )

    foreach ($relativePath in $configPaths) {
        $sourcePath = Join-Path $projectRoot $relativePath
        if (-not (Test-Path -LiteralPath $sourcePath)) {
            continue
        }

        $content = Get-Content -LiteralPath $sourcePath -Raw
        if ($content -match "'version'\s*=>\s*'([^']*)'") {
            return $Matches[1]
        }
    }

    throw 'No se pudo determinar la versión de build. Indica -Version o define app.version en config/config.example.php.'
}

function Copy-DistributionItem {
    param(
        [Parameter(Mandatory)] [string]$RelativePath,
        [Parameter(Mandatory)] [string]$DestinationPath
    )

    $sourcePath = Join-Path $projectRoot $RelativePath
    if (-not (Test-Path -LiteralPath $sourcePath)) {
        throw "No se encontró el archivo o directorio requerido: $RelativePath"
    }

    Copy-Item -Path $sourcePath -Destination $DestinationPath -Recurse -Force
}

function Copy-DistributionRootContents {
    param(
        [string]$DestinationRoot
    )

    $excludedEntries = @(
        '.git', '.github', 'dist', 'docs', 'scripts', 'tests', 'node_modules', '.env', 'storage', 'config', 'build-dist.ps1'
    )

    Get-ChildItem -LiteralPath $projectRoot -Force | ForEach-Object {
        if ($excludedEntries -contains $_.Name) {
            return
        }

        $targetPath = Join-Path $DestinationRoot $_.Name
        Copy-Item -Path $_.FullName -Destination $targetPath -Recurse -Force
    }
}

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$distRoot = Join-Path $projectRoot 'dist'

$Version = Resolve-BuildVersion $Version
if ($Version -match '^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$') {
    $Version = "v$Version"
}

if ($Version -notmatch '^v\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$') {
    throw 'Version inválida. Usa el formato vMAJOR.MINOR.PATCH, por ejemplo v1.7.02.'
}

$distributionName = "pccurico-agricola"
$distributionRoot = Join-Path $distRoot $distributionName
$archivePath = Join-Path $distRoot "$distributionName-$Version.tar.gz"

if (-not $SkipValidation) {
    & (Join-Path $projectRoot 'scripts/Validate-Project.ps1')
    if ($LASTEXITCODE -ne 0) {
        throw 'El build de producción se canceló: las validaciones del proyecto fallaron.'
    }
}

Assert-ProjectFile 'composer.json' 'metadatos del paquete'
Assert-ProjectFile 'vendor/autoload.php' 'autoload de producción'
Assert-ProjectFile 'app/bootstrap.php' 'bootstrap del proyecto'
Assert-ProjectFile 'public/index.php' 'entrada pública'
Assert-ProjectFile 'index.php' 'entrada global'
Assert-ProjectFile 'config/config.example.php' 'plantilla de configuración'
Assert-ProjectFile 'config/.htaccess' 'protección de configuración'
Assert-ProjectFile '.htaccess' 'reglas raíz'
Assert-ProjectFile 'database/schema.sql' 'esquema base de instalación'
Assert-Directory 'database/seeds' 'datos iniciales de instalación'
Assert-Directory 'database/migrations' 'migraciones del esquema'
Assert-Directory 'app/Controllers' 'controladores del ERP'
Assert-Directory 'app/Core' 'clases base del ERP'
Assert-Directory 'app/Services' 'servicios del ERP'
Assert-Directory 'app/Views' 'vistas del ERP'
Assert-Directory 'public/assets' 'assets públicos'

$composerJsonPath = Join-Path $projectRoot 'composer.json'
$composerData = Get-Content -LiteralPath $composerJsonPath -Raw | ConvertFrom-Json
if (-not ($composerData.autoload -and $composerData.autoload.'psr-4')) {
    throw 'composer.json no define un autoload PSR-4 válido para el paquete de producción.'
}
if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'vendor/autoload.php'))) {
    throw 'vendor/autoload.php no está disponible para la distribución. La preparación de producción no puede continuar.'
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
if (Test-Path -LiteralPath $distributionRoot) {
    Remove-Item -LiteralPath $distributionRoot -Recurse -Force
}
if (Test-Path -LiteralPath $archivePath) {
    Remove-Item -LiteralPath $archivePath -Force
}
New-Item -ItemType Directory -Path $distributionRoot -Force | Out-Null

Copy-DistributionRootContents $distributionRoot

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

$forbiddenReleaseFiles = @('config/config.php', 'config/development.php', '.env', '.git', '.github', 'docs', 'scripts', 'tests', 'node_modules', 'build-dist.ps1')
foreach ($relativePath in $forbiddenReleaseFiles) {
    if (Test-Path -LiteralPath (Join-Path $distributionRoot $relativePath)) {
        throw "El paquete de producción contiene un recurso no distribuible: $relativePath"
    }
}

$manifestPath = Join-Path $distributionRoot 'MANIFEST.sha256'
$manifest = Get-ChildItem -LiteralPath $distributionRoot -Recurse -File | Where-Object { $_.FullName -ne $manifestPath } | Sort-Object FullName | ForEach-Object {
    $relativePath = $_.FullName.Substring($distributionRoot.Length + 1).Replace('\\', '/')
    "$((Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash)  $relativePath"
}
Set-Content -LiteralPath $manifestPath -Value $manifest -Encoding ASCII

$tarCommand = Get-Command tar -ErrorAction SilentlyContinue
if ($null -eq $tarCommand) {
    throw 'No se encontró tar. Instala Git Bash, WSL o una distribución de tar compatible para generar el paquete con permisos Unix.'
}

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
Write-Host '  Directorios esperados: 755 (inclido public/assets)'
Write-Host '  Archivos PHP, CSS, JS, SQL y .htaccess: 644'
Write-Host '  config y storage quedan en 755 para permitir la instalación inicial'
Write-Host '  No subas config/config.php: el instalador lo genera en el primer acceso.'
