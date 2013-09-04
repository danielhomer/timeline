<?php 

class Facebook extends TimelineService {

	private $app_id = '402402469864947';
	private $secret = 'b204fd49a59f92ae5779241e41718149';

	public function __construct()
	{

	}

	public function sync()
	{
		$facebook = new FacebookSession( array(
			'appId' => $this->app_id,
			'secret' => $this->secret
			) );

		$user = $facebook->getUser();

		var_dump( $facebook );

		if ($user) {
			try {
		    	$user_profile = $facebook->api('/me');
		  	} catch (FacebookApiException $e) {
		    	error_log($e);
		    	$user = null;
		  	}
		}

		var_dump( $user_profile );

	}



}

?>