<ol id="timeline">

<?php
if ( $posts ) {
	$i = 0;
	foreach ( $posts as $post ) {
		if ( $post->service == 'end' ) { ?>
			<li class="timeline-item <?php echo strtolower( $post->service ); echo $i == 0 ? ' latest' : ''; ?>">
				<div class="left-margin"></div>
			</li>
		<?php } else if ( $post->service == 'more' ) { ?>
			<li class="timeline-item <?php echo strtolower( $post->service ); echo $i == 0 ? ' latest' : ''; ?>">
				<div class="left-margin"><img src="<?php echo TIMELINE_PLUGIN_URI ?>/images/<?php echo strtolower( $post->service ) ?>-32.png" alt="<?php echo $post->service ?> logo" /></div>
				<div class="right-margin">
					<p class="content"><a id="timeline-more">Load more posts</a></p>
				</div>
			</li>
		<?php } else if ( ! $post->hidden ) {
		?>
			<li class="timeline-item <?php echo strtolower( $post->service ); echo $i == 0 ? ' latest' : ''; ?>">
				<div class="left-margin">
					<img src="<?php echo TIMELINE_PLUGIN_URI ?>/images/<?php echo strtolower( $post->service ) ?>-32.png" alt="<?php echo $post->service ?> logo" />
				</div>
				<div class="right-margin">
					<p class="content"><?php echo $post->content ?></p>
					<p class="byline"><span id="datetime"><?php echo date( 'd/m/y H:i:s', $post->time ) ?></span> via <a href="#" class="vialink"><?php echo $post->service ?></a>
					</p>
				</div>
			</li>
	<?php 
	}
		$i++;
	}
} ?>

</ol>