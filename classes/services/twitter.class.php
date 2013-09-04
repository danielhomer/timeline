<?php

class Twitter extends TimelineService {

	public $username;
	public $consumer_key;
	public $consumer_secret;
	public $access_token;
	public $access_token_secret;
	public $host = "https://api.twitter.com/1.1/";

	public function __construct()
	{
		$this->username = get_option( 'timeline_option_twitter' )['username'];
		$this->consumer_key = get_option( 'timeline_option_twitter' )['consumer_key'];
		$this->consumer_secret = get_option( 'timeline_option_twitter' )['consumer_secret'];
		$this->access_token = get_option( 'timeline_option_twitter' )['access_token'];
		$this->access_token_secret = get_option( 'timeline_option_twitter' )['access_token_secret'];
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
	    $this->consumer = new OAuthConsumer( $this->consumer_key, $this->consumer_secret );
	    
	    if ( ! empty( $this->access_token ) && ! empty( $this->access_token_secret ) ) {
			$this->token = new OAuthConsumer( $this->access_token, $this->access_token_secret );
	    } else {
			$this->token = NULL;
	    }
	}

	public function sync()
	{
		$tweets = $this->get('statuses/user_timeline', array( 'screen_name' => $this->username ) );
		
		if ( ! $tweets || empty( $tweets ) )
			return;

		$i = 0;
		foreach ( $tweets as $tweet ) {
			if ( $i === 0 && TimelinePost::get( $tweet->id_str ) )
				return false;

			$timelinePost = new TimelinePost();
			$timelinePost->service = "Twitter";
			$timelinePost->serviceID = $tweet->id_str;
			$timelinePost->content = $tweet->text;
			$timelinePost->time = strtotime( $tweet->created_at );
			$timelinePost->save();
		}
	}

	public function get( $url, $parameters )
	{
		$response = $this->oAuthRequest( $url, 'GET', $parameters );
		
		if ( $this->format === 'json' && $this->decode_json ) {
			return json_decode( $response );
		}
		
		return $response;
	}

}

?>