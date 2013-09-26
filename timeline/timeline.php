<?php 
/*
Plugin Name: Timeline
Plugin URI: http://danielhomer.me/plugins/timeline
Description: Include a timeline of your most recent online activity directly on your blog!
Version: 0.2
Author: Daniel Homer
Author URI: http://danielhomer.me
License: GPL2
*/

define( 'TIMELINE_VERSION', '0.2' );
define( 'TIMELINE_PLUGIN_URI', plugins_url( '', 'timeline/timeline.php' ) );
define( 'TIMELINE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Autoload the classes
foreach ( glob( plugin_dir_path( __FILE__ ) . "classes{/*,/*/*}.class.php", GLOB_BRACE ) as $file )
    include_once $file;

register_activation_hook( __FILE__, array( 'Timeline', 'install' ) );
register_deactivation_hook( __FILE__, array( 'Timeline', 'uninstall' ) );

add_action( 'init', array( 'Timeline', 'run' ) );
add_action( 'admin_menu', array( 'Timeline', 'addMenus' ) );
add_action( 'wp_ajax_get_response', array( 'Timeline', 'ajaxResponse' ) );

class Timeline {

	public static $active_providers;
	public static $available_providers = array(
		'twitter', 'facebook', 'github', 'wordpress'
		);

	/**
	 * Create the table that is going to contain all of our timeline posts
	 */
	public static function install()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$error_table = $wpdb->prefix . 'timeline_errors';

		$sql = "CREATE TABLE $posts_table (
			id int(9) NOT NULL AUTO_INCREMENT,
			service varchar(45) NOT NULL,
			service_id varchar(45) NOT NULL,
			content varchar(2048) NOT NULL,
			attributes varchar(2048) NOT NULL,
			time int(10) NOT NULL,
			hidden int(1),
			UNIQUE KEY id (id)
			);
			CREATE TABLE $error_table (
			id int(9) NOT NULL AUTO_INCREMENT,
			provider varchar(45) NOT NULL,
			severity varchar(45) NOT NULL,
			message varchar(2048) NOT NULL,
			time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY id (id)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Drop the timeline table and clear our timeout transient
	 */
	public static function uninstall()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$wpdb->query( "DROP TABLE IF EXISTS $posts_table" );

		$error_table = $wpdb->prefix . 'timeline_errors';
		$wpdb->query( "DROP TABLE IF EXISTS $error_table" );

		if ( get_transient( 'timeline_wait' ) )
			delete_transient( 'timeline_wait' );
	}

	/**
	 * Fired on init, if our wait transient has expired, we cycle through all 
	 * of our active providers and run their sync methods. The WordPress 
	 * provider is the exception to this, where we don't have a feed / API to 
	 * check for new data, we just need to register some hooks for when 
	 * various post operations are triggered.
	 * 
	 * Once all of our providers have done their thing, we reset the wait
	 * transient to ensure we don't hammer the server every time a page is
	 * loaded.
	 */
	public static function run()
	{
		self::$active_providers = get_option( 'timeline_option_providers' );

		if ( self::$active_providers['wordpress'] ) {
			add_action( 'publish_post', array( 'WordPress', 'add' ) );
			add_action( 'wp_trash_post', array( 'WordPress', 'trash' ) );
			add_action( 'untrashed_post', array( 'WordPress', 'untrash' ) );
			add_action( 'before_delete_post', array( 'WordPress', 'delete' ) );
		}

		if ( get_transient( 'timeline_wait' ) )
			return false;

		foreach ( self::$active_providers as $provider => $enabled ) {
			if ( $enabled && $provider != 'wordpress' ) {
				$$provider = new $provider();
				$$provider->sync();
			}
		}

		set_transient( 'timeline_wait', true, 60*5 );
	}

	/**
	 * Hooked into 'wp_ajax_get_response', checks our POST data, validates
	 * it and runs the appropriate action with the given parameters.
	 */
	public static function ajaxResponse()
	{
		$timeline_action = isset( $_POST['timeline_action'] ) ? $_POST['timeline_action'] : false;
		$timeline_params = isset( $_POST['timeline_params'] ) ? $_POST['timeline_params'] : false;
		$errors = array();
		$results = array();

		if ( ! $timeline_action )
			$errors[] = "No action specified";

		if ( ! current_user_can( 'activate_plugins' ) ) {
			$errors[] = "Permission denied";
		} else if ( ! is_user_logged_in() ) {
			$errors[] = "User not logged in";
		}

		if ( ! empty( $errors ) )
			self::ajaxError( $errors );

		switch( $timeline_action ) {
			case 'hide_post':
				$status = "OK";
				$results['rows_updated'] = self::ajaxHide( $timeline_params );
				break;

			case 'unhide_post':
				$status = "OK";
				$results['rows_updated'] = self::ajaxUnhide( $timeline_params );
				break;

			case 'clear_error_log':
				$status = "OK";
				$results['message'] = TimelineError::clear();
				break;

			default:
				$status = "ERROR";
				$errors[] = "Unknown action";
				break;
		}

		if ( $status == "ERROR" )
			self::ajaxError( $errors );

		$response = array(
			'status' => $status,
			'results' => $results
			);
		echo json_encode( $response );
		die();
	}

	/**
	 * Compile all of the ajax errors into one response object so it
	 * can be iterated over on the client side
	 * @param  array $errors the errors
	 */
	public static function ajaxError( $errors )
	{
		$response = array(
			'status' => 'ERROR',
			'errors' => $errors
			);
		echo json_encode( $response );
		die();
	}

	/**
	 * Hide a timeline post
	 * @param  array $params the parameters for the action
	 * @return int           the number of rows updated
	 */
	public static function ajaxHide( $params )
	{
		if ( ! is_array( $params ) || ! array_key_exists( 'id', $params ) )
			return false;

		return TimelinePost::hide( $params['id'] );
	}


	/**
	 * Unhide a timeline post
	 * @param  array $params the parameters for the action
	 * @return int           the number of rows updated
	 */
	public static function ajaxUnhide( $params )
	{
		if ( ! is_array( $params ) || ! array_key_exists( 'id', $params ) )
			return false;

		return TimelinePost::unhide( $params['id'] );
	}

	/**
	 * Hooked into admin_menu - add the plugin menu items the the WordPress backend
	 */
	public static function addMenus()
	{
		$page = add_menu_page(
				'Timeline',
				'Timeline',
				'activate_plugins',
				'timeline',
				array( 'Timeline', 'pageContent' )
				);

		add_action( 'admin_print_styles-' . $page, array( 'Timeline', 'pageStyles' ) );
		add_action( 'admin_print_scripts-' . $page, array( 'Timeline', 'pageScripts' ) );
		
		$settings = add_submenu_page(
					'timeline',
					'Timeline Settings',
					'Settings',
					'activate_plugins',
					'timeline-settings',
					array( 'Timeline', 'settingsPageContent' )
					);

		add_action( 'admin_print_styles-' . $settings, array( 'Timeline', 'pageStyles' ) );
		add_action( 'admin_print_scripts-' . $settings, array( 'Timeline', 'pageScripts' ) );
	}

	/**
	 * Hooked into admin_print_styles - enqueue the timeline admin stylesheet
	 */
	public static function pageStyles()
	{
		wp_enqueue_style( 'timeline-admin-css', TIMELINE_PLUGIN_URI . "/styles/admin.css" );
	}

	/**
	 * Hooked into admin_print_scripts - enqueue the timeline admin javascript
	 */
	public static function pageScripts()
	{
		wp_enqueue_script( 'timeline-admin-scripts', TIMELINE_PLUGIN_URI . "/scripts/admin.js", array( 'jquery' ), '0.1' );
	}

	/**
	 * Loop through the timeline submitted timeline settings and save them to
	 * the database.
	 * @param  array $data the submission data
	 */
	public static function saveSettings( $data )
	{
		$providers = array();

		foreach ( $data as $option => $value ) {
			if ( strpos( $option, 'timeline_option_providers' ) !== false && is_array( $value ) ) {
				$providers = $value;
			} else if ( strpos( $option, 'timeline_option_' ) !== false ) {
				update_option( $option, $value );
			}
		}

		self::saveProviderSwitches( $providers );
		self::$active_providers = get_option( 'timeline_option_providers' );
	}

	/**
	 * Check if a checkbox's value has been submitted, if not, disable it's
	 * option in the database
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public static function saveProviderSwitches( $value )
	{
		$cleaned = array();

		foreach ( self::$available_providers as $provider )
			$cleaned[ $provider ] = array_key_exists( $provider, $value ) ? 1 : 0;

		update_option( 'timeline_option_providers', $cleaned );
	}	

	/**
	 * Get the page content for the timeline from the main admin page template
	 */
	public static function pageContent()
	{ 
		$posts = TimelinePost::all();
		require_once( 'templates/admin_timeline.php' );
	}

	/**
	 * Check for any post data before getting the page content from the settings
	 * page template file.
	 */
	public static function settingsPageContent()
	{
		if ( isset( $_POST['page'] ) && $_POST['page'] == 'timeline_settings' )
			self::saveSettings( $_POST );

		$timeline_option_providers = self::$active_providers;
		$errors = TimelineError::get();

		require_once( 'templates/admin_settings.php' );	
	}

}

?>