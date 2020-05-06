<?php
/**
 * Cache class file.
 *
 * @package KAGG\Cache
 */

namespace KAGG\Cache;

/**
 * Class Cache
 */
class Cache {

	/**
	 * Key name under which array of all group keys is stored.
	 *
	 * @var string
	 */
	const KEYS = __CLASS__ . '__group_keys';

	/**
	 * Group name.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * Cache constructor.
	 *
	 * @param string $group Optional. Where the cache contents are grouped. Default empty.
	 */
	public function __construct( $group = '' ) {
		$this->group = $group;
	}

	/**
	 * Retrieves the cache contents from the cache by key and group.
	 *
	 * @param int|string $key    The key under which the cache contents are stored.
	 * @param bool       $found  Optional. Whether the key was found in the cache (passed by reference).
	 *                           Disambiguates a return of false, a storable value. Default null.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache
	 *                    contents on success.
	 */
	public function get( $key, &$found = null ) {
		$value = $this->cache_get( $key, $found );
		if ( is_array( $value ) && array_key_exists( 'data', $value ) ) {
			// We know that we have set something in the cache.
			$found = true;

			return $value['data'];
		}

		$found = false;

		return $value;
	}

	/**
	 * Saves the data to the cache.
	 *
	 * @param int|string $key    The cache key to use for retrieval later.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param int        $expire Optional. When to expire the cache contents, in seconds.
	 *                           Default 0 (no expiration).
	 *
	 * @return bool False on failure, true on success
	 */
	public function set( $key, $data, $expire = 0 ) {
		$keys = $this->get_keys();
		if ( ! in_array( $key, $keys, true ) ) {
			$keys[] = $key;
			$this->cache_set( self::KEYS, $keys );
		}

		// Save $value to the array. We need to do this because W3TC and Redis have bug with saving null.
		return $this->cache_set( $key, [ 'data' => $data ], $expire );
	}

	/**
	 * Removes the cache contents matching key and group.
	 */
	public function flush_group_cache() {
		$keys = $this->get_keys();

		foreach ( $keys as $key ) {
			$this->cache_delete( $key );
		}

		$this->cache_delete( self::KEYS );
	}

	/**
	 * Retrieves the cache contents from the WordPress cache.
	 *
	 * @param int|string $key    The key under which the cache contents are stored.
	 * @param bool       $found  Optional. Whether the key was found in the cache (passed by reference).
	 *                           Disambiguates a return of false, a storable value. Default null.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache
	 *                    contents on success
	 */
	protected function cache_get( $key, &$found = null ) {
		return wp_cache_get( $key, $this->group, false, $found );
	}

	/**
	 * Saves the data to the WordPress cache.
	 *
	 * @param int|string $key    The cache key to use for retrieval later.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param int        $expire Optional. When to expire the cache contents, in seconds.
	 *                           Default 0 (no expiration).
	 *
	 * @return bool False on failure, true on success
	 */
	protected function cache_set( $key, $data, $expire = 0 ) {
		return wp_cache_set( $key, $data, $this->group, $expire );
	}

	/**
	 * Removes the WordPress cache contents matching key and group.
	 *
	 * @param int|string $key What the contents in the cache are called.
	 *
	 * @return bool True on successful removal, false on failure.
	 */
	protected function cache_delete( $key ) {
		return wp_cache_delete( $key, $this->group );
	}

	/**
	 * Get stored group keys.
	 *
	 * @return array
	 */
	private function get_keys() {
		$found = false;
		$keys  = $this->cache_get( self::KEYS, $found );
		if ( $found && is_array( $keys ) ) {
			return $keys;
		}

		return [];
	}
}
