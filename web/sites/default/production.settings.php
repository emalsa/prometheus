<?php

/**
 * @file
 * Production
 */

$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => 'prometheus',
  'username' => 'prometheus',
  'password' => 'MzZaslaksd=22dsa3=dsadsa33=)=)*0091',
  'host' => '127.0.0.1',
  'port' => 3306,
  'prefix' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
];

$settings['deployment_identifier'] = '454545';
$settings['config_sync_directory'] = '../config/sync';

$settings['trusted_host_patterns'] = [
  '^prometheus\.ch$',
  '^.+\.nicastro\.io$',
  '^80.74.145.85\.io$',
  '^.+\.nicastro\.io$',
];

if (isset($GLOBALS['request']) && '/web/index.php' === $GLOBALS['request']->server->get('SCRIPT_NAME')) {
  $GLOBALS['request']->server->set('SCRIPT_NAME', '/index.php');
}

$settings['hash_salt'] = 'prometheus-2828-54885-halloeuhjdsahjkaskj';
$settings['file_temp_path'] = '/tmp';
$settings['file_public_path'] = 'sites/default/files';
$settings['file_public_base_url'] = 'https://prometheus.nicastro.io/sites/default/files';


// Don't show any error messages on the site (will still be shown in watchdog)
$config['system.logging']['error_level'] = 'verbose';

// Expiration of cached pages on Varnish to 15 min
$config['system.performance']['cache']['page']['max_age'] = 300;

// Aggregate CSS files on
$config['system.performance']['css']['preprocess'] = 1;

// Aggregate JavaScript files on
$config['system.performance']['js']['preprocess'] = 1;

// Disabling stage file proxy on production, with that the module can be enabled even on production
$config['stage_file_proxy.settings']['origin'] = FALSE;
