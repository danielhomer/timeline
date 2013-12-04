<?php 

class TimelineService {

	protected $http_info;
	protected $http_code;
	protected $url;
	protected $timeout = 30;
	protected $connecttimeout = 30;
	protected $useragent = 'Timeline Plugin for WordPress';
	protected $http_header = array();
	protected $ssl_verifypeer = FALSE;
	protected $format = 'json';
	protected $decode_json = TRUE;
	protected $service_ids = array();

	/**
	 * Construct the service object, get the service ids from the database
	 */
	public function __construct()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$this->service_ids = $wpdb->get_col( "SELECT service_id FROM $posts_table" );
	}

	/**
	 * Create a HTTP request with the passed URL, method and fields
	 * @param  string $url        The URL to run the request against
	 * @param  string $method     The HTTP method (i.e. POST, GET, UPDATE etc.)
	 * @param  array  $postfields The parameters to send with the request
	 * @return mixed  						The response
	 */
	protected function http( $url, $method, $postfields = NULL )
	{
		$this->http_info = array();
		$ci = curl_init();
	
		curl_setopt( $ci, CURLOPT_USERAGENT, $this->useragent );
		curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout );
		curl_setopt( $ci, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ci, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer );
		curl_setopt( $ci, CURLOPT_HEADERFUNCTION, array( $this, 'getHeader' ) );
		curl_setopt( $ci, CURLOPT_HEADER, FALSE );

		switch ( $method ) {
			case 'POST':
				curl_setopt( $ci, CURLOPT_POST, TRUE );
				if ( ! empty( $postfields ) ) {
					curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
				}
				break;
			case 'DELETE':
				curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'DELETE' );
				if ( ! empty( $postfields ) ) {
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt( $ci, CURLOPT_URL, $url );
		$response = curl_exec( $ci );
		$this->http_code = curl_getinfo( $ci, CURLINFO_HTTP_CODE );
		$this->http_info = array_merge( $this->http_info, curl_getinfo( $ci ) );
		$this->url = $url;
		curl_close ( $ci );
		return $response;
	}

	/**
	 * Add the headers to the http_headers array
	 * @param  object $ch     The cURL resource
	 * @param  string $header The header data
	 * @return int            The header length
	 */
	protected function getHeader( $ch, $header )
	{
		$i = strpos( $header, ':' ); // Check for the header key
		
		if ( ! empty( $i ) ) {
			$key = str_replace( '-', '_', strtolower( substr( $header, 0, $i ) ) ); // Replace all dashes with underscores, convert the key to lowercase
			$value = trim( substr( $header, $i + 2 ) ); // Remove whitespace after the key
			$this->http_header[ $key ] = $value; // Add the header key => value to the object http_headers array
		}

		return strlen( $header ); // Return the header length
	}

	/**
	 * Prepare a HTTP request using oAuth for authentication
	 * @param  string $url        The URL to run the request against
	 * @param  string $method     The HTTP method (i.e. POST, GET, UPDATE etc.)
	 * @param  array  $parameters The parameters to send with the request
	 * @return mixed  						The response
	 */
	protected function oAuthRequest( $url, $method, $parameters )
	{
		if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 )
			$url = "{$this->host}{$url}.{$this->format}";

		$request = OAuthRequest::from_consumer_and_token( $this->consumer, $this->token, $method, $url, $parameters );
		$request->sign_request( $this->sha1_method, $this->consumer, $this->token );

		switch ( $method ) {
			case 'GET':
				return $this->http( $request->to_url(), 'GET' );
			default:
				return $this->http( $request->get_normalized_http_url(), $method, $request->to_postdata() );
		}
	}

}

?>