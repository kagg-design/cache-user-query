<?php

namespace WPML\CacheUserQuery;

/**
 * Class TestCacheUserQuery
 */
class TestCacheUserQuery extends CacheUserQueryTestCase {

	/**
	 * @test
	 */
	public function itAddsHooks() {
		$subject = new CacheUserQuery();

		\WP_Mock::expectActionAdded( 'clean_user_cache', [ $subject, 'cleanUserCacheAction' ] );
		\WP_Mock::expectActionAdded( 'updated_user_meta', [ $subject, 'updatedUserMetaAction' ] );

		$subject->add_hooks();
	}

	/**
	 * @test
	 */
	public function itRunsCleanUserCacheAction() {
		$cache = \Mockery::mock( 'WPML_WP_Cache' );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		\WP_Mock::userFunction( 'wpml_get_cache' )->with( CacheUserQuery::class )->once()->
		andReturn( $cache );

		$subject = new CacheUserQuery();

		$subject->cleanUserCacheAction();
	}

	/**
	 * @test
	 */
	public function itUpdatesdUserMetaAction() {
		$cache = \Mockery::mock( 'WPML_WP_Cache' );
		$cache->shouldReceive( 'flush_group_cache' )->once();

		\WP_Mock::userFunction( 'wpml_get_cache' )->with( CacheUserQuery::class )->once()->
		andReturn( $cache );

		$subject = new CacheUserQuery();

		$subject->updatedUserMetaAction();
	}
}
