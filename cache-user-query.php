<?php
/**
 * Plugin Name: Cache User Query
 * Description: MU-Plugin to cache user query. Useful on sites with big number of users.
 * Version: 1.0
 * Author: KAGG Design
 * Author URI: https://kagg.eu/en
 * License: GPL2
 * Requires at least: 4.4
 * Tested up to: 5.4
 * Requires PHP: 5.6
 *
 * @package KAGG\CacheUserQuery
 */

namespace KAGG\CacheUserQuery;

define( 'KAGG_CACHE_USER_QUERY', dirname( __FILE__ ) . '/cache-user-query' );

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
