<?php namespace Leean\Endpoints\Inc;

use Leean\Endpoints\Post;

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
					Post::ENDPOINT,
					$post_id,
					$field
				);
		}
	}

	/**
	 * Add filters to customise the output of certain acf field types
	 */
	public static function acf_customize() {
		// Get all data for posts only when the return format is 'id'.
		add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $post_id, $field ) {
			if ( ! ('post_object' === $field['type'] && 'id' === $field['return_format']) ) {
				return $value;
			}

			if ( is_array( $field['value'] ) ) {
				$data = [];
				foreach ( $field['value'] as $post_id ) {
					$data[] = self::get( $post_id );
				}
				return $data;
			} else {
				return self::get( $field['value'] );
			}
		}, 10, 4 );

		// Get the image details for the given size (only when return format is 'id').
		add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $post_id, $field ) {
			if ( ! ('image' === $field['type'] && 'id' === $field['return_format']) ) {
				return $value;
			}

			return self::customize_image( $value, $post_id, $field );
		}, 10, 4 );

		// Do the same for images in repeaters.
		add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $post_id, $field ) {
			if ( ! ('repeater' === $field['type'] && is_array($value) && 0 <= count($value) ) ) {
				return $value;
			}

			foreach ( $field['sub_fields'] as $sub_field ) {
				if ( 'image' === $sub_field['type'] && 'id' === $sub_field['return_format'] ) {
					foreach ( $value as $id => $item ) {
						$value[$id][ $sub_field['name'] ] =
							self::customize_image( $item[ $sub_field['name'] ], $post_id, $field, $sub_field['name'] );
					}
				}
			}

			return $value;
		}, 8, 4 );

		// Provide a filter to output repeater as object instead of array if there's just 1 item.
		add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $post_id, $field ) {
			if ( ! ('repeater' === $field['type'] && is_array($value) && 1 === count($value) ) ) {
				return $value;
			}

			$as_array = apply_filters(
				'ln_endpoints_acf_repeater_as_array',
				true,
				Post::ENDPOINT,
				$post_id,
				$field
			);

			return $as_array ? $value : $value[0];
		}, 10, 4 );
	}

	/**
	 * Do the image size filter.
	 *
	 * @param int    $attachment_id Image's id
	 * @param int    $post_id		Post id
	 * @param string $field			Field
	 * @param bool   $sub_field		Sub field (only if it's a repeater)
	 * @return array
	 */
	private static function customize_image( $attachment_id, $post_id, $field, $sub_field = false ) {
		$size = apply_filters(
			'ln_endpoints_acf_image_size',
			false,
			Post::ENDPOINT,
			$post_id,
			$field,
			$sub_field
		);

		if ( ! $size ) {
			return $attachment_id;
		}

		$src = wp_get_attachment_image_src( $attachment_id, $size );

		if ( ! $src ) {
			return $attachment_id;
		}

		return [
			'src' 		=> $src[0],
			'width'		=> $src[1],
			'height'	=> $src[2],
			'alt'		=> get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		];
	}
}
