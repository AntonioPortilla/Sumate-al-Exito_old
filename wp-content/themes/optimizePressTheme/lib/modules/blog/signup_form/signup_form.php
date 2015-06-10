<?php
class OptimizePress_Blog_Signup_Form_Module extends OptimizePress_Modules_Base {

	var $output_styles = array();
	var $output_defaults = array();

	function __construct($config=array()){
		parent::__construct($config);
		add_action((defined('OP_LIVEEDITOR')?'admin_print_footer_scripts':OP_SN.'-print-footer-scripts-admin'),array($this,'print_admin_footer_scripts'));
		op_mod('submit_button');
		//add_action('wp_footer',array($this,'print_front_scripts'));
		//add_filter(OP_SN.'-script-localize',array($this,'localize_script'));
	}

	function print_admin_footer_scripts(){
		echo '<script type="text/javascript" src="'.$this->url.'form'.OP_SCRIPT_DEBUG.'.js?ver='.OP_VERSION.'"></script>';
		//wp_enqueue_script(OP_SN.'form', $this->url.'form.js', array(OP_SN.'-noconflict-js'), OP_VERSION);

		$out = array('lang'=>array('order' => __('Order', OP_SN), 'required' => __('Required', OP_SN), 'add_new_field'=>__('Add New Field',OP_SN),'text'=>__('Text',OP_SN),'title'=>__('Title',OP_SN),'field_name'=>__('Field Name',OP_SN),'remove'=>__('Remove',OP_SN)));
		if(count($this->output_styles) > 0){
			$out['styles'] = $this->output_styles;
		}
		echo '
<script type="text/javascript">
var op_mod_signup_form = '.json_encode($out).';
</script>';
	}

	function print_front_scripts(){
		if(count($this->output_defaults) > 0){
			echo '<script type="text/javascript" src="'.$this->url.'form_output'.OP_SCRIPT_DEBUG.'.js?ver='.OP_VERSION.'"></script>';
			//wp_enqueue_script(OP_SN.'form_output', $this->url.'form_output.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
		}
	}

	function localize_script($data=array()){
		if(count($this->output_defaults) > 0){
			$data['signup_form'] = $this->output_defaults;
		}
		return $data;
	}

