<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access.' ); ?>

<div class="wrap">
	
	<?php screen_icon(); ?>
	<h2 class="title">Clear URL Cache on WP Engine</h2>

	<form method="post" action="">
		<?php wp_nonce_field( 'clear_url_cache', '_cftp_clear_url_nonce' ); ?>
		<p>
			<label for="url">
				The URL to clear cache for:
				<input type="text"name="cached_url" class="regular-text" value="" />
			</label>
		</p>
		<?php submit_button( 'Clear Cache' ); ?>
	</form>
	
</div>

