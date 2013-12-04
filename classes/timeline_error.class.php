<?php 

class TimelineError {

	public $error_table;
	public $provider;
	public $severity;
	public $message;

	/**
	 * Construct the error object
	 * @param [type] $provider Where the error came from
	 * @param [type] $severity Severity of the error
	 * @param [type] $message  Description of the error
	 */
	public function __construct( $provider, $severity, $message ) {
		global $wpdb;
		$this->error_table = $wpdb->prefix . 'timeline_errors';
		$this->provider = $provider;
		$this->severity = $severity;
		$this->message = $message;
	}

	/**
	 * Save the log item in the database
	 */
	public function log() {
		global $wpdb;

		$error = array(
			'provider' => $this->provider,
			'severity' => $this->severity, // Error / Warning / Notice
			'message' => $this->message
			);

		$wpdb->insert( $this->error_table, $error );
	}

	/**
	 * Get all of the logs from the database
	 * @return array|boolean Array of log items, false if no log items found
	 */
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

	/**
	 * Clear all of the logs from the database
	 * @return string The confirmation message
	 */
	public static function clear() {
		global $wpdb;
		$error_table = $wpdb->prefix . 'timeline_errors';
		$wpdb->query( "TRUNCATE TABLE $error_table" );
		return "Error log cleared.";
	}
}

?>