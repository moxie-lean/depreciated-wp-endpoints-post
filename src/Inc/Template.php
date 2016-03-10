<?php namespace Leean\Endpoints\Inc;

/**
 * Class Template
 *
 * @package Leean\Endpoints\Inc
 */
class Template
{
	/**
	 * Get the template
	 *
	 * @param Int|\WP_Post $post The post
	 * @return string
	 */
	public static function get( $post ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : get_post( $post );

		if ( 'page' === $post->post_type ) {
			$template_slug = get_page_template_slug( $post->ID );
			return $template_slug ? basename( $template_slug, '.php' ) : 'page';
		}

		return $post->post_type;
	}
}
