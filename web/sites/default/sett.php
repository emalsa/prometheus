<?php

/**
 * @file
 * Lagoon Drupal 8 configuration file.
 *
 * You should not edit this file, please use environment specific files!
 * They are loaded in this order:
 * - all.settings.php
 *   For settings that should be applied to all environments (dev, prod, staging, docker, etc).
 * - all.services.yml
 *   For services that should be applied to all environments (dev, prod, staging, docker, etc).
 * - production.settings.php
 *   For settings only for the production environment.
 * - production.services.yml
 *   For services only for the production environment.
 * - development.settings.php
 *   For settings only for the development environment (devevlopment sites, docker).
 * - development.services.yml
 *   For services only for the development environment (devevlopment sites, docker).
 * - settings.local.php
 *   For settings only for the local environment, this file will not be commited in GIT!
 * - services.local.yml
 *   For services only for the local environment, this file will not be commited in GIT!
 *
 */

$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => 'drupal9',
  'username' => 'drupal9',
  'password' => 'drupal9',
  'host' => 'database',
  'port' => 3306,
  'prefix' => '',
];
$settings['hash_salt'] = 'whatever-i-like-29292929';
$settings['config_sync_directory'] = '../config/sync';


// Show all error messages on the site
$config['system.logging']['error_level'] = 'all';

// Disable Google Analytics from sending dev GA data.
$config['google_analytics.settings']['account'] = 'UA-XXXXXXXX-YY';

$settings['redis.connection']['interface'] = 'PhpRedis';
$settings['redis.connection']['host'] = 'redis';
$settings['redis.connection']['port'] = 6379;

$settings['cache_prefix']['default'] = getenv('LAGOON_PROJECT') . '_' . getenv('LAGOON_GIT_SAFE_BRANCH');

// Services for all environments
if (file_exists(__DIR__ . '/all.services.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/all.services.yml';
}

// Last: this servers specific settings files.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
// Last: This server specific services file.
if (file_exists(__DIR__ . '/services.local.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/services.local.yml';
}
