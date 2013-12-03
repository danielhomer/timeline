<?php 

class TimelinePost {

	public $posts_table;
	public $service;
	public $serviceID;
	public $content;
	public $attributes;
	public $time;
	public $hidden = 0;

	/**
	 * Set the timeline post table
	 */
	public function __construct()
	{
		global $wpdb;
		$this->posts_table = $wpdb->prefix . 'timeline';
	}

	/**
	 * Retrieve all of the timeline posts from the posts table
	 * @return array Numerically indexed array of row objects
	 */
	public static function all()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->get_results( "SELECT * FROM $posts_table ORDER BY time DESC" );
	}

	/**
	 * Get all of the timeline posts between two bounds followed by a trailer
	 * object which can be used to display the 'more' link or 'end' at the end 
	 * of the timeline
	 * @param  integer $start Start count
	 * @param  integer $stop  End count
	 * @return array         	Numerically indexed array of row objects followed
	 *                        by a trailer object            
	 */
	public static function inBounds( $start = 0, $stop = 10 )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$results = $wpdb->get_results( "SELECT * FROM $posts_table ORDER BY time DESC LIMIT $start, $stop" );

		$trailer = new stdClass();
		$trailer->service = $wpdb->num_rows < $stop ? 'end' : 'more';
		$results[] = $trailer;

		return $results;
	}

	/**
	 * Get a timeline post matching the passed criteria
	 * @param  string $id     The search term
	 * @param  string $column The column to match against
	 * @return object         The found timeline post
	 */
	public static function get( $id, $column = "service_id" )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->query( $wpdb->prepare( "SELECT * FROM $posts_table WHERE $column = %s", $id ) );
	}

	/**
	 * Delete a timeline post matching the given criteria
	 * @param  string $id     The search term
	 * @param  string $column The column to match against
	 * @return int|false      The number of rows updated if successful
	 *                        False on error
	 */
	public static function delete( $id, $column = 'id' )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->delete( $posts_table, array( $column => $id ) );
	}

	/**
	 * Set a timeline post as hidden
	 * @param  string $id     The search term
	 * @param  string $column The column to match against
	 * @return int|false      The number of rows updated if successful
	 *                        False on error
	 */
	public static function hide( $id, $column = 'id' )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->update( $posts_table, array( 'hidden' => 1 ), array( $column => $id ), array( '%d' ) );
	}

	/**
	 * Set a timeline post as visible
	 * @param  string $id     The search term
	 * @param  string $column The column to match against
	 * @return int|false      The number of rows updated if successful
	 *                        False on error
	 */
	public static function unhide( $id, $column = 'id' )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->update( $posts_table, array( 'hidden' => 0 ), array( $column => $id ), array( '%d' ) );
	}

	/**
	 * Save the current timeline post in the database
	 * @return boolean True if successful or false on error
	 */
	public function save()
	{
		global $wpdb;
		$data = array(
			'service' => $this->service,
			'service_id' => $this->serviceID,
			'content' => $this->content,
			'attributes' => $this->attributes,
			'time' => $this->time,
			'hidden' => $this->hidden,
			);

		$wpdb->insert( $this->posts_table, $data );
	}

}

?>