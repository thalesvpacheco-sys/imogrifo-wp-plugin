<?php
namespace ImoGrifo;


/**
* Autoloader PSR-4 simplificado com tolerância para arquivos/pastas minúsculos.
*/
final class Autoloader
{
public static function register(): void
{
spl_autoload_register([static::class, 'autoload']);
}


public static function autoload(string $class): void
{
$prefix = __NAMESPACE__ . '\\';
if (strpos($class, $prefix) !== 0) { return; }


$relative = substr($class, strlen($prefix));
$relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);


// Caminho esperado (PascalCase)
$file = __DIR__ . DIRECTORY_SEPARATOR . $relative . '.php';
if (is_readable($file)) { require_once $file; return; }


// Fallback: tudo minúsculo
$fileLower = __DIR__ . DIRECTORY_SEPARATOR . strtolower($relative) . '.php';
if (is_readable($fileLower)) { require_once $fileLower; return; }


// Fallback por segmentos (caso subpastas minúsculas e arquivos PascalCase ou vice-versa)
$parts = explode(DIRECTORY_SEPARATOR, $relative);
$lowerParts = array_map('strtolower', $parts);
$mixed1 = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $lowerParts) . '.php';
if (is_readable($mixed1)) { require_once $mixed1; return; }
}
}