<?php namespace Leean\Endpoints\Inc;

use Leean\Endpoints\View;

/**
 * Class Content
 *
 * @package Leean\Endpoints\Inc
 */
class Content
{
	/**
	 * Get the content
	 *
	 * @return array
	 */
	public static function get() {
		$data = [
			'title' => get_the_title(),
			'content' => get_the_content(),
		];

		if ( function_exists( 'get_field_objects' ) ) {
			self::get_acf( $data );
		}

		return $data;
	}

	/**
	 * Get the post's ACF fields
	 *
	 * @param array $data The data array to update
	 */
	public static function get_acf( &$data ) {
		$fields = get_field_objects();

		if ( ! $fields ) {
			return;
		}

		foreach ( $fields as $field_name => $field ) {
			$data[ get_post( $field['parent'] )->post_excerpt ][ $field_name ] =
				apply_filters(
					'ln_endpoints_acf',
					$field['value'],
					View::ENDPOINT,
					$field,
					get_post()
				);
		}
	}
}
