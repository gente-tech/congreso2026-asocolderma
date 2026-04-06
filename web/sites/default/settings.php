<?php

/**
 * Drupal settings.php for PRODUCCIÓN
 */

$databases['default']['default'] = [
  'database' => 'congreso2026asocolderma',
  'username' => 'dermau',
  'password' => 'PasswordFuerte123!',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

/**
 * Hash salt (OBLIGATORIO en producción)
 */
$settings['hash_salt'] = 'catalogo_dermau_2026_hash_salt_super_seguro_987654321';

/**
 * Trusted host (OBLIGATORIO en producción)
 */
$settings['trusted_host_patterns'] = [
  '^catalogo\.dermau\.net$',
  '^congreso2026\.asocolderma\.org\.co$',
  '^congreso2026asocolderma\.haspe\.co$',
];

/**
 * Config sync directory (opcional pero recomendado)
 */
$settings['config_sync_directory'] = '../config/sync';

/**
 * Archivos públicos
 */
$settings['file_public_path'] = 'sites/default/files';

/**
 * Permisos por defecto
 */
$settings['file_chmod_directory'] = 0775;
$settings['file_chmod_file'] = 0664;

/**
 * Logging en verbose (puedes bajarlo luego)
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Mostrar errores (solo mientras depuramos)
 */
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
error_reporting(E_ALL);

/**
 * Cargar services.yml para activar twig debug
 */
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/services.yml';
