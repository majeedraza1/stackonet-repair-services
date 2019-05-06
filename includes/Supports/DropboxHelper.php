<?php

namespace Stackonet\Supports;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Models\FileMetadata;

defined( 'ABSPATH' ) || exit;

class DropboxHelper {

	/**
	 * @var string
	 */
	private $client_id;

	/**
	 * @var string
	 */
	private $client_secret;

	/**
	 * @var string
	 */
	private $access_token = null;

	/**
	 * @var DropboxApp
	 */
	private $config;

	/**
	 * @var Dropbox
	 */
	private $dropbox;

	public function __construct() {
		$this->client_id     = '68vxtory55yy12f';
		$this->client_secret = '7isfxoqyisaxgol';
		$this->access_token  = 'W8EvBDtxcrMAAAAAAAATPEmHT-DQeUT34QoPx-sgh8V6pDkXqbSsFSTJKCoofmDw';

		$this->config  = new DropboxApp( $this->client_id, $this->client_secret, $this->access_token );
		$this->dropbox = new Dropbox( $this->config );
	}

	/**
	 * Upload a File to Dropbox
	 *
	 * @param string $source_path
	 *
	 * @return FileMetadata
	 */
	public function upload( $source_path ) {
		$fileName    = basename( $source_path );
		$dropboxFile = DropboxFile::createByPath( $source_path, DropboxFile::MODE_READ );

		return $this->dropbox->upload( $dropboxFile, "/" . $fileName, [ 'autorename' => true ] );
	}

	/**
	 * Get Authorization URL
	 *
	 * @param string $redirectUri Callback URL to redirect to after authorization
	 * @param array $params Additional Params
	 * @param string $urlState Additional User Provided State Data
	 *
	 * @return string
	 */
	public function get_auth_url( $redirectUri = null, array $params = [], $urlState = null ) {
		return $this->get_dropbox()->getAuthHelper()->getAuthUrl( $redirectUri, $params, $urlState );
	}

	/**
	 * @return Dropbox
	 */
	public function get_dropbox() {
		return $this->dropbox;
	}
}
