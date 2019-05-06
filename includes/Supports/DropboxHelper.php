<?php

namespace Stackonet\Supports;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

defined( 'ABSPATH' ) || exit;

class DropboxHelper {

	/**
	 * @var string
	 */
	private static $client_id;

	/**
	 * @var string
	 */
	private static $client_secret;

	/**
	 * @var string
	 */
	private static $access_token;

	/**
	 * @var DropboxApp
	 */
	private static $config;

	/**
	 * @var Dropbox
	 */
	private static $dropbox;

	/**
	 * Upload file to Dropbox
	 *
	 * @param string $source_path
	 */
	public static function upload( $source_path ) {
		self::$client_id     = '68vxtory55yy12f';
		self::$client_secret = '7isfxoqyisaxgol';
		self::$access_token  = 'W8EvBDtxcrMAAAAAAAATPEmHT-DQeUT34QoPx-sgh8V6pDkXqbSsFSTJKCoofmDw';

		self::$config  = new DropboxApp( self::$client_id, self::$client_secret, self::$access_token );
		self::$dropbox = new Dropbox( self::$config );

		$dropboxFile = DropboxFile::createByPath( $source_path, DropboxFile::MODE_READ );
	}

}
