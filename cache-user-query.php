<?php
/**
 * Plugin Name: Cache User Query
 * Description: MU-Plugin to cache a user query. Useful on sites with a big number of users.
 * Version: 1.1
 * Author: KAGG Design
 * Author URI: https://kagg.eu/en
 * License: GPL2
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 *
 * @package kagg\cache-user-query
 */

namespace KAGG\CacheUserQuery;

define( 'KAGG_CACHE_USER_QUERY', __DIR__ );

require_once KAGG_CACHE_USER_QUERY . '/vendor/autoload.php';

$cache_user_query_plugin = new Cache_User_Query();
$cache_user_query_plugin->add_hooks();

define( 'KAGG_CACHE_USER_QUERY_GENERATE_USERS', false );

if ( KAGG_CACHE_USER_QUERY_GENERATE_USERS ) {
	( new Generate_Users() )->generate( 250 * 1000 );
}

define( 'KAGG_CACHE_USER_QUERY_DELETE_USERS', false );

if ( KAGG_CACHE_USER_QUERY_DELETE_USERS ) {
	( new Generate_Users() )->delete();
}
