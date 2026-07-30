[CmdletBinding()]
param(
    [string]$ProjectRoot
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

function Get-SqlStatements {
    param([string]$Path)
    if (-not (Test-Path -LiteralPath $Path)) {
        return @()
    }
    return @((Get-Content -Raw -LiteralPath $Path) -split ';' | Where-Object { $_.Trim() -ne '' })
}

$schemaPath = Join-Path $ProjectRoot 'database/schema.sql'
$migrationsPath = Join-Path $ProjectRoot 'database/migrations'
$seedsPath = Join-Path $ProjectRoot 'database/seeds'

Write-Result 'schema.sql existe' (Test-Path -LiteralPath $schemaPath) 'Fuente oficial localizada.'
Write-Result 'Directorio de migraciones existe' (Test-Path -LiteralPath $migrationsPath) 'Directorio localizado.'
Write-Result 'Directorio de seeds existe' (Test-Path -LiteralPath $seedsPath) 'Directorio localizado.'

if (Test-Path -LiteralPath $schemaPath) {
    $schema = Get-Content -Raw -LiteralPath $schemaPath
    $schemaStatements = @(Get-SqlStatements $schemaPath)
    $tableMatches = [regex]::Matches($schema, '(?im)^\s*CREATE TABLE IF NOT EXISTS\s+`?([a-zA-Z0-9_]+)`?')
    $tables = @($tableMatches | ForEach-Object { $_.Groups[1].Value.ToLowerInvariant() } | Sort-Object -Unique)
    $foreignReferences = @([regex]::Matches($schema, '(?im)\bREFERENCES\s+`?([a-zA-Z0-9_]+)`?') | ForEach-Object { $_.Groups[1].Value.ToLowerInvariant() } | Sort-Object -Unique)

    Write-Result 'schema.sql contiene tablas' ($tables.Count -gt 0) "$($tables.Count) tabla(s) declarada(s)."
    Write-Result 'schema.sql termina en restauración de FK' ($schema.TrimEnd() -match 'SET FOREIGN_KEY_CHECKS\s*=\s*1;?$') 'Foreign key checks se restauran.'
    Write-Result 'schema.sql no usa ENUM de negocio' ($schema -notmatch '(?i)\bENUM\s*\(') 'No se detectaron ENUM rígidos.'
    Write-Result 'schema.sql tiene sentencias terminadas' ($schemaStatements.Count -gt 0) "$($schemaStatements.Count) sentencia(s) detectada(s)."

    $missingReferences = @($foreignReferences | Where-Object { $_ -notin $tables })
    Write-Result 'FK referencian tablas declaradas' ($missingReferences.Count -eq 0) $(if ($missingReferences.Count -eq 0) { 'Todas las tablas referenciadas existen.' } else { 'Faltan: ' + ($missingReferences -join ', ') })

    $expectedTables = @('companies', 'users', 'schema_migrations', 'system_catalogs', 'system_catalog_values', 'backup_records', 'restore_records')
    foreach ($table in $expectedTables) {
        Write-Result "Tabla requerida: $table" ($table -in $tables) 'Tabla declarada en schema.sql.'
    }
}

$migrations = @()
if (Test-Path -LiteralPath $migrationsPath) {
    $migrations = @(Get-ChildItem -LiteralPath $migrationsPath -Filter '*.sql' -File | Sort-Object Name)
}
Write-Result 'Migraciones numeradas' (($migrations.Count -eq 0) -or (@($migrations | Where-Object { $_.BaseName -notmatch '^\d{3}_[a-z0-9_]+$' }).Count -eq 0)) 'Nombres con formato NNN_descripcion.'

$numbers = @($migrations | ForEach-Object { [int]$_.BaseName.Substring(0, 3) })
$duplicates = @($numbers | Group-Object | Where-Object Count -gt 1)
Write-Result 'Migraciones sin números duplicados' ($duplicates.Count -eq 0) 'Cada versión tiene un número único.'

foreach ($migration in $migrations) {
    $statements = @(Get-SqlStatements $migration.FullName)
    Write-Result "Migración no vacía: $($migration.Name)" ($statements.Count -gt 0) 'Contiene SQL ejecutable.'
}

$seeds = @()
if (Test-Path -LiteralPath $seedsPath) {
    $seeds = @(Get-ChildItem -LiteralPath $seedsPath -Filter '*.sql' -File | Sort-Object Name)
}
Write-Result 'Seeds numerados' (($seeds.Count -eq 0) -or (@($seeds | Where-Object { $_.BaseName -notmatch '^\d{3}_[a-z0-9_]+$' }).Count -eq 0)) 'Nombres con formato NNN_descripcion.'
foreach ($seed in $seeds) {
    $seedText = Get-Content -Raw -LiteralPath $seed.FullName
    Write-Result "Seed no vacío: $($seed.Name)" ($seedText.Trim().Length -gt 0) 'Contiene SQL.'
    Write-Result "Seed idempotente: $($seed.Name)" ($seedText -match '(?i)NOT\s+EXISTS|INSERT\s+IGNORE|ON\s+DUPLICATE\s+KEY') 'Incluye patrón idempotente.' -Warning
}

if ($failures -gt 0) {
    Write-Host "Validación SQL finalizada con $failures fallo(s) y $warnings advertencia(s)." -ForegroundColor Red
    if ($dotSourced) { return }
    exit 1
}

Write-Host "Validación SQL finalizada correctamente con $warnings advertencia(s)." -ForegroundColor Green
if ($dotSourced) { return }
exit 0
