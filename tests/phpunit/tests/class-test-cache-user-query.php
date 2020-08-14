<?php

namespace KAGG\CacheUserQuery;

use tad\FunctionMocker\FunctionMocker;
use KAGG\Cache\Cache;

/**
 * Class TestCache_User_Query
 */
class Test_Cache_User_Query extends Cache_User_Query_TestCase {

	public function tearDown() {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function itAddsHooks() {
		$subject = \Mockery::mock( Cache_User_Query::class )->makePartial();

		\WP_Mock::expectActionAdded( 'clean_user_cache', [ $subject, 'clean_user_cache_action' ] );
		\WP_Mock::expectActionAdded( 'updated_user_meta', [ $subject, 'updated_user_meta_action' ] );
		\WP_Mock::expectFilterAdded( 'users_pre_query', [ $subject, 'users_pre_query' ], 10, 2 );

		$subject->add_hooks();
	}

	/**
	 * @test
	 */
	public function itFilters_users_pre_query() {
		global $wpdb;

		$subject = \Mockery::mock( Cache_User_Query::class )->makePartial();

		$total   = 3;
		$qv      = [
			'fields'      =>
				[
					0 => 'ID',
					1 => 'user_login',
					2 => 'display_name',
				],
			'count_total' => true,
		];
		$results = [
			0 =>
				(object) [
					'ID'           => '1',
					'user_login'   => 'kagg',
					'display_name' => 'kagg',
				],
			1 =>
				(object) [
					'ID'           => '5',
					'user_login'   => 'translator',
					'display_name' => 'translator',
				],
			2 =>
				(object) [
					'ID'           => '4',
					'user_login'   => 'user',
					'display_name' => 'user',
				],
		];
		$data    = [
			'results'     => $results,
			'total_users' => $total,
		];

		$user_query = \Mockery::mock( 'WP_User_Query' );
		$user_query->shouldReceive( 'get_total' )->with()->andReturn( $total );
		$user_query->shouldReceive( '__set' )->with( 'total_users', $total )->andReturn( $total );

		$user_query->query_vars    = $qv;
		$user_query->query_fields  = 'wp_users.ID,wp_users.user_login,wp_users.display_name';
		$user_query->query_from    = 'FROM wp_users INNER JOIN wp_usermeta ON ( wp_users.ID = wp_usermeta.user_id )';
		$user_query->query_where   = 'WHERE 1=1 AND (
  ( wp_usermeta.meta_key = \'wp_user_level\' AND wp_usermeta.meta_value != \'0\' )
)';
		$user_query->query_orderby = 'ORDER BY display_name ASC';
		$user_query->query_limit   = null;

		$request = "SELECT $user_query->query_fields $user_query->query_from $user_query->query_where $user_query->query_orderby $user_query->query_limit";

		$cache = \Mockery::mock( Cache::class );
		$cache->shouldReceive( 'get' )->with( md5( $request ), false )->once();
		$cache->shouldReceive( 'set' )->with( md5( $request ), $data, Cache_User_Query::class )->once();

		$this->set_protected_property( $subject, 'cache', $cache );

		$wpdb = \Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'get_results' )->with( $request )->andReturn( $results );
		$wpdb->shouldReceive( 'get_var' )->with( $user_query )->andReturn( $total );

		\WP_Mock::onFilter( 'found_users_query' )->with( 'SELECT FOUND_ROWS()', $user_query )->reply( $user_query );

		$subject->users_pre_query( null, $user_query );
	}

	/**
	 * @test
	 */
	public function itRuns_clean_user_cache_action() {
		$cache = \Mockery::mock( Cache::class );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		$subject = \Mockery::mock( Cache_User_Query::class )->makePartial();
		$this->set_protected_property( $subject, 'cache', $cache );

		$subject->clean_user_cache_action();
	}

	/**
	 * @test
	 */
	public function itUpdates_user_meta_action() {
		$cache = \Mockery::mock( Cache::class );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		$subject = \Mockery::mock( Cache_User_Query::class )->makePartial();
		$this->set_protected_property( $subject, 'cache', $cache );

		$subject->updated_user_meta_action();
	}
}
