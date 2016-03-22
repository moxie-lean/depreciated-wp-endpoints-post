<?php namespace Leean\Endpoints\Inc;

use Leean\Acf;

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

		return array_merge( $data, Acf::get_post_field( $post->ID, false ) );
	}
}
