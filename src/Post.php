<?php namespace Leean\Endpoints;

use Leean\Endpoints\Inc\Content;
use Leean\Endpoints\Inc\Type;
use Leean\AbstractEndpoint;

/**
 * Class to provide activation point for our endpoints.
 */
class Post extends AbstractEndpoint {

	/**
	 * Slug for the definition of the post.
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/post';

	const QUERY_FILTER = 'ln_endpoints_%s_query_args';

	const INVALID_PARAMS = 'ln_invalid_params';

	const NOT_FOUND = 'ln_not_found';

	/**
	 * Get the post.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$params = $request->get_params();

		$id = isset( $params['id'] ) ? $params['id'] : false;

		$slug = isset( $params['slug'] ) ? trim( $params['slug'], '/' ) : false;

		if ( false === $id && false === $slug ) {
			return new \WP_Error( self::INVALID_PARAMS, 'The request must have either an id or a slug', [ 'status' => 400 ] );
		}

		$query_args = [
			'post_type' => 'any',
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		if ( false !== $id ) {
			$query_args['p'] = $id;
		} else {
			$query_args['name'] = $slug;
		}

		$query = new \WP_Query( apply_filters( $this->get_query_filter_name(), $query_args, $request ) );

		if ( $query->have_posts() ) {
			$query->the_post();

			$post = $query->post;

			$data = [
				'id' => $post->ID,
				'slug' => $post->post_name,
				'type' => Type::get( $post ),
				'content' => Content::get( $post ),
				'meta' => [],
			];

			wp_reset_postdata();

			return $this->filter_data( $data, $post->ID );
		}

		return new \WP_Error( self::NOT_FOUND, 'Nothing found for this slug', [ 'status' => 404 ] );
	}

	/**
	 * Makes sure there is no more _ between and after the filter_format
	 *
	 * @since 0.2.0
	 * @return String
	 */
	private function get_query_filter_name() {
		$filter_format = trim( $this->filter_format( $this->endpoint ), '_' );
		return sprintf( self::QUERY_FILTER, $filter_format );
	}

	/**
	 * Callback used for the endpoint
	 *
	 * @since 0.1.0
	 */
	public function endpoint_args() {
		return [
			'id' => [
				'validate_callback' => function ( $id ) {
					return ctype_digit( (string) $id ) && 0 !== (int) $id;
				},
			],
			'slug' => [
				'sanitize_callback' => function ( $slug, $request, $key ) {
					return sanitize_text_field( $slug );
				},
			],
		];
	}
}
