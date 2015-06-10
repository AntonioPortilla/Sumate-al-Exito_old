<div class="wrap">
<h2>Google AdWords Remarketing</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<?php settings_fields('google_adwords_remarketing'); ?>

<p>Insert your Remarketing code below:</p>

<textarea name="remarketing_code" id="remarketing_code" rows="25"  cols="65" >
<?php echo get_option('remarketing_code'); ?> </textarea>


<input type="hidden" name="action" value="update" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
Have a question? Drop us a question at <a href="http://onlineads.lt/?utm_source=WordPress&utm_medium=Google+AdWords+Remarketing+-+Options+page&utm_campaign=WordPress+plugins" title="Google AdWords Remarketing">OnlineAds.lt</a>
</div>