	function display_settings($section_name,$config=array(),$return=false){
		$tabs = array(
			'form_html' => __('Opt-In HTML', OP_SN),
			'color_scheme' => __('Colour Scheme', OP_SN),
			'content' => __('Content', OP_SN),
			'submit_button' => __('Submit Button', OP_SN),
		);
		$tab_content = array();
		$disable = $this->_get_disabled($config);
		foreach($disable as $d){
			unset($tabs[$d]);
		}

		$disable_name_options = false;
		if(isset($config['disable_name']) && $config['disable_name'] === true){
			$disable_name_options = true;
		}

		$content_fields = array();
		if(isset($tabs['content'])){
			$data = array('fields'=>$this->_get_fields($config),'ignore_fields'=>op_get_var($config,'ignore_fields',array()));
			if($disable_name_options){
				$data['ignore_fields'][] = 'name_default';
			}
			if(count($data['fields']) > 0){
				if(is_array($section_name)){
					$content = $section_name;
					$content[] = 'content';
				} else {
					$content = array($section_name,'content');
				}
				$tab_content['content'] = op_mod('content_fields')->display_settings($content,$data,true);
			} else {
				unset($tabs['content']);
			}
		}
		if(isset($tabs['submit_button'])){
			if(is_array($section_name)){
				$submit_button = $section_name;
				$submit_button[] = 'submit_button';
			} else {
				$submit_button = array($section_name,'submit_button');
			}
			$tab_content['submit_button'] = op_mod('submit_button')->display_settings($submit_button,op_get_var($config,'submit_button_config',array()),true);
		}
		$data = array(
			'id' => $this->get_fieldid($section_name),
			'fieldname' => $this->get_fieldname($section_name),
			'section_name' => $section_name,
			'module_name' => 'signup_form',
			'url' => $this->url,
			'add_wrapper' => op_get_var($config,'add_wrapper',true),
			'fields' => array(),
			'disable_name_options' => $disable_name_options,
		);
		if(isset($tabs['color_scheme'])){
			$data['color_schemes'] = $this->_get_color_schemes($section_name,$config);
		}
		if(isset($tabs['form_html'])){
			$fields = array('email_data'=>'N','email_address'=>'','redirect_url'=>'','html'=>'','new_window'=>'N', 'name_order' => 0, 'name_required' => 'Y', 'email_order' => 0,
							'disable_name'=>'N','name_box'=>'','email_box'=>'','method'=>'','action'=>'','extra_fields'=>array(),'email_extra_fields'=>array(), 'email_extra_fields_order' => array(), 'email_extra_fields_required' => array(),
							'integration_type' => 'custom', 'thank_you_page' => '', 'list' => null, 'action_page' => '', 'gotowebinar' => null, 'gotowebinar_enabled' => 'N', 'double_optin' => 'N');
			$section = $this->get_option($section_name,'form_html');
			if($section !== false && is_array($section)){
				foreach($fields as $field => $value){
					$fields[$field] = op_get_var($section,$field);
				}
			} elseif(isset($config['values'])){
				foreach($fields as $field => $value){
					$fields[$field] = op_get_var($config['values'],$field);
				}
			}
			$data['fields'] = $fields;
		}
		op_tpl_assign($data);
		foreach($tabs as $name => $tab){
			if(!isset($tab_content[$name])){
				$tab_content[$name] = $this->load_tpl($name);
			}
		}
		if(count($tabs) > 1){
			$data['tabs'] = $tabs;
			$data['tab_content'] = $tab_content;
			$data['content'] = $this->load_tpl('generic/tabbed_module', $data, false);
		} else {
			$data['content'] = $tab_content[key($tab_content)];
		}

		$data['content'] = ($return ? $data['content'] : str_replace('module-signup_form">', 'module-signup_form"><p class="module-help-text">'.__('To display the sidebar optin on your blog, please complete the settings below and then go to <a href="/wp-admin/widgets.php">Appearance > Widgets</a> on your Wordpress sidebar and drag the "OptimizePress: Sidebar Opt-in" widget into the Sidebar to position it where you wish.').'</p>', $data['content']));

		if($return) return $data['content']; else echo $this->load_tpl('index', $data);
	}

