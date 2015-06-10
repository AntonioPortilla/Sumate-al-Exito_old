<?php echo $before_form ?>
<div class="op_signup_form<?php echo (isset($color_scheme) ? ' op-signup-style-'.$color_scheme : '')?>">
	<?php if ($color_scheme>=0 && $color_scheme<9){ ?>
		<?php op_get_var_e($content,'title','','<h2>%1$s</h2>') ?>
		<?php op_get_var_e($content,'form_header','','<p>%1$s</p>') ?>
		<?php echo $form_open ?>
		<div>
			<?php
				echo $hidden_elems;
				if (isset($order)) {
					foreach ($order as $field) {
						if ($field === 'email_input') {
							echo $email_input;
						} else if ($field === 'name_input' && isset($name_input)) {
							echo $name_input;
						} else if (isset($extra_fields[$field])) {
							op_get_var_e($extra_fields, $field);
						}
					}
				} else {
					echo (isset($name_input) ? $name_input : '').$email_input;
					if(isset($extra_fields)){
						echo implode('',$extra_fields);
					}
				}				
				?>
				<?php wp_nonce_field('op_optin', 'op_optin_nonce'); ?>
				<?php op_mod('submit_button')->output(array('submit_button'),array(),$submit_button); ?>
		</div>
		<?php echo $form_close ?>
		<?php op_get_var_e($content,'footer_note','','<p class="secure-icon">%1$s</p>') ?>
	<?php } elseif ($color_scheme>=0 && $color_scheme<9){ ?>
		<?php op_get_var_e($content,'headline','','<h2>%1$s</h2>') ?>
		<div class="optin-box-content">
			<?php op_get_var_e($content,'paragraph','') ?>
			<?php echo $form_open.$hidden_str ?>
				<?php op_get_var_e($fields,'email_field'); echo implode('',$extra_fields) ?>
				<?php wp_nonce_field('op_optin', 'op_optin_nonce'); ?>
				<?php echo $submit_button ?>
			</form>
			<?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
		</div>
	<?php } ?>
</div>
<?php echo $after_form ?>