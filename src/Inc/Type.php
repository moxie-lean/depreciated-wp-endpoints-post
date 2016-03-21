<?php namespace Leean\Endpoints\Inc;

/**
 * Class Type
 *
 * @package Leean\Endpoints\Inc
 */
class Type
{
	/**
	 * Returns the type for the post, the term template applies to what type of
	 * post is or what type of template is using.
	 *
	 * @param Int|\WP_Post $post The post
	 * @return string
	 */
	public static function get( $post ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : get_post( $post );
		$type = $post->post_type;
		if ( 'page' === $type ) {
			$template_slug = get_page_template_slug( $post->ID );
			if ( ! empty( $template_slug ) ) {
				$type .= '-' . wp_basename( $template_slug, '.php' );
			}
		}
		return $type;
	}
}
