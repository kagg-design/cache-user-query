<?php
/**
 * Cache_User_Query class file.
 *
 * @package kagg\cache-user-query
 */

namespace KAGG\CacheUserQuery;

use KAGG\Cache\Cache;
use WP_User_Query;
use wpdb;

/**
 * Class Cache_User_Query
 *
 * @package kagg\cache-user-query
 */
class Cache_User_Query {

	/**
	 * Cache object.
	 *
	 * @var Cache
	 */
	protected Cache $cache;

	/**
	 * Cache_User_Query constructor.
	 */
	public function __construct() {
		$this->cache = new Cache( __CLASS__ );
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks(): void {
		add_action( 'clean_user_cache', [ $this, 'clean_user_cache_action' ] );
		add_action( 'updated_user_meta', [ $this, 'updated_user_meta_action' ] );
		add_filter( 'users_pre_query', [ $this, 'users_pre_query' ], 10, 2 );
	}

	/**
	 * Filter users_pre_query.
	 *
	 * @param array|mixed   $results    Return an array of user data to short-circuit WP's user query
	 *                                  or null to allow WP to run its normal queries.
	 * @param WP_User_Query $user_query The WP_User_Query instance (passed by reference).
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function users_pre_query( $results, WP_User_Query $user_query ): array {
		global $wpdb;

		$qv      =& $user_query->query_vars;
		$request = "SELECT $user_query->query_fields $user_query->query_from $user_query->query_where $user_query->query_orderby $user_query->query_limit";

		$cache_key = md5( $request );
		$found     = false;
		$data      = $this->cache->get( $cache_key, $found );

		if ( $found ) {
			$user_query->__set( 'total_users', $data['total_users'] );

			return $data['results'];
		}

		$user_query->request = $request;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		if ( is_array( $qv['fields'] ) || 'all' === $qv['fields'] ) {
			$results = (array) $wpdb->get_results( $user_query->request );
		} else {
			$results = $wpdb->get_col( $user_query->request );
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( isset( $qv['count_total'] ) && $qv['count_total'] ) {
			/**
			 * Filters SELECT FOUND_ROWS() query for the current WP_User_Query instance.
			 *
			 * @since 3.2.0
			 * @since 5.1.0 Added the `$this` parameter.
			 * @global wpdb         $wpdb WordPress database abstraction object.
			 *
			 * @param WP_User_Query $this The current WP_User_Query instance.
			 *
			 * @param string        $sql  The SELECT FOUND_ROWS() query for the current WP_User_Query.
			 */
			$found_users_query = apply_filters( 'found_users_query', 'SELECT FOUND_ROWS()', $user_query );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$user_query->__set( 'total_users', (int) $wpdb->get_var( $found_users_query ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}

		$data = [
			'results'     => $results,
			'total_users' => $user_query->get_total(),
		];

		$this->cache->set( $cache_key, $data );

		return $results;
	}

	/**
	 * Do action clean_user_cache.
	 */
	public function clean_user_cache_action(): void {
		$this->flush_cache();
	}

	/**
	 * Do action updated_user_meta.
	 */
	public function updated_user_meta_action(): void {
		$this->flush_cache();
	}

	/**
	 * Flush own cache.
	 */
	private function flush_cache(): void {
		$this->cache->flush_group_cache();
	}
}
