<?php

namespace Stackonet\Supports;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Models\FileMetadata;
use Stackonet\Models\Settings;

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
		$this->client_id     = Settings::get_dropbox_key();
		$this->client_secret = Settings::get_dropbox_secret();
		$this->access_token  = Settings::get_dropbox_access_token();

		if ( ! empty( $this->client_id ) && ! empty( $this->client_secret ) ) {
			$this->config  = new DropboxApp( $this->client_id, $this->client_secret, $this->access_token );
			$this->dropbox = new Dropbox( $this->config );
		}
	}

	/**
	 * Upload a File to Dropbox
	 *
	 * @param string $source_path
	 * @param string $path
	 *
	 * @return FileMetadata|bool
	 */
	public function upload( $source_path, $path = "/" ) {
		$fileName    = basename( $source_path );
		$dropboxFile = DropboxFile::createByPath( $source_path, DropboxFile::MODE_READ );
		if ( ! $this->dropbox instanceof Dropbox ) {
			return false;
		}

		return $this->dropbox->upload( $dropboxFile, $path . $fileName, [ 'autorename' => true ] );
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
		if ( ! $this->dropbox instanceof Dropbox ) {
			return '';
		}

		return $this->get_dropbox()->getAuthHelper()->getAuthUrl( $redirectUri, $params, $urlState );
	}

	/**
	 * @return Dropbox
	 */
	public function get_dropbox() {
		return $this->dropbox;
	}
}