	function save_settings($section_name,$config=array(),$op,$return=false){
		if(!$cur = $this->get_option($section_name)){
			$cur = array();
		}
		$disabled = $this->_get_disabled($config);
		if(!isset($disabled['on_off_switch'])){
			$cur['enabled'] = op_get_var($op,'enabled','N');
		}
		if(!isset($disabled['form_html'])){
            // sometimes $cur is a string, casting it to array
            if (!is_array($cur)) {
                $cur = array();
            }
			$fields = array('email_data','email_address','redirect_url','extra_fields','email_extra_fields', 'email_order', 'name_order', 'name_required',
							'html','new_window','disable_name','name_box','email_box','action','fields','method', 'email_extra_fields_order', 'email_extra_fields_required',
							'integration_type', 'list', 'thank_you_page', 'action_page', 'gotowebinar', 'gotowebinar_enabled', 'double_optin');
			$defaults = array('extra_fields' => array(), 'email_extra_fields' => array(), 'new_window' => 'N', 'email_extra_fields_order' => array(), 'email_extra_fields_required' => array(),
							  'disable_name' => 'N', 'fields' => array(), 'method' => 'post', 'name_order' => 0, 'name_required' => 'Y', 'email_order' => 0,
							  'integration_type' => 'custom', 'double_optin' => 'N', 'gotowebinar_enabled' => 'N');
			$cur['form_html'] = array();
			$form_html = op_get_var($op,'form_html',array());
			foreach($fields as $i => $field){
				$val = op_get_var($form_html,$field,(isset($defaults[$i])?$defaults[$i]:''));
				if ($field == 'html') {
					$pattern = "/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i";
					$replacement = '';
					$val = preg_replace($pattern, $replacement, $val);

					$pattern = "/<textarea\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/textarea>/i";
					$replacement = '';
					$val = preg_replace($pattern, $replacement, $val);

					$pattern = "/<style\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/style>/i";
					$replacement = '';
					$val = preg_replace($pattern, $replacement, $val);
				}
				$cur['form_html'][$field] = is_array($val) ? $val : stripslashes($val);
			}
		}
		if(!isset($disabled['content'])){
			$data = array('fields' => $this->_get_fields($config));
			$cur['content'] = op_mod('content_fields')->save_settings(array($section_name,'content'),$data,op_get_var($op,'content',array()),true);
		}
		if(!isset($disabled['color_scheme'])){
			$schemes = $this->_get_color_schemes($section_name,$config);
			if(isset($op['color_scheme']) && isset($schemes[$op['color_scheme']])){
				$cur['color_scheme'] = $op['color_scheme'];
			}
		}
		if(!isset($disabled['submit_button'])){
			$cur['submit_button'] = op_mod('submit_button')->save_settings(array($section_name,'submit_button'),op_get_var($config,'submit_button_config',array()),op_get_var($op,'submit_button',array()),true);
		}
		if($return){
			return $cur;
		}
		$this->update_option($section_name,$cur);
	}

	function _get_fields($config=array()){
		$content_fields = op_optin_default_fields();
		if(isset($config['content_fields'])){
			$content_fields = $config['content_fields'];
		}
		return $content_fields;
	}

	function _get_color_schemes($section_name,$config=array()){
		$url = $this->url.'preview/';
		$styles = array(
			'style1' => array(
				'title' => __('Style 1',OP_SN),
				'preview' => $url.'style1.png'
			),
			'style2' => array(
				'title' => __('Style 2',OP_SN),
				'preview' => $url.'style2.png'
			),
			'style3' => array(
				'title' => __('Style 3',OP_SN),
				'preview' => $url.'style3.png'
			),
			'style4' => array(
				'title' => __('Style 4',OP_SN),
				'preview' => $url.'style4.png'
			),
			'style5' => array(
				'title' => __('Style 5',OP_SN),
				'preview' => $url.'style5.png'
			),
			'style6' => array(
				'title' => __('Style 6',OP_SN),
				'preview' => $url.'style6.png'
			),
			'style7' => array(
				'title' => __('Style 7',OP_SN),
				'preview' => $url.'style7.png'
			),
			'style8' => array(
				'title' => __('Style 8',OP_SN),
				'preview' => $url.'style8.png'
			),
			'style9' => array(
				'title' => __('Style 9',OP_SN),
				'preview' => $url.'style9.png'
			),
			'style10' => array(
				'title' => __('Style 10',OP_SN),
				'preview' => $url.'style10.png'
			),
			'style11' => array(
				'title' => __('Style 11',OP_SN),
				'preview' => $url.'style11.png'
			),
			'style12' => array(
				'title' => __('Style 12',OP_SN),
				'preview' => $url.'style12.png'
			),
			'style13' => array(
				'title' => __('Style 13',OP_SN),
				'preview' => $url.'style13.png'
			),
			'style14' => array(
				'title' => __('Style 14',OP_SN),
				'preview' => $url.'style14.png'
			),
			'style15' => array(
				'title' => __('Style 15',OP_SN),
				'preview' => $url.'style15.png'
			),
			'style16' => array(
				'title' => __('Style 16',OP_SN),
				'preview' => $url.'style16.png'
			)
		);
		if(isset($config['color_schemes'])){
			$styles = $config['color_schemes'];
		}
		$this->output_styles[(is_array($section_name) ? implode('_',$section_name): $section_name)] = $styles;
		return $styles;
	}

