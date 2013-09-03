<?php 

class TimelinePost {

	public $posts_table;
	public $service;
	public $serviceID;
	public $content;
	public $attributes;
	public $time;
	public $hidden = 0;

	public function __construct()
	{
		global $wpdb;
		$this->posts_table = $wpdb->prefix . 'timeline';
	}

	public static function all()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->get_results( "SELECT * FROM $posts_table ORDER BY time DESC" );
	}

	public static function get( $id, $column = "service_id" )
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		return $wpdb->query( $wpdb->prepare( "SELECT * FROM $posts_table WHERE service_id = %s", $id ) );
	}

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