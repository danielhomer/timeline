<?php 

class TimelineError {

	public $error_table;
	public $provider;
	public $severity;
	public $message;

	public function __construct( $provider, $severity, $message ) {
		global $wpdb;
		$this->error_table = $wpdb->prefix . 'timeline_errors';
		$this->provider = $provider;
		$this->severity = $severity;
		$this->message = $message;
	}

	public function log() {
		global $wpdb;

		$error = array(
			'provider' => $this->provider,
			'severity' => $this->severity,
			'message' => $this->message
			);

		$wpdb->insert( $this->error_table, $error );
	}

	public static function get() {
		global $wpdb;
		$error_table = $wpdb->prefix . 'timeline_errors';

		$results = $wpdb->get_results( "SELECT * FROM $error_table ORDER BY id ASC" );

		if ( $wpdb->num_rows > 0 ) {
			return $results;
		} else {
			return false;
		}
	}

	public static function clear() {
		global $wpdb;
		$error_table = $wpdb->prefix . 'timeline_errors';
		$wpdb->query( "TRUNCATE TABLE $error_table" );
		return "Error log cleared.";
	}
}

?>