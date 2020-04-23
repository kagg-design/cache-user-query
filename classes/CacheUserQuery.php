<?php

namespace WPML\CacheUserQuery;

use WP_User_Query;
use wpdb;

class CacheUserQuery {

	public function add_hooks() {
		add_action( 'clean_user_cache', [ $this, 'cleanUserCacheAction' ] );
		add_action( 'updated_user_meta', [ $this, 'updatedUserMetaAction' ] );
		add_filter( 'users_pre_query', [ $this, 'cacheUserQuery' ], 10, 2 );
	}

	/**
	 * @param null          $results
	 * @param WP_User_Query $user_query
	 *
	 * @return array
	 */
	public function cacheUserQuery( $results, $user_query ) {
		global $wpdb;

		$qv      =& $user_query->query_vars;
		$request = "SELECT $user_query->query_fields $user_query->query_from $user_query->query_where $user_query->query_orderby $user_query->query_limit";

		$cache_key = $request;
		$cache     = wpml_get_cache( __CLASS__ );
		$found     = false;
		$data      = $cache->get( $cache_key, $found );
		if ( $found ) {
			$user_query->__set( 'total_users', $data['total_users'] );

			return $data['results'];
		}

		$user_query->request = $request;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		if ( is_array( $qv['fields'] ) || 'all' === $qv['fields'] ) {
			$results = $wpdb->get_results( $user_query->request );
		} else {
			$results = $wpdb->get_col( $user_query->request );
		}
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( isset( $qv['count_total'] ) && $qv['count_total'] ) {
			/**
			 * Filters SELECT FOUND_ROWS() query for the current WP_User_Query instance.
			 *
			 * @param string        $sql  The SELECT FOUND_ROWS() query for the current WP_User_Query.
			 * @param WP_User_Query $this The current WP_User_Query instance.
			 *
			 * @global wpdb         $wpdb WordPress database abstraction object.
			 *
			 * @since 3.2.0
			 * @since 5.1.0 Added the `$this` parameter.
			 */
			$found_users_query = apply_filters( 'found_users_query', 'SELECT FOUND_ROWS()', $user_query );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$user_query->__set( 'total_users', (int) $wpdb->get_var( $found_users_query ) );
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		$data = [
			'results'     => $results,
			'total_users' => $user_query->get_total(),
		];

		$cache->set( $cache_key, $data, __CLASS__ );

		return $results;
	}

	public function cleanUserCacheAction() {
		$this->flushCache();
	}

	public function updatedUserMetaAction() {
		$this->flushCache();
	}

	private function flushCache() {
		wpml_get_cache( __CLASS__ )->flush_group_cache();
	}
}
