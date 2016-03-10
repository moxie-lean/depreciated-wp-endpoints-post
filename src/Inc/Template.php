<?php namespace Leean\Endpoints\Inc;

class Template
{
	public static function get_template( $post ) {
		if ( 'page' === $post->post_type ) {
			return basename( get_page_template(), '.php' );
		}

		return $post->post_type;
	}
}
