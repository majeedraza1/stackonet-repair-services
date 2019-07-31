<?php

namespace Stackonet\Integrations;

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseDatabase {

	/**
	 * Firebase project id
	 *
	 * @var string
	 */
	protected $project_id = '';

	/**
	 * Firebase access token
	 *
	 * @var string
	 */
	protected $access_token = '';

	/**
	 * @var Database
	 */
	private $database;

	/**
	 * FirebaseDatabase constructor.
	 *
	 * @param string $project_id
	 * @param string $access_token
	 */
	public function __construct( $project_id = '', $access_token = '' ) {
		$this->project_id   = $project_id;
		$this->access_token = $access_token;

		// This assumes that you have placed the Firebase credentials in the same directory
		// as this PHP file.
		$serviceAccount = ServiceAccount::fromArray( [
			'project_id'   => 'stackonet-services',
			'client_id'    => '114720408691711230257',
			'client_email' => 'firebase-adminsdk-t1a3u@stackonet-services.iam.gserviceaccount.com',
			'private_key'  => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCrGN9zGHDAN52+\nnNPr7wRHkm5+fR0+CMN+SyF5zauWDJIJ4Z/Y0NPowj6/I8jHNTxEZaZOgdtL6C+4\nzgbNN8GwCca+yhgCgl760q8y3U7grGKOufMhJlW5xfLtfycN0npR7j1Y3s76ABX0\nhC9wKmcdRFvbvkNEgbXVD0ml7QKJweH2gSVBS+oZmdWR6jExhN0gkhQUOmvIeTO2\n+F7z1qrdM+ZOWczBezcVf7kQn6J9fjHbz7F0DXKhZkYoOv+30gRlp/wXz5eKPHXn\nNfqo6EFbEsq721gWtkmJ/yVW4YuZZIhckHZrb21S1Q0Dg7B0ZikdA4a6YiZrReUX\n1P6XNyllAgMBAAECggEAGZgNaO6pgx9useJM0zi8Z6Zwb+xwUOTHtZ8wgUNyXO5B\ndEfzfN/0KxfPghEsyhvdCZA5GfpT3x168gajvtQN3bMAr26Uu99brOKSQaYYVASF\nc7s+MuHEphcF2WARbABlutbMB3zA8mXL4vlZDUrShO7CXPMvpdJPbHEuf6seG5QN\n15pgSt4WK7izEPC6uAtptfx+pEeuQzQ5aX5yOYVAkYjsX2Lv7fuh3Fqzw/23Ryt+\n2HJGlrOKpfmd4ndBO/KiV56evkAG7OrRMeEt3/6vFfFd7HXd55QBMNKc2zxBTnM/\nmnIiA2hWruK9i3+vzA67xvPo+TzTotCSH6F4XsedCwKBgQDiGCCI/AaDUZi0NXJC\nEH5b6taoEtp0FpqAf5yB1+iOgQdkJWudB587Zq5xSJNAMyNpiFWtEoM/BYmjb/NQ\nkjr8n1Mq82cFkgYtHh6MhcfUb2hgKhhJsBH1DxsskhJzurQ2zl+eqMh6wGPSvPLR\nQJFxDWPNRlFdNGDvE5gi4szKiwKBgQDBundU4k42su88PbMTf1eyYi8w6tFZgSf/\nHxPMgIS6nHOXPPbSVaKx7T1BSYs4rqKKfeGUsh8dmFRk4b53sLlf5l+DjOtQY08G\nCQUNkPun9YL2fO3b86sPZvwBE6Q9kmXXsYdbTd100YVCGnRtTb9WLyaPu9sTGQqr\ngrN9eBGJzwKBgQCtYjdBFZShC+gA+qOeiit8rcDjr2GbeOIO/M22vS54afaTFCdM\nitXPpdTMxw01RII5ofWh/fpsUADXNzjuZtWZeU08OOCeYvcdjmV6+fTesnjmliF+\nEOoUdfsu2O4RlfIxvV2SvHjRucxThdboJY3jlpMcjnpC8bIMZYhb6HWINQKBgGzS\nn+HQ1fqn/Pcr+YIEUHDl8nhaD4tln+ARxv9jWiuxYsUb+9IfRKsKBxS7iTcn8io1\nmBf9DrmDLjUVEfcOELOsJw8wg6a+gk9zlaEPRi4NHan0d3DMqdSXFwxLykDEEe1d\nzhkd3j6Wy3JchfY4bDivd8vZzLAnqvS5ELZ0UfXFAoGAJiuEgQ81t7QGyLLDHGq0\nJt5boOy+Cg+nn26FJb6Af2R7eWERp7aTsmsGQ6ikGhA8WfX82k9rF5ZUzcOTE30d\nDhTGTSpNvlv8dCuGurvmtj8TJKJS/NC3DsAw+6yW8fYgKmSXfDCCuDI63YRSdj0B\n3vMAVNpZYunP5r7KoMW5PBM=\n-----END PRIVATE KEY-----\n',
		] );

		$firebase = ( new Factory )
			->withServiceAccount( $serviceAccount )
			->withDatabaseUri( $this->get_database_url() )
			->create();

		$this->database = $firebase->getDatabase();
	}

	/**
	 * Get firebase database url
	 *
	 * @return string
	 */
	public function get_database_url() {
		return sprintf( "https://%s.firebaseio.com", $this->get_project_id() );
	}

	/**
	 * Get project id
	 *
	 * @return string
	 */
	public function get_project_id() {
		return $this->project_id;
	}

	/**
	 * @return Database
	 */
	public function get_database() {
		return $this->database;
	}
}
