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

define( 'TIMELINE_VERSION', '0.1' );
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

	public static function install()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$sql = "CREATE TABLE $posts_table (
			id int(9) NOT NULL AUTO_INCREMENT,
			service varchar(45) NOT NULL,
			service_id varchar(45) NOT NULL,
			content varchar(2048) NOT NULL,
			attributes varchar(2048) NOT NULL,
			time int(10) NOT NULL,
			hidden int(1),
			UNIQUE KEY id (id)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function uninstall()
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'timeline';
		$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %s", $posts_table ) );

		if ( get_transient( 'timeline_wait' ) )
			delete_transient( 'timeline_wait' );
	}

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
				$$provider = $$provider->sync();
			}
		}

		set_transient( 'timeline_wait', true, 60*5 );
	}

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
				$results = self::ajaxHide( $timeline_params );
				break;

			case 'unhide_post':
				$status = "OK";
				$results = self::ajaxUnhide( $timeline_params );
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

	public static function ajaxError( $errors )
	{
		$response = array(
			'status' => 'ERROR',
			'errors' => $errors
			);
		echo json_encode( $response );
		die();
	}

	public static function ajaxHide( $params )
	{
		if ( ! is_array( $params ) || ! array_key_exists( 'id', $params ) )
			return false;

		return TimelinePost::hide( $params['id'] );
	}

		public static function ajaxUnhide( $params )
	{
		if ( ! is_array( $params ) || ! array_key_exists( 'id', $params ) )
			return false;

		return TimelinePost::unhide( $params['id'] );
	}

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

	public static function pageStyles()
	{
		wp_enqueue_style( 'timeline-admin-css', TIMELINE_PLUGIN_URI . "/styles/admin.css" );
	}

	public static function pageScripts()
	{
		wp_enqueue_script( 'timeline-admin-scripts', TIMELINE_PLUGIN_URI . "/scripts/admin.js", array( 'jquery' ), '0.1' );
	}

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
	}

	public static function saveProviderSwitches( $value )
	{
		if ( ! is_array( $value ) )
			return false;

		$cleaned = array();

		foreach ( self::$available_providers as $provider ) {
			if ( array_key_exists( $provider, $value ) ) {
				$cleaned[ $provider ] = 1;
			} else {
				$cleaned[ $provider ] = 0;
			}
		}

		update_option( 'timeline_option_providers', $cleaned );
	}

	public static function pageContent()
	{ 
		$posts = TimelinePost::all();
		?>

		<div class="wrap" id="container">
			<h2>Timeline</h2>

			<ol id="timeline">

			<?php
			if ( $posts ) {
				$i = 0;
				foreach ( $posts as $post ) { 
					?>
						<li class="timeline-item <?php echo strtolower( $post->service ); echo $post->hidden ? ' hidden' : ''; echo $i == 0 ? ' latest' : ''; ?>">
							<div class="left-margin">
								<img src="<?php echo TIMELINE_PLUGIN_URI ?>/images/<?php echo strtolower( $post->service ) ?>-32.png" alt="<?php echo $post->service ?> logo" />
							</div>
							<div class="right-margin">
								<p class="content"><?php echo $post->content ?></p>
								<p class="byline"><span id="datetime"><?php echo date( 'd/m/y H:i:s', $post->time ) ?></span> via <a href="#" class="vialink"><?php echo $post->service ?></a>
									<?php if ( strtolower( $post->service ) != 'wordpress' ) : ?><a id="hide-<?php echo $post->id ?>" class="hide-button"><?php echo $post->hidden ? 'unhide' : 'hide' ?></a><?php endif; ?>
								</p>
							</div>
						</li>
				<?php 
					$i++;
				}
			} ?>

			</ol>
		</div>

	<?php }

	public static function settingsPageContent()
	{ 
		if ( isset( $_POST['page'] ) && $_POST['page'] == 'timeline_settings' )
			self::saveSettings( $_POST );

		if ( get_transient( 'timeline_wait' ) )
			delete_transient( 'timeline_wait' );

		$timeline_option_providers = self::$active_providers;
		?>
		<div class="wrap" id="container">
			<h2>Timeline Settings</h2>

			<form action="" method="post">
				<!--Twitter-->
				<label for="timeline_option_providers[twitter]">Twitter</label>
				<input type="checkbox" name="timeline_option_providers[twitter]" value="1" <?php checked( $timeline_option_providers['twitter'] ) ?> />
				
				<label for="timeline_option_twitter[username]">Username</label>
				<input type="text" name="timeline_option_twitter[username]" value="<?php echo get_option( 'timeline_option_twitter' )['username'] ?>" />
				
				<label for="timeline_option_twitter[consumer_key]">Consumer Key</label>
				<input type="text" name="timeline_option_twitter[consumer_key]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_key'] ?>" />
				
				<label for="timeline_option_twitter[consumer_secret]">Consumer Secret</label>
				<input type="text" name="timeline_option_twitter[consumer_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_secret'] ?>" />
				
				<label for="timeline_option_twitter[access_token]">Access Token</label>
				<input type="text" name="timeline_option_twitter[access_token]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token'] ?>" />
				
				<label for="timeline_option_twitter[access_token_secret]">Access Token Secret</label>
				<input type="text" name="timeline_option_twitter[access_token_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token_secret'] ?>" />
				
				<!--Facebook-->
				<label for="timeline_option_providers[facebook]">Facebook</label>
				<input type="checkbox" name="timeline_option_providers[facebook]" value="1" <?php checked( $timeline_option_providers['facebook'] ) ?> />
				
				<!--GitHub-->
				<label for="timeline_option_providers[github]">GitHub</label>
				<input type="checkbox" name="timeline_option_providers[github]" value="1" <?php checked( $timeline_option_providers['github'] ) ?> />

				<label for="timeline_option_github[username]">Username</label>
				<input type="text" name="timeline_option_github[username]" value="<?php echo get_option( 'timeline_option_github' )['username'] ?>" />

				<input type="hidden" name="page" value="timeline_settings" />
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
			
				<!--WordPress-->
				<label for="timeline_option_providers[wordpress]">WordPress</label>
				<input type="checkbox" name="timeline_option_providers[wordpress]" value="1" <?php checked( $timeline_option_providers['wordpress'] ) ?> />				
			</form>
	<?php }

}

?>