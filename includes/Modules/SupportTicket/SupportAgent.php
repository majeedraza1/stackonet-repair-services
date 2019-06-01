<?php

namespace Stackonet\Modules\SupportTicket;

use Stackonet\Abstracts\AbstractModel;
use WP_Term;
use WP_User;

class SupportAgent extends AbstractModel {


	/**
	 * Taxonomy name
	 *
	 * @var string
	 */
	protected static $taxonomy = 'wpsc_agents';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'term_id';

	/**
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * @var WP_User
	 */
	protected $user;

	/**
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * @var void
	 */
	private $agent_roles = [];

	/**
	 * @var int
	 */
	private $role_id = 0;

	/**
	 * @var string
	 */
	private $role_label = '';

	/**
	 * Class constructor.
	 *
	 * @param null|WP_Term $term
	 */
	public function __construct( $term = null ) {
		if ( $term instanceof WP_Term ) {
			$this->term        = $term;
			$this->data        = $term->to_array();
			$this->user_id     = (int) get_term_meta( $term->term_id, 'user_id', true );
			$this->role_id     = (int) get_term_meta( $term->term_id, 'role', true );
			$this->agent_roles = get_option( 'wpsc_agent_role' );
			if ( ! empty( $this->agent_roles[ $this->role_id ]['label'] ) ) {
				$this->role_label = $this->agent_roles[ $this->role_id ]['label'];
			}
		}
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'term_id'      => $this->get( 'term_id' ),
			'slug'         => $this->get( 'slug' ),
			'name'         => $this->get( 'name' ),
			'role_label'   => $this->role_label,
			'id'           => $this->get_user()->ID,
			'display_name' => $this->get_user()->display_name,
			'email'        => $this->get_user()->user_email,
			'avatar_url'   => get_avatar_url( $this->get_user()->user_email ),
		];
	}

	/**
	 * Get user
	 *
	 * @return WP_User
	 */
	public function get_user() {
		if ( ! $this->user instanceof WP_User ) {
			$this->user = get_user_by( 'id', $this->user_id );
		}

		return $this->user;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @param array $args
	 *
	 * @return self[]
	 */
	public static function get_all( $args = [] ) {
		$default          = array(
			'hide_empty' => false,
			'meta_query' => array(
				array(
					'key'     => 'agentgroup',
					'value'   => '0',
					'compare' => '='
				)
			)
		);
		$args             = wp_parse_args( $args, $default );
		$args['taxonomy'] = self::$taxonomy;

		$_terms = get_terms( $args );

		$terms = [];
		foreach ( $_terms as $term ) {
			$terms[] = new self( $term );
		}

		return $terms;
	}

	/**
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}
}
