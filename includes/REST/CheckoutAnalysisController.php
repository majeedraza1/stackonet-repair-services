<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use Stackonet\Models\CheckoutAnalysis;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CheckoutAnalysisController extends ApiController {


	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/checkout-analysis', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ], ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ], ],
		] );

		register_rest_route( $this->namespace, '/checkout-analysis/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
		] );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$per_page = $request->get_param( 'per_page' );
		$paged    = $request->get_param( 'paged' );
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged    = ! empty( $paged ) ? absint( $paged ) : 1;

		$checkoutAnalysis = new CheckoutAnalysis();
		$_items           = $checkoutAnalysis->find( [ 'paged' => $paged, 'per_page' => $per_page, ] );
		$items            = [];
		foreach ( $_items as $item ) {
			$items[] = $this->prepare_item_for_response( $item, $request );
		}
		$counts     = $checkoutAnalysis->count_records();
		$pagination = $checkoutAnalysis->getPaginationMetadata( [
			'totalCount'  => $counts,
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		return $this->respondOK( [ 'items' => $items, 'pagination' => $pagination ] );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$step             = $request->get_param( 'step' );
		$now              = current_time( 'mysql' );
		$checkoutAnalysis = new CheckoutAnalysis();

		$data = [ 'id' => 0 ];

		if ( $checkoutAnalysis->is_valid_column( $step ) ) {
			$data[ $step ] = $now;
		}

		$record_id = ( new CheckoutAnalysis() )->create( $data );

		if ( $record_id ) {
			return $this->respondCreated( [ 'id' => $record_id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Update one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$id        = (int) $request->get_param( 'id' );
		$step      = $request->get_param( 'step' );
		$step_data = $request->get_param( 'step_data' );

		$now              = current_time( 'mysql' );
		$checkoutAnalysis = ( new CheckoutAnalysis() )->find_by_id( $id );

		if ( ! $checkoutAnalysis instanceof CheckoutAnalysis ) {
			return $this->respondNotFound();
		}

		if ( ! $checkoutAnalysis->is_valid_column( $step ) ) {
			return $this->respondUnprocessableEntity( null, 'Step is not recognized.' );
		}

		$data = [ 'id' => $id ];

		if ( empty( $checkoutAnalysis->get( $step ) ) ) {
			$data[ $step ] = $now;
		}

		$extra_information = $checkoutAnalysis->get( 'extra_information' );
		$extra_information = is_array( $extra_information ) ? $extra_information : [];

		if ( is_array( $step_data ) && count( $step_data ) ) {
			$data['extra_information'] = array_merge( $extra_information, $step_data );
		}

		$checkoutAnalysis->update( $data );

		return $this->respondOK();
	}

	/**
	 * Prepare the item for the REST response.
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function prepare_item_for_response( $item, $request ) {
		$extra_information = isset( $item['extra_information'] ) ? $item['extra_information'] : null;
		$extra_information = is_serialized( $extra_information ) ? unserialize( $extra_information ) : $extra_information;

		$response = [
			'id'          => intval( $item['id'] ),
			'ip_address'  => $item['ip_address'],
			'city'        => $item['city'],
			'postal_code' => $item['postal_code'],
		];

		$default_data = [
			'device',
			'device_model',
			'device_color',
			'zip_code',
			'screen_cracked',
			'device_issue',
			'phone_number',
			'requested_date_time',
			'user_address',
			'user_details',
			'terms_and_conditions',
			'thank_you',
		];
		$total        = count( $default_data );
		$active_item  = 0;

		foreach ( $item as $key => $value ) {
			if ( ! in_array( $key, $default_data ) ) {
				continue;
			}

			switch ( $key ) {
				case 'device_model';
					$label = 'Model';
					break;
				case 'device_color';
					$label = 'Color';
					break;
				case 'zip_code';
					$label = 'ZIP';
					break;
				case 'screen_cracked';
					$label = 'Screen Crack';
					break;
				case 'device_issue';
					$label = 'Issues';
					break;
				case 'phone_number';
					$label = 'Phone Number';
					break;
				case 'requested_date_time';
					$label = 'Time';
					break;
				case 'user_address';
					$label = 'Address';
					break;
				case 'user_details';
					$label = 'User Details';
					break;
				case 'terms_and_conditions';
					$label = 'Terms';
					break;
				case 'thank_you';
					$label = 'Complete';
					break;
				default:
					$label = str_replace( '_', ' ', $key );
					$label = str_replace( 'and', '&', $label );
					$label = ucwords( $label );
					break;
			}

			$active = ! empty( $value );
			if ( $active ) {
				$active_item += 1;
			}

			$dateTime = new DateTime( $value );

			$response['steps'][] = [
				'label'    => $label,
				'datetime' => $value,
				'date'     => $dateTime->format( 'M d, Y' ),
				'time'     => $dateTime->format( 'H:i:s' ),
				'active'   => $active,
				'value'    => isset( $extra_information[ $key ] ) ? $extra_information[ $key ] : null,
			];
		}

		$response['steps_count']      = count( $response['steps'] );
		$response['steps_completed']  = $active_item;
		$response['steps_percentage'] = round( ( $active_item / $response['steps_count'] ) * 100 );

		return $response;
	}
}
