<?php namespace Leean\Endpoints;

use Leean\Endpoints\Inc\Content;

/**
 * Class to provide activation point for our endpoints.
 */
class View
{
	const ENDPOINT = '/view';

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'rest_api_init', function () {
			$namespace = apply_filters( 'ln_endpoints_api_namespace', 'leean', self::ENDPOINT );
			$version = apply_filters( 'ln_endpoints_api_version', 'v1', self::ENDPOINT );

			register_rest_route(
				$namespace . '/' . $version,
				self::ENDPOINT,
				[
					'methods' => 'GET',
					'callback' => [ __CLASS__, 'get_post' ],
					'args' => [
						'slug' => [
							'required' => true,
							'sanitize_callback' => function ( $param, $request, $key ) {
								return sanitize_text_field( $param );
							},
						],
					],
				]
			);
		} );

		Inc\Content::acf_customize();
	}

	/**
	 * Get the post.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_post( \WP_REST_Request $request ) {
		$slug = $request->get_param( 'slug' );

		$query = new \WP_Query(
			apply_filters(
				'ln_endpoints_query_args',
				[
					'name' => $slug,
					'post_type' => 'any',
				],
				self::ENDPOINT,
				$request
			)
		);

		if ( $query->have_posts() ) {
			$query->the_post();

			$post = get_post();

			$data = [
				'post_id' => get_the_ID(),
				'slug' => $slug,
				'template' => Inc\Template::get(),
				'content' => Inc\Content::get( $post ),
				'meta' => [],
			];

			wp_reset_postdata();

			return apply_filters(
				'ln_endpoints_data',
				$data,
				self::ENDPOINT,
				$post
			);
		}

		return new \WP_Error( 'ln_slug_not_found', 'Nothing found for this slug', [ 'status' => 404 ] );
	}
}
