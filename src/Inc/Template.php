<?php namespace Leean\Endpoints\Inc;

/**
 * Class Template
 *
 * @package Leean\Endpoints\Inc
 */
class Template
{
	/**
	 * Returns the template used on a page:
	 *
	 * - Default for the default page template
	 * - Template Slug
	 * - Empty string if the post is not a page.
	 *
	 * @param Int|\WP_Post $post The post
	 * @return string
	 */
	public static function get( $post ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : get_post( $post );
		$template = '';
		if ( 'page' === $post->post_type ) {
			$template_slug = get_page_template_slug( $post->ID );
			$template = empty( $template_slug )
				? 'default'
				: basename( $template_slug, '.php' );
		}
		return $template;
	}
}
