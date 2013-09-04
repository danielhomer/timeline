<?php 

class WordPress extends TimelineService {

	public function __construct()
	{

	}

	public static function add( $id )
	{
		if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {
			$post = get_post( $id );
			$title = $post->post_title;
			$permalink = get_permalink( $post->id );
			$author = get_userdata( $post->post_author );
			$full_name = $author->user_firstname . ( $author->user_lastname ? ' ' . $author->user_lastname : '' );

			$timelinePost = new TimelinePost();
			$timelinePost->service = "WordPress";
			$timelinePost->serviceID = $id;
			$timelinePost->content = '<a href="' . $author->user_url . '">' . $full_name . '</a> published <a href="' . $permalink . '">' . $title . '</a>';
			$timelinePost->time = time();
			$timelinePost->save();
		}
	}

	public static function trash( $id )
	{
		TimelinePost::hide( $id, 'service_id' );
	}

	public static function untrash( $id )
	{
		TimelinePost::unhide( $id, 'service_id' );
	}

	public static function delete( $id )
	{
		TimelinePost::delete( $id, 'service_id' );
	}

}

?>