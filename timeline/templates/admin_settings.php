<div class="wrap" id="container">
	<h2>Timeline Settings</h2>

	<form action="" method="post">
		<!--Twitter-->
		<div>
			<p>
				<label for="timeline_option_providers[twitter]">Twitter</label>
				<input type="checkbox" name="timeline_option_providers[twitter]" value="1" <?php checked( $timeline_option_providers['twitter'] ) ?> />
			</p>
			
			<p>
				<label for="timeline_option_twitter[username]">Username</label>
				<input type="text" name="timeline_option_twitter[username]" value="<?php echo get_option( 'timeline_option_twitter' )['username'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[consumer_key]">Consumer Key</label>
				<input type="text" name="timeline_option_twitter[consumer_key]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_key'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[consumer_secret]">Consumer Secret</label>
				<input type="text" name="timeline_option_twitter[consumer_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['consumer_secret'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[access_token]">Access Token</label>
				<input type="text" name="timeline_option_twitter[access_token]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token'] ?>" />
			</p>
			<p>
				<label for="timeline_option_twitter[access_token_secret]">Access Token Secret</label>
				<input type="text" name="timeline_option_twitter[access_token_secret]" value="<?php echo get_option( 'timeline_option_twitter' )['access_token_secret'] ?>" />
			</p>
		</div>
		
		<!--Facebook-->
		<label for="timeline_option_providers[facebook]">Facebook</label>
		<input type="checkbox" name="timeline_option_providers[facebook]" value="1" <?php checked( $timeline_option_providers['facebook'] ) ?> />
		
		<!--GitHub-->
		<div>
			<p>
				<label for="timeline_option_providers[github]">GitHub</label>
				<input type="checkbox" name="timeline_option_providers[github]" value="1" <?php checked( $timeline_option_providers['github'] ) ?> />
			</p>
			<p>
				<label for="timeline_option_github[username]">Username</label>
				<input type="text" name="timeline_option_github[username]" value="<?php echo get_option( 'timeline_option_github' )['username'] ?>" />
			</p>
		</div>
	
		<!--WordPress-->
		<div>
			<p>
				<label for="timeline_option_providers[wordpress]">WordPress</label>
				<input type="checkbox" name="timeline_option_providers[wordpress]" value="1" <?php checked( $timeline_option_providers['wordpress'] ) ?> />
			</p>
		</div>

		<!--Submit-->
		<input type="hidden" name="page" value="timeline_settings" />
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>