<?php
/**
 * Generate_Users class file.
 */

namespace KAGG\CacheUserQuery;

use const Patchwork\CodeManipulation\Actions\RedefinitionOfNew\publicizeConstructors;

/**
 * Class Generate_Users
 *
 * @package KAGG\CacheUserQuery
 */
class Generate_Users {

	/**
	 * Generate users.
	 *
	 * @param int $count Count of users to generate. Makes sense to generate more than 200,000.
	 */
	public function generate( $count ) {
		global $wpdb;

		// Check if users were generated, do nothing then.
		$result = $wpdb->query( "SELECT ID FROM {$wpdb->users} WHERE user_login = 'test_user_1'" );

		if ( $result ) {
			return;
		}

		// Generate users.
		$sql = "INSERT INTO {$wpdb->users} (
			user_login,
			user_pass,
			user_nicename,
			user_email,
			user_url,
			user_registered,
			user_activation_key,
			user_status,
			display_name
		) VALUES ";

		for ( $i = 1; $i <= $count; $i ++ ) {
			$user['user_login']          = "'test_user_" . $i . "'";
			$user['user_pass']           = "'\$P\$BW1XUO91cp9wqzieumKPBuy2zJr1j9.'";
			$user['user_nicename']       = $user['user_login'];
			$user['user_email']          = "'test_" . $i . "@test.test'";
			$user['user_url']            = "''";
			$user['user_registered']     = "'2020-05-06 19:00:00'";
			$user['user_activation_key'] = "'1588790605:\$P\$BEtnZazSiuc/Uv644wsAWYBW9R7yxD/'";
			$user['user_status']         = 0;
			$user['display_name']        = $user['user_login'];

			$value = '(' . implode( ',', $user ) . '),';

			$sql .= $value;
		}

		$sql = rtrim( $sql, ',' );

		$result = $wpdb->query( $sql );

		// Generate metas.
		$sql = "INSERT INTO {$wpdb->usermeta} (
			user_id,
			meta_key,
			meta_value
		) VALUES ";

		$metas = [
			'nickname'              => "'test'",
			'first_name'            => "'test'",
			'last_name'             => "'test'",
			'description'           => "''",
			'rich_editing'          => "'true'",
			'syntax_highlighting'   => "'true'",
			'comment_shortcuts'     => "'false'",
			'admin_color'           => "'fresh'",
			'use_ssl'               => "'0'",
			'show_admin_bar_front'  => "'true'",
			'locale'                => "''",
			'wp_capabilities'       => "'a:1:{s:10:\"subscriber\";b:1;}'",
			'wp_user_level'         => "'0'",
			'dismissed_wp_pointers' => "''",
		];

		for ( $i = 1; $i <= $count; $i ++ ) {
			foreach ( $metas as $key => $value ) {
				$meta['user_id']    = $i;
				$meta['meta_key']   = "'" . $key . "'";
				$meta['meta_value'] = $value;

				$value = '(' . implode( ',', $meta ) . '),';

				$sql .= $value;
			}
		}

		$sql = rtrim( $sql, ',' );

		$result = $wpdb->query( $sql );
	}
}
