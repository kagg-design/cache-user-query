<?php
/**
 * Bootstrap file for Cache User Query phpunit tests.
 *
 * @package kagg\cache-user-query
 */

use tad\FunctionMocker\FunctionMocker;

define( 'PLUGIN_MAIN_FILE', realpath( __DIR__ . '/../../cache-user-query.php' ) );
define( 'PLUGIN_PATH', realpath( dirname( PLUGIN_MAIN_FILE ) ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', PLUGIN_PATH . '/../../' );
}

FunctionMocker::init(
	[
		'blacklist'             => [
			realpath( PLUGIN_PATH ),
		],
		'whitelist'             => [
			realpath( PLUGIN_PATH . '/cache-user-query.php' ),
			realpath( PLUGIN_PATH . '/classes' ),
		],
		'redefinable-internals' => [
			'class_exists',
		],
	]
);

\WP_Mock::bootstrap();
