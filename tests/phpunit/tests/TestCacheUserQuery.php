<?php

namespace WPML\CacheUserQuery;

use tad\FunctionMocker\FunctionMocker;

/**
 * Class TestCacheUserQuery
 */
class TestCacheUserQuery extends CacheUserQueryTestCase {

	public function tearDown() {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_inits() {
		$subject = new CacheUserQuery();

		\WP_Mock::expectActionAdded( 'init', [ $subject, 'add_hooks' ] );

		$subject->init();
	}

	/**
	 * @test
	 */
	public function itAddsHooks() {
		$subject = new CacheUserQuery();

		FunctionMocker::replace(
			'class_exists',
			function ( $name ) {
				if ( 'SitePress' === $name ) {
					return true;
				}

				return null;
			}
		);

		\WP_Mock::expectActionAdded( 'clean_user_cache', [ $subject, 'cleanUserCacheAction' ] );
		\WP_Mock::expectActionAdded( 'updated_user_meta', [ $subject, 'updatedUserMetaAction' ] );
		\WP_Mock::expectFilterAdded( 'users_pre_query', [ $subject, 'usersPreQuery' ], 10, 2 );

		$subject->add_hooks();
	}

	/**
	 * @test
	 */
	public function itDoesNotAddHooksWhenNoSitePress() {
		$subject = new CacheUserQuery();

		\WP_Mock::expectActionNotAdded( 'clean_user_cache', [ $subject, 'cleanUserCacheAction' ] );
		\WP_Mock::expectActionNotAdded( 'updated_user_meta', [ $subject, 'updatedUserMetaAction' ] );
		\WP_Mock::expectFilterNotAdded( 'users_pre_query', [ $subject, 'usersPreQuery' ], 10, 2 );

		$subject->add_hooks();
	}

	/**
	 * @test
	 */
	public function itFiltersUsersPreQuery() {
		global $wpdb;

		$subject = new CacheUserQuery();

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

		$cache = \Mockery::mock( 'WPML_WP_Cache' );
		$cache->shouldReceive( 'get' )->with( md5( $request ), false )->once();
		$cache->shouldReceive( 'set' )->with( md5( $request ), $data, 'WPML\CacheUserQuery\CacheUserQuery' )->once();

		$wpdb = \Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'get_results' )->with( $request )->andReturn( $results );
		$wpdb->shouldReceive( 'get_var' )->with( $user_query )->andReturn( $total );

		\WP_Mock::onFilter( 'found_users_query' )->with( 'SELECT FOUND_ROWS()', $user_query )->reply( $user_query );

		\WP_Mock::userFunction( 'wpml_get_cache' )->with( CacheUserQuery::class )->once()->andReturn( $cache );

		$subject->usersPreQuery( null, $user_query );
	}

	/**
	 * @test
	 */
	public function itRunsCleanUserCacheAction() {
		$cache = \Mockery::mock( 'WPML_WP_Cache' );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		\WP_Mock::userFunction( 'wpml_get_cache' )->with( CacheUserQuery::class )->once()->andReturn( $cache );

		$subject = new CacheUserQuery();

		$subject->cleanUserCacheAction();
	}

	/**
	 * @test
	 */
	public function itUpdatesdUserMetaAction() {
		$cache = \Mockery::mock( 'WPML_WP_Cache' );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		\WP_Mock::userFunction( 'wpml_get_cache' )->with( CacheUserQuery::class )->once()->andReturn( $cache );

		$subject = new CacheUserQuery();

		$subject->updatedUserMetaAction();
	}
}
