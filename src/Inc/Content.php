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
	 * @param Int|\WP_Post $post The post
	 * @return array
	 */
	public static function get( $post ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : get_post( $post );

		$data = [
			'title' => get_the_title( $post ),
			'content' => apply_filters( 'the_content', get_post_field( 'post_content', $post->ID ) ),
		];

		if ( function_exists( 'get_field_objects' ) ) {
			self::get_acf( $post->ID, $data );
		}

		return $data;
	}

	/**
	 * Get the post's ACF fields
	 *
	 * @param Int   $post_id  The post
	 * @param array $data The data array to update
	 */
	public static function get_acf( $post_id, &$data ) {
		$fields = get_field_objects( $post_id );

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

	/**
	 * Add filters to customise the output of certain acf field types
	 */
	public static function acf_customize() {
		// Get all data for posts only when the return format is 'id'.
		add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $field, $post ) {
			if ( 'post_object' === $field['type'] && 'id' === $field['return_format'] ) {
				if ( is_array( $field['value'] ) ) {
					$data = [];
					foreach ( $field['value'] as $post_id ) {
						$data[] = self::get( $post_id );
					}
					return $data;
				} else {
					return self::get( $field['value'] );
				}
			}
			return $value;
		}, 10, 4 );
	}
}
