<div class="wrap" id="container">
	<h2>Timeline Settings</h2>

	<form action="<?php echo admin_url( 'admin.php?page=timeline-settings&updated=true' ) ?>" method="post">
		<div class="service-box half-width left" id="timeline-settings">
			<h3>General Settings</h3>
			<?php if ( isset( $_GET['updated'] ) && $_GET['updated'] === 'true' ) : ?>
				<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>
			<?php endif; ?>
				<p>
					<label for="timeline_option_general[update_interval]">Update interval</label>
					<input class="small-text" type="text" name="timeline_option_general[update_interval]" value="<?php echo get_option( 'timeline_option_general' )['update_interval'] ?>" /> minutes<br>
					<span class="description">The amount of time to wait before checking the provider servers for new data. Raise this number if you're getting experiencing slow-downs or errors.</span>
				</p>
				<p>
					<label for="timeline_option_general[max_posts]">Maximum posts</label>
					<input class="small-text" type="text" name="timeline_option_general[max_posts]" value="<?php echo get_option( 'timeline_option_general' )['max_posts'] ?>" /><br>
					<span class="description">The maximum number of timeline posts to display before the 'More' link</span>
				</p>
		</div>

		<div class="service-box half-width right" id="timeline-errors">
			<h3>Error Log <span id="clear-log">clear log</span></h3>
			<ul id="log">
				<?php if ( $errors ) { 
					foreach ( $errors as $error ) { ?>
						<li><?php echo '[' . $error->time . '] [' . $error->provider . '] [' . $error->severity . '] ' . $error->message ?></li>
					<?php }
					} else { ?>
						<li>No errors :)</li>
				<?php } ?>
			</ul>
		</div>

		<h2>Providers</h2>
		<!--Twitter-->
		<div class="service-box <?php echo $timeline_option_providers['twitter'] ? '' : 'disabled' ?>" id="twitter-options">
			<h3 class="toggle-header"><span class="tick">&#10004;</span><span class="cross">&#10006;</span>Twitter <span class="toggle">click to toggle</span></h3>
			<input class="hidden" type="checkbox" name="timeline_option_providers[twitter]" value="1" <?php checked( $timeline_option_providers['twitter'] ) ?> />
			
			<p>
				<label for="timeline_option_twitter[username]">Username</label>
				<input type="text" name="timeline_option_twitter[username]" value="<?php echo get_option( 'timeline_option_twitter' )['username'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[consumer_key]">Consumer Key</label>
				<input class="input-large" type="text" name="timeline_option_twitter[consumer_key]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_key'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[consumer_secret]">Consumer Secret</label>
				<input class="input-large" type="text" name="timeline_option_twitter[consumer_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_secret'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[access_token]">Access Token</label>
				<input class="input-large" type="text" name="timeline_option_twitter[access_token]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[access_token_secret]">Access Token Secret</label>
				<input class="input-large" type="text" name="timeline_option_twitter[access_token_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token_secret'] ?>" />
			</p>
		</div>
		
		<div class="service-box <?php echo $timeline_option_providers['github'] ? '' : 'disabled' ?>" id="github-options">
			<h3 class="toggle-header"><span class="tick">&#10004;</span><span class="cross">&#10006;</span>GitHub <span class="toggle">click to toggle</span></h3>
			<input class="hidden" type="checkbox" name="timeline_option_providers[github]" value="1" <?php checked( $timeline_option_providers['github'] ) ?> />
			<p>
				<label for="timeline_option_github[username]">Username</label>
				<input type="text" name="timeline_option_github[username]" value="<?php echo get_option( 'timeline_option_github' )['username'] ?>" />
			</p>
		</div>
	
		<!--WordPress-->
		<div class="service-box <?php echo $timeline_option_providers['wordpress'] ? '' : 'disabled' ?>" id="wordpress-options">
			<h3 class="toggle-header"><span class="tick">&#10004;</span><span class="cross">&#10006;</span>WordPress <span class="toggle">click to toggle</span></h3>
			<input class="hidden" type="checkbox" name="timeline_option_providers[wordpress]" value="1" <?php checked( $timeline_option_providers['wordpress'] ) ?> />
			<p>The Timeline is now monitoring posts on this blog. Whenever a new post is published, it'll be added to the timeline.</p>
		</div>

		<!--Submit-->
		<input type="hidden" name="page" value="timeline_settings" />
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>