<?php
/**
 * Plugin Name: Cache User Query
 * Description: MU-Plugin to cache user query. Useful on sites with big number of users.
 * Version: 1.0
 * Author: OnTheGoSystems
 * Author URI: https://www.onthegosystems.com/
 * License: GPL2
 * Requires at least: 4.4
 * Tested up to: 5.4
 * Requires PHP: 5.6
 *
 * @package WPML\CacheUserQuery
 */

namespace WPML\CacheUserQuery;

define( 'WPML_CACHE_USER_QUERY', dirname( __FILE__ ) . '/cache-user-query' );

require_once WPML_CACHE_USER_QUERY . '/vendor/autoload.php';

$cache_user_query_plugin = new CacheUserQuery();
$cache_user_query_plugin->add_hooks();