	function _get_disabled($config=array()){
		$disable = array();
		if(isset($config['disable'])){
			$disable = is_array($config['disable']) ? $config['disable'] : array_filter(explode('|',$config['disable']));
			$newdisable = array();
			foreach($disable as $d){
				$newdisable[$d] = $d;
			}
			$disable = $newdisable;
		}
		return $disable;
	}


	function output($section_name,$config,$op,$return=false){
		$disable = $this->_get_disabled($config);
		if(is_array($section_name[0]) && count($section_name) > 1){
			$tmp = $section_name;
			$section_name = $section_name[0];
			for($i=1,$sl=count($tmp);$i<$sl;$i++){
				array_push($section_name,$tmp[$i]);
			}
		}
		$data = $used_fields = array();
		if(isset($op['form_html'])){
			$order = array();
			$fh = $op['form_html'];
			$data['before_form'] = op_get_var($config,'before_form');
			$data['after_form'] = op_get_var($config,'after_form');
			$data['extra_fields'] = array();
			$form_class = '';
			$disable_name = (op_get_var($fh,'disable_name','N') == 'Y');
			$js_defaults = array();


			$fields = array('email');
			$order[op_get_var($fh, 'email_order', 0)][] = 'email_input';
			if(!$disable_name){
				array_unshift($fields,'name');
				$order[op_get_var($fh, 'name_order', 0)][] = 'name_input';
			}

			$required = 'required="required"';

			switch (op_get_var($fh, 'integration_type')) {
				case 'email':
					$email_address = op_get_var($fh,'email_address');
					$redirect_url = op_get_var($fh,'redirect_url');

					if(!isset($disable['content']) && isset($op['content'])){
						foreach($fields as $field){
							if(isset($op['content'][$field.'_default']) && !empty($op['content'][$field.'_default'])){
								$js_defaults[$field] = $op['content'][$field.'_default'];
							}
						}
						if(count($js_defaults) > 0){
							$count = count($this->output_defaults);
							$this->output_defaults[++$count] = $js_defaults;
							$form_class = 'op-signup-form-'.$count;
						}
					}

					$new_fields = array();
					foreach($fields as $field){
						if ($field == 'email') {
							$data[$field.'_input'] = '<input type="email" required="required" name="'.$field.'" placeholder="'.op_attr(op_get_var($js_defaults, $field)).'" class="'.$field.'" />';
						} else {
							$requiredField = '';
							if (!isset($fh['name_required']) || op_get_var($fh, 'name_required') == 'Y') {
								$requiredField = $required;
							}
							$data[$field.'_input'] = '<input type="text" ' . $requiredField . ' name="'.$field.'" placeholder="'.op_attr(op_get_var($js_defaults, $field)).'" class="'.$field.'" />';
						}
						$new_fields[$field] = array('name'=> $field,'text'=>op_get_var($js_defaults, $field));
					}
					$extra_fields = op_get_var($fh,'email_extra_fields',array());
					$extra_fields_order = op_get_var($fh, 'email_extra_fields_order', array());
					$extra_fields_required = op_get_var($fh, 'email_extra_fields_required', array());
					$new_extra = array();
					if(is_array($extra_fields)){
						$counter = 1;
						foreach($extra_fields as $field){
							$new_extra['op_extra_'.$counter] = $field;
							$requiredField = '';
							if (isset($extra_fields_required[$counter - 1]) && $extra_fields_required[$counter - 1] == 'Y') {
								$requiredField = $required;
							}
							$data['extra_fields']['op_extra_'.$counter] = '<input type="text" ' . $requiredField . ' name="op_extra_'.$counter.'" placeholder="'.op_attr($field).'" />';
							$field_order =
							$order[op_get_var($extra_fields_order, $counter - 1, 0)][] = 'op_extra_' . $counter;
							$counter++;
						}
					}
					$data['form_open'] = '<form action="'.op_current_url().'" method="post" class="op-optin-validation '.$form_class.'">';
					$data['form_close'] = '</form>';

					$hidden = array(
						'email_to' => $email_address,
						'redirect_url' => $redirect_url,
						'extra_fields' => $new_extra,
						'fields' => $new_fields
					);
					$data['hidden_elems'] = '<input type="hidden" name="op_optin_form_data" value="'.op_attr(base64_encode(serialize($hidden))).'" /><input type="hidden" name="op_optin_form" value="Y" />';
					break;
				case 'custom':
				case 'oneshoppingcart':
					if ('Y' === op_get_var($fh, 'gotowebinar_enabled', 'N')) {
						$action = get_bloginfo('url') . '/process-optin-form/';
					} else {
						$action = op_get_var($fh, 'action');
					}
					if($action != ''){
						if(!isset($disable['content']) && isset($op['content'])){
							foreach($fields as $field){
								if(isset($op['content'][$field.'_default']) && !empty($op['content'][$field.'_default'])){
									$js_defaults[$field] = $op['content'][$field.'_default'];
								}
							}
							if(count($js_defaults) > 0){
								$count = count($this->output_defaults);
								$this->output_defaults[++$count] = $js_defaults;
								$form_class = 'op-signup-form-'.$count;
							}
						}
						$data['form_open'] = '<form action="'.$action.'" method="'.op_get_var($fh,'method','post').'" class="op-optin-validation '.$form_class.'"'.(op_get_var($fh,'new_window','N') == 'Y' ? ' target="_blank"':'').'>';
						$data['form_close'] = '</form>';

						foreach($fields as $field){
							$fieldname = '';
							if(isset($fh[$field.'_box']) && !empty($fh[$field.'_box'])){
								$used_fields[$fh[$field.'_box']] = true;
								$fieldname = $fh[$field.'_box'];
							}
							if ('email' == $field) {
								$data[$field.'_input'] = '<input type="email" required="required" name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
							} else {
								$requiredField = '';
								if (!isset($fh['name_required']) || op_get_var($fh, 'name_required') == 'Y') {
									$requiredField = $required;
								}
								$data[$field.'_input'] = '<input type="text" ' . $requiredField . ' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
							}
						}

						$extra_fields = op_get_var($fh,'extra_fields',array());
						$field_names = op_get_var($extra_fields,'field_name',array());
						$field_titles = op_get_var($extra_fields,'title',array());
						$field_text = op_get_var($extra_fields,'text',array());
						$field_order = op_get_var($extra_fields,'order',array());
						$field_required = op_get_var($extra_fields,'required',array());
						for($i=0,$il=count($field_names);$i<$il;$i++){
							$fieldname = $field_names[$i];
							if($field_names[$i] == '' || $field_names[$i] == 'op_add_new_field'){
								$fieldname = $field_titles[$i];
							}
							$requiredField = '';
							if (isset($field_required[$i]) && $field_required[$i] == 'Y') {
								$requiredField = $required;
							}
							$data['extra_fields'][$fieldname] = '<input type="text" ' . $requiredField . ' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr($field_text[$i]).'" />';
							$order[op_get_var($field_order, $i, 0)][] = $fieldname;
							$used_fields[$fieldname] = true;
						}


						$hidden_elems = '';
						if(isset($fh['fields']) && isset($fh['fields']['field_name']) && isset($fh['fields']['field_vals'])){
							$field_names = $fh['fields']['field_name'];
							$field_vals = $fh['fields']['field_vals'];
							$field_count = count($field_names);
							if(is_array($field_names) && is_array($field_vals) && $field_count == count($field_vals)){
								for($i=0;$i<$field_count;$i++){
									if(!isset($used_fields[$field_names[$i]])){
										$hidden_elems .= '<input type="hidden" name="'.op_attr($field_names[$i]).'" value="'.op_attr($field_vals[$i]).'" />';
									}
								}
							}
						}
						$data['hidden_elems'] = $hidden_elems;
						$data['hidden_elems'] .= '<input type="hidden" name="redirect_url" value="' . op_get_var($fh, 'action') . '" />';
					}
					break;
				case 'infusionsoft':
					if (op_get_var($fh, 'gotowebinar')) {
						$action = get_bloginfo('url') . '/process-optin-form/';
					} else {
						$action = op_get_var($fh, 'action_page');
					}

					if(!isset($disable['content']) && isset($op['content'])){
						foreach($fields as $field){
							if(isset($op['content'][$field.'_default']) && !empty($op['content'][$field.'_default'])){
								$js_defaults[$field] = $op['content'][$field.'_default'];
							}
						}
						if(count($js_defaults) > 0){
							$count = count($this->output_defaults);
							$this->output_defaults[++$count] = $js_defaults;
							$form_class = 'op-signup-form-'.$count;
						}
					}
					$data['form_open'] = '<form action="'.esc_url($action).'" method="POST" class="op-optin-validation '.$form_class.'">';
					$data['form_close'] = '</form>';

					/*
					 * Hardcoding the email field name
					 */
					$fh['email_box'] = 'inf_field_Email';

					foreach($fields as $field){
						$fieldname = '';
						if(isset($fh[$field.'_box']) && !empty($fh[$field.'_box'])){
							$used_fields[$fh[$field.'_box']] = true;
							$fieldname = $fh[$field.'_box'];
						}
						if ('email' == $field) {
							$data[$field.'_input'] = '<input type="email" required="required" name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
						} else {
							$requiredField = '';
							if (!isset($fh['name_required']) || op_get_var($fh, 'name_required') == 'Y') {
								$requiredField = $required;
							}
							$data[$field.'_input'] = '<input type="text" ' . $requiredField .' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
						}
					}

					$extra_fields = op_get_var($fh,'extra_fields',array());
					$field_names = op_get_var($extra_fields,'field_name',array());
					$field_titles = op_get_var($extra_fields,'title',array());
					$field_text = op_get_var($extra_fields,'text',array());
					$field_order = op_get_var($extra_fields,'order',array());
					$field_required = op_get_var($extra_fields,'required',array());
					for($i=0,$il=count($field_names);$i<$il;$i++){
						$fieldname = $field_names[$i];
						if($field_names[$i] == '' || $field_names[$i] == 'op_add_new_field'){
							$fieldname = $field_titles[$i];
						}
						$requiredField = '';
						if (isset($field_required[$i]) && $field_required[$i] == 'Y') {
							$requiredField = $required;
						}
						$data['extra_fields'][$fieldname] = '<input type="text" ' . $requiredField . ' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr($field_text[$i]).'" />';
						$order[op_get_var($field_order, $i, 0)][] = $fieldname;
						$used_fields[$fieldname] = true;
					}
					$hidden_elems = '';
					if(isset($fh['fields']) && isset($fh['fields']['field_name']) && isset($fh['fields']['field_vals'])){
						$field_names = $fh['fields']['field_name'];
						$field_vals = $fh['fields']['field_vals'];
						$field_count = count($field_names);
						if(is_array($field_names) && is_array($field_vals) && $field_count == count($field_vals)){
							for($i=0;$i<$field_count;$i++){
								if(!isset($used_fields[$field_names[$i]])){
									$hidden_elems .= '<input type="hidden" name="'.op_attr($field_names[$i]).'" value="'.op_attr($field_vals[$i]).'" />';
								}
							}
						}
					}

					$thankYouPage = op_get_var($fh, 'thank_you_page');
					$thankYouPage = empty($thankYouPage) ? op_current_url() : $thankYouPage;

					$data['hidden_elems'] = $hidden_elems;
					$data['hidden_elems'] .= '<input type="hidden" name="provider" value="' . op_get_var($fh, 'integration_type') . '" />';
					$data['hidden_elems'] .= '<input type="hidden" name="redirect_url" value="' . $thankYouPage . '" />';
					$data['hidden_elems'] .= '<input type="hidden" name="list" value="' . op_get_var($fh, 'list') . '" />';
					break;
				case 'aweber':
				case 'mailchimp':
				case 'icontact':
				case 'getresponse':
				case 'campaignmonitor':
				case 'officeautopilot':
				case 'ontraport':
				case 'emma':
					$action = get_bloginfo('url') . '/process-optin-form/';

					if(!isset($disable['content']) && isset($op['content'])){
						foreach($fields as $field){
							if(isset($op['content'][$field.'_default']) && !empty($op['content'][$field.'_default'])){
								$js_defaults[$field] = $op['content'][$field.'_default'];
							}
						}
						if(count($js_defaults) > 0){
							$count = count($this->output_defaults);
							$this->output_defaults[++$count] = $js_defaults;
							$form_class = 'op-signup-form-'.$count;
						}
					}
					$data['form_open'] = '<form action="'.esc_url($action).'" method="POST" class="op-optin-validation '.$form_class.'">';
					$data['form_close'] = '</form>';

					/*
					 * Hardcoding the email field name
					 */
					$fh['email_box'] = 'email';
					foreach($fields as $field){
						$fieldname = '';
						if(isset($fh[$field.'_box']) && !empty($fh[$field.'_box'])){
							$used_fields[$fh[$field.'_box']] = true;
							$fieldname = $fh[$field.'_box'];
						}
						if ('email' == $field) {
							$data[$field.'_input'] = '<input type="email" required="required" name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
						} else {
							$requiredField = '';
							if (!isset($fh['name_required']) || op_get_var($fh, 'name_required') == 'Y') {
								$requiredField = $required;
							}
							$data[$field.'_input'] = '<input type="text" ' . $requiredField . ' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr(op_get_var($js_defaults,$field)).'" class="'.$field.'" />';
						}
					}

					$extra_fields = op_get_var($fh,'extra_fields',array());
					$field_names = op_get_var($extra_fields,'field_name',array());
					$field_titles = op_get_var($extra_fields,'title',array());
					$field_text = op_get_var($extra_fields,'text',array());
					$field_order = op_get_var($extra_fields,'order',array());
					$field_required = op_get_var($extra_fields,'required',array());
					for($i=0,$il=count($field_names);$i<$il;$i++){
						$fieldname = $field_names[$i];
						if($field_names[$i] == '' || $field_names[$i] == 'op_add_new_field'){
							$fieldname = $field_titles[$i];
						}
						$requiredField = '';
						if (isset($field_required[$i]) && $field_required[$i] == 'Y') {
							$requiredField = $required;
						}
						$data['extra_fields'][$fieldname] = '<input type="text" ' . $requiredField . ' name="'.op_attr($fieldname).'" value="' . getOptinUrlValue($fieldname) . '" placeholder="'.op_attr($field_text[$i]).'" />';
						$order[op_get_var($field_order, $i, 0)][] = $fieldname;
						$used_fields[$fieldname] = true;
					}

					$thankYouPage = op_get_var($fh, 'thank_you_page');
					$thankYouPage = empty($thankYouPage) ? op_current_url() : $thankYouPage;

					$data['hidden_elems'] = '<input type="hidden" name="provider" value="' . op_get_var($fh, 'integration_type') . '" />';
					$data['hidden_elems'] .= '<input type="hidden" name="redirect_url" value="' . $thankYouPage . '" />';
					$data['hidden_elems'] .= '<input type="hidden" name="list" value="' . op_get_var($fh, 'list') . '" />';
					if (op_get_var($fh, 'integration_type') === 'mailchimp') {
						$data['hidden_elems'] .= '<input type="hidden" name="double_optin" value="' . op_get_var($fh, 'double_optin') . '" />';
					}
					break;
			}
			if (op_get_var($fh, 'gotowebinar') && 'Y' === op_get_var($fh, 'gotowebinar_enabled')) {
				$data['hidden_elems'] .= '<input type="hidden" name="gotowebinar" value="' . op_get_var($fh, 'gotowebinar') . '" />';
			}
			$data['order'] = flatten_multidim_array($order, true);
		}

		$tpl = '';
		$out = '';
		$data['content'] = array();
		if(!isset($disable['content'])){
			$fields = $this->_get_fields($config);
			$data['content'] = op_mod('content_fields')->output(array($section_name,'content'),array('fields'=>$fields),op_get_var($op,'content',array()));
		}
		$btnconf = op_get_var($config,'submit_button_config',array());
		if(isset($op['submit_button'])){
			$btnop = $op['submit_button'];
		} else {
			$btnop = array();
			if(!isset($btnconf['type'])){
				$btnop['type'] = 0;
			}
			if(isset($btnconf['defaults']) && isset($btnconf['defaults']['content'])){
				$btnop['content'] = $btnconf['defaults']['content'];
			}
		}

		if (is_array($btnop)) {
			$data['submit_button'] = op_mod('submit_button')->save_settings(array($section_name,'submit_button'),$btnconf,$btnop,true);
		} else {
			$data['submit_button'] = $btnop;
		}

		if(!isset($disable['color_scheme'])){
			$styles = $this->_get_color_schemes($section_name,$config);
			if(count($styles) > 0){
				$tpl = 'output';
				$style = op_get_current_item($styles,op_get_var($op,'color_scheme'));
				if(isset($styles[$style]['output'])){
					$tpl = $styles[$style]['output'];
				}
				$data['color_scheme'] = $style;
			}
		}
		if(isset($config['template'])){
			$tpl = $config['template'];
		}
		if(!empty($tpl)){
			$out = $this->load_tpl('output/'.$tpl,$data);
		}

		/*
		 * Loading validation script
		 */
		op_validation_script();

		if(!empty($out)){
			if($return){
				return $out;
			}
			echo $out;
			return true;
		} else {
			return $data;
		}
	}
}
function op_optin_default_fields()
{
	static $content_fields;
	if(!isset($content_fields)){
		$content_fields = array(
			'title' => array(
				'name' => __('Title',OP_SN),
				'help' => __('Enter a title to be displayed above your optin form',OP_SN),
				'default' => __('Sell Anything With OptimizePress',OP_SN),
			),
			'form_header' => array(
				'name' => __('Form Header',OP_SN),
				'help' => __('Enter a call to action for your form - tell the visitor what to do (e.g. Complete the form below&hellip;)',OP_SN),
				'default' => __('Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod.',OP_SN),
			),
			'footer_note' => array(
				'name' => __('Footer Security Note',OP_SN),
				'help' => __('Enter your security or spam notice here to reassure your visitors their details will be safe with you',OP_SN),
				'default' => __('Your information is 100% secure with us and will never be shared',OP_SN),
			),
			'name_default' => array(
				'name' => __('Name Default Value',OP_SN),
				'help' => __('Enter the default text for the Name input field',OP_SN),
				'default' => __('Enter your First Name...',OP_SN),
			),
			'email_default' => array(
				'name' => __('Email Default Value',OP_SN),
				'help' => __('Enter the default text for the Email input field',OP_SN),
				'default' => __('Enter your Email Address...',OP_SN),
			),
			/*'submit_button' => array(
				'name' => __('Submit Button',OP_SN),
				'help' => __('Enter the text for the submit button on your feature area optin form',OP_SN),
				'default' => __('Take The Tour...',OP_SN),
			)*/
		);
	}
	return $content_fields;
}