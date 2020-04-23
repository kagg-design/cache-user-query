<?php
/**
 * Bootstrap file for Cache User Query phpunit tests.
 *
 * @package WPML\CacheUserQuery
 */

define( 'PLUGIN_MAIN_FILE', realpath( __DIR__ . '/../../cache-user-query.php' ) );
define( 'PLUGIN_PATH', realpath( dirname( PLUGIN_MAIN_FILE ) ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', PLUGIN_PATH . '/../../' );
}

\WP_Mock::bootstrap();
