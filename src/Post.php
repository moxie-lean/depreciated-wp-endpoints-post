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

	const QUERY_FILTER = 'ln_endpoints_query_args';
	const SLUG_NOT_FOUND = 'ln_slug_not_found';

	/**
	 * Get the post.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$slug = trim( $request->get_param( 'slug' ), '/' );
		$query_filter_name = self::QUERY_FILTER . $this->filter_format( $this->endpoint );
		$query_args = [
			'name' => $slug,
			'post_type' => 'any',
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		$query = new \WP_Query( apply_filters( $query_filter_name, $query_args, $request ) );

		if ( $query->have_posts() ) {
			$query->the_post();

			$post = $query->post;

			$data = [
				'post_id' => $post->ID,
				'slug' => $slug,
				'type' => Type::get( $post ),
				'content' => Content::get( $post ),
				'meta' => [],
			];

			wp_reset_postdata();
			return $this->filter_data( $data, $post->ID );
		}
		return new \WP_Error( self::SLUG_NOT_FOUND, 'Nothing found for this slug', [ 'status' => 404 ] );
	}

	/**
	 * Callback used for the endpoint
	 *
	 * @since 0.1.0
	 */
	public function endpoint_args() {
		return [
			'slug' => [
				'required' => true,
				'sanitize_callback' => function ( $slug, $request, $key ) {
					return sanitize_text_field( $slug );
				},
			],
		];
	}
}
