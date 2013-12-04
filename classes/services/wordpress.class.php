<?php 

class WordPress extends TimelineService {

	/**
	 * Hooked into 'publish_post', fired when a new post is published.
	 * Create a new timeline post using the post data and save it in the DB.
	 * @param int $id The post ID
	 */
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

	/**
	 * Hooked into 'wp_trash_post', fired when a post is sent to the trash.
	 * @param int $id The post ID
	 */
	public static function trash( $id )
	{
		TimelinePost::hide( $id, 'service_id' ); // Hide the post from the front-end.
	}

	/**
	 * Hooked into 'untrashed_post', fired when a post is restored from the trash.
	 * @param int $id The post ID	 
	 */
	public static function untrash( $id )
	{
		TimelinePost::unhide( $id, 'service_id' ); // Show the post on the front-end.
	}

	/**
	 * Hooked into 'before_delete_post', fired just before a post is about to be
	 * permanently deleted from the database.
	 * @param int $id The post ID
	 */
	public static function delete( $id )
	{
		TimelinePost::delete( $id, 'service_id' ); // Delete the timeline post from the DB
	}

}

?>