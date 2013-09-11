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
						<?php if ( strtolower( $post->service ) != 'wordpress' ) : ?>
							<a data-id="<?php echo $post->id ?>" data-hidden="<?php echo $post->hidden ? 'true' : 'false' ?>" class="hide-button"><?php echo $post->hidden ? 'unhide' : 'hide' ?></a>
						<?php endif; ?>
						</p>
					</div>
				</li>
		<?php 
			$i++;
		}
	} ?>

	</ol>
</div>