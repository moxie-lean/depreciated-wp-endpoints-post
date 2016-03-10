<?php namespace Leean\Endpoints\Inc;

/**
 * Class Template
 * @package Leean\Endpoints\Inc
 */
class Template
{
	/**
	 * Get the template
	 * @return string
	 */
	public static function get() {
		$post = get_post();

		if ( 'page' === $post->post_type ) {
			return basename( get_page_template(), '.php' );
		}

		return $post->post_type;
	}
}
