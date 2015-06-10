<?php
/*
  Plugin Name: Currency Converter
  Description: Currency Converter for any currency in the world. Allows selection of colors, size, layout, default currencies and values.
  Author: enclick
  Version: 2.1
  Author URI: http://fx-rate.net
  Plugin URI: http://fx-rate.net/wordpress-currency-converter-plugin/
*/

require_once("functions.php");
static $currency_list;

/**
 * Add function to widgets_init that'll load our widget.
 */

add_action( 'widgets_init', 'load_currency_converter' );

/**
 * Register our widget.
 * 'currency_converter' is the widget class used below.
 *
 */
function load_currency_converter() {
	register_widget( 'currency_converter' );
}



/*******************************************************************************************
 *
 *       Currency Converter  class.
 *       This class handles everything that needs to be handled with the widget:
 *       the settings, form, display, and update.
 *
 *********************************************************************************************/
class currency_converter extends WP_Widget
{

	/*******************************************************************************************
	 *
	 *
	 * Widget setup.
	 *
	 *
	 ********************************************************************************************/
	function currency_converter() {
		#Widget settings
		$widget_ops = array( 'description' => __('Currency Converter for any currency in the world', 'currency_conveter') );

		#Widget control settings
		$control_ops = array( 'width' => 200, 'height' => 550, 'id_base' => 'currency_converter' );

		#Create the widget
		$this->WP_Widget( 'currency_converter', __('Currency Converter', 'currency_converter'), $widget_ops, $control_ops );
	}



	/*******************************************************************************************
	 *
	 *
	 * Update the widget settings.
	 *
	 *
	 *******************************************************************************************/
	function update( $new_instance, $old_instance )
	{

		if(empty($currency_list)){
			$file_location = dirname(__FILE__)."/currencies.ser";
			if ($fd = fopen($file_location,'r')){
				$currency_list_ser = fread($fd,filesize($file_location));
				fclose($fd);
			}
			$currency_list = array();
			$currency_list = unserialize($currency_list_ser);
		}

		$instance = $old_instance;

		$instance['currency_code'] =  strip_tags(stripslashes($new_instance['currency_code']));
		$currency_code = $instance['currency_code'] ;
		$instance['currency_name'] =  strip_tags(stripslashes($currency_list[$currency_code]['currency_name']));
		$instance['country_code'] =  strip_tags(stripslashes($currency_list[$currency_code]['country_code']));
		$instance['title'] =  strip_tags(stripslashes($instance['currency_name'])) ;

		$instance['length'] = strip_tags(stripslashes($new_instance['length']));
		$instance['label_type'] = strip_tags(stripslashes($new_instance['label_type']));
		$instance['background_color'] = strip_tags(stripslashes($new_instance['background_color']));
		$instance['border_color'] = strip_tags(stripslashes($new_instance['border_color']));
		$instance['text_color'] = strip_tags(stripslashes($new_instance['text_color']));
		$instance['layout'] = strip_tags(stripslashes($new_instance['layout']));
		$instance['width'] = strip_tags(stripslashes($new_instance['width']));

		$instance['default_amount'] = strip_tags(stripslashes($new_instance['default_amount']));
		$instance['default_from'] = strip_tags(stripslashes($new_instance['default_from']));
		$instance['default_to'] = strip_tags(stripslashes($new_instance['default_to']));
		$instance['transparentflag'] = strip_tags(stripslashes($new_instance['transparentflag']));
		$instance['tflag'] = strip_tags(stripslashes($new_instance['tflag']));

		return $instance;
	}


	/*
	 *      Displays the widget settings controls on the widget panel.
	 *      Make use of the get_field_id() and get_field_name() function
	 *      when creating your form elements. This handles the confusing stuff.
	 *
	 *
	 ********************************************************************************************/
	function form( $instance )
	{

		#
		#       Set up some default widget settings
		#


		if(empty($currency_list)){
			$file_location = dirname(__FILE__)."/currencies.ser"; 
			if ($fd = fopen($file_location,'r')){
				$currency_list_ser = fread($fd,filesize($file_location));
	   	  	    fclose($fd);
			}

			$currency_list = array();
			$currency_list = unserialize($currency_list_ser);	
		}


		$defaults = array(
			'currency_code'=>'',
			'currency_name'=>'',
			'title'=>'Euro',
			'country_code' => '',
			'layout' => 'vertical',
			'length' => 'medium',
			'width' => '150',
			'default_amount' => '100',
			'default_from' => 'USD',
			'default_to' => 'EUR',
			'text_color' => '#000000',
			'border_color' => '#BBBBBB',
			'background_color' => '#FFFFFF',
			'transparentflag'=>'0',
			'tflag'=>'0'
			);


   		if(!isset($instance['layout']))
			$instance = $defaults;


		// Extract value from vars
		$currency_code = htmlspecialchars($instance['currency_code'], ENT_QUOTES);
		$currency_name = htmlspecialchars($instance['currency_name'], ENT_QUOTES);
		$title = $currency_name;
		$country_code = htmlspecialchars($instance['country_code'], ENT_QUOTES);
		$length = htmlspecialchars($instance['length'], ENT_QUOTES);
		$layout = htmlspecialchars($instance['layout'], ENT_QUOTES);
		$width = htmlspecialchars($instance['width'], ENT_QUOTES);
		$default_amount = htmlspecialchars($instance['default_amount'], ENT_QUOTES);
		$default_from = htmlspecialchars($instance['default_from'], ENT_QUOTES);
		$default_to = htmlspecialchars($instance['default_to'], ENT_QUOTES);
		$text_color = htmlspecialchars($instance['text_color'], ENT_QUOTES);
		$border_color = htmlspecialchars($instance['border_color'], ENT_QUOTES);
		$background_color = htmlspecialchars($instance['background_color'], ENT_QUOTES);
		$transparentflag = htmlspecialchars($instance['transparentflag'], ENT_QUOTES);
		$tflag = htmlspecialchars($instance['tflag'], ENT_QUOTES);

		#
		#
		#       START FORM OUTPUT
		#
		#

          
		// Get currency, length and label type 

		echo '<p><label for="' .$this->get_field_id( 'currency_code' ). '">Currency:'.
			'<select id="' .$this->get_field_id( 'currency_code' ). '" name="' .$this->get_field_name( 'currency_code' ). '" style="width:125px">';
		echo '<OPTION value=""></option>';
		cc_print_thecurrency_list($currency_code, $currency_list);
		echo '</select></label></p>';

		// Set layout type
		echo '<p><label for="' .$this->get_field_id( 'layout' ). '">'.'Layout:&nbsp;&nbsp;';
		echo '<select id="' .$this->get_field_id( 'layout' ). '" name="' .$this->get_field_name( 'layout' ). '"  style="width:120px" >';
		cc_print_layout_list($layout);
		echo '</select></label>';
		echo '</p>';

		if(empty($currency_code) && $layout != "horizontal"){
			// Set Length
			echo "\n";
			echo '<p><label for="' .$this->get_field_id( 'length' ). '">'.'Length: &nbsp;';
			echo '<select id="' .$this->get_field_id( 'length' ). '" name="' .$this->get_field_name( 'length' ). '"  style="width:75px">';
			cc_print_thelength_list($length);
			echo '</select></label></p>';
		}

		// Set Width
		echo "\n";
		echo '<p><label for="' .$this->get_field_id( 'width' ). '">'.'Width: &nbsp;&nbsp;&nbsp;';
		echo '<select id="' .$this->get_field_id( 'width' ). '" name="' .$this->get_field_name( 'width' ). '"  style="width:75px">';
		cc_print_thewidth_list($width);
		echo '</select></label></p>';

		// Set Default Amount
		echo "\n";
		echo '<p><label for="' .$this->get_field_id( 'default_amount' ). '">'.'Default Amount: &nbsp;&nbsp;&nbsp;';
		echo '<input id="' .$this->get_field_id( 'default_amount' ). '" name="' .$this->get_field_name( 'default_amount' ). '"  style="width:40px;" value="' .$default_amount .'">';
		echo '</input></label></p>';

		// Set Default To Currency
		if(empty($currency_code)){
			echo '<p><label for="' .$this->get_field_id( 'default_to' ). '">To Currency: &nbsp;&nbsp;&nbsp;'.
				'<select id="' .$this->get_field_id( 'default_to' ). '" name="' .$this->get_field_name( 'default_to' ). '" style="width:125px" >';
			echo '<OPTION value=""></option>';
			cc_print_thecurrency_list($default_to, $currency_list);
			echo '</select></label></p>';
		}
		else{
			echo '<label for="' .$this->get_field_id( 'default_to' ). '">';
			echo '<input type="hidden" id="' .$this->get_field_id( 'default_to' ). '" name="' .$this->get_field_name( 'default_to' ). '" value="EUR"></input>';
			echo '</label>';
		}

		// Set Default From Currency
		echo '<p><label for="' .$this->get_field_id( 'default_from' ). '">From Currency:'.
			'<select id="' .$this->get_field_id( 'default_from' ). '" name="' .$this->get_field_name( 'default_from' ). '" style="width:125px">';
		echo '<OPTION value=""></option>';
		cc_print_thecurrency_list($default_from, $currency_list);
		echo '</select></label></p>';


		// Set Text Widget color
		echo '<p><label for="' .$this->get_field_id( 'text_color' ). '">'.'Text Color: &nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<select id="' .$this->get_field_id( 'text_color' ). '" name="' .$this->get_field_name( 'text_color' ). '"  style="width:95px" >';
		cc_print_textcolor_list($text_color);
		echo '</select></label>';
		echo '</p>';

		// Set Border Widget color
		echo '<p><label for="' .$this->get_field_id( 'border_color' ). '">'.'Header Color:&nbsp;';
		echo '<select id="' .$this->get_field_id( 'border_color' ). '" name="' .$this->get_field_name( 'border_color' ). '"  style="width:95px" >';
		cc_print_bordercolor_list($border_color);
		echo '</select></label>';
		echo '</p>';

		// Set Background Widget color
		echo '<p><label for="' .$this->get_field_id( 'background_color' ). '">'.'Background Color:&nbsp;';
		echo '<select id="' .$this->get_field_id( 'background_color' ). '" name="' .$this->get_field_name( 'background_color' ). '"  style="width:95px" >';
		cc_print_backgroundcolor_list($background_color);
		echo '</select></label>';
		echo '</p>';


		//   Transparent option

		$transparent_checked = "";
		if ($transparentflag =="1")
			$transparent_checked = "CHECKED";
		echo "\n";
		echo '<p><label for="' .$this->get_field_id( 'transparentflag' ). '"> Transparent: 
		<input type="checkbox" id="' .$this->get_field_id( 'transparentflag' ). '" name="' .$this->get_field_name( 'transparentflag' ). '" value=1 '.$transparent_checked.' /> 
		</label></p>';

		$title_checked = "";
		if ($tflag =="1")
	     	$title_checked = "CHECKED";

		echo "\n";
		echo '<p><label for="' .$this->get_field_id( 'tflag' ). '"> Widget Title & fx-rate Link: 
	     <input type="checkbox" id="' .$this->get_field_id( 'tflag' ). '" name="' .$this->get_field_name( 'tflag' ). '" value=1 '.$title_checked.' /> 
	     </label></p>';


        echo '<label for="' .$this->get_field_id( 'title' ). 'title"> <input type="hidden" id="' .$this->get_field_id( 'title' ). '" name="' .$this->get_field_id( 'title' ). '" value="'.$title.'" /> </label>';



    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    //	OUTPUT TABLE WIDGET
    //
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    function widget($args , $instance)
    {


		// Get values 
      	extract($args);

      	// Extract value from vars
      	$currency_code = htmlspecialchars($instance['currency_code'], ENT_QUOTES);
		$currency_name = htmlspecialchars($instance['currency_name'], ENT_QUOTES);
		$title = $currency_name;
      	$country_code = htmlspecialchars($instance['country_code'], ENT_QUOTES);
      	$length = htmlspecialchars($instance['length'], ENT_QUOTES);
      	$layout = htmlspecialchars($instance['layout'], ENT_QUOTES);
      	$length = htmlspecialchars($instance['length'], ENT_QUOTES);
      	$width = htmlspecialchars($instance['width'], ENT_QUOTES);
      	$default_amount = htmlspecialchars($instance['default_amount'], ENT_QUOTES);
      	$default_from = htmlspecialchars($instance['default_from'], ENT_QUOTES);
      	$default_to = htmlspecialchars($instance['default_to'], ENT_QUOTES);
      	$text_color = htmlspecialchars($instance['text_color'], ENT_QUOTES);
      	$border_color = htmlspecialchars($instance['border_color'], ENT_QUOTES);
      	$background_color = htmlspecialchars($instance['background_color'], ENT_QUOTES);
      	$transparentflag = htmlspecialchars($instance['transparentflag'], ENT_QUOTES);
      	$tflag = htmlspecialchars($instance['tflag'], ENT_QUOTES);

		if($transparentflag == "1"){
			$background_color ="";
			$border_color ="";
		}

		if($currency_code)
			$length = "medium";

		$text_color = str_replace("#","",$text_color);


		// Output calculator

		$widget_call_string = 'http://fx-rate.net/wp_converter.php?';
		if($currency_code) $widget_call_string .= 'currency='.$currency_code ."&";
		$widget_call_string .="size=". $length;
		$widget_call_string .="&layout=". $layout;
		$widget_call_string .="&amount=". $default_amount;
		$widget_call_string .="&tcolor=". $text_color;
		if(!empty($default_from))
			$widget_call_string .="&default_pair=". $default_from . "/" . $default_to;

		$country_code = strtolower($country_code);
        $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
		$image_url = $plugin_url . '/countries/'.$country_code.'.png';

       	$calc_label= strtoupper(substr($layout,0,1));
       	if($length == "short") $calc_label .= "S";


		if($currency_code){
			$target_url= "http://fx-rate.net/$currency_code/";
			$flag_string = '<img style="margin:0;padding:0;border:0;" src="'.$image_url.'" border=0 >&nbsp;<b>';
			$flag_string2 = '</b>';
			$title = UCWords($currency_name) . " Converter";
			$calc_label .=  "1";
		}
		else{
			$target_url= "http://fx-rate.net/";
			$title = "Currency Converter";
		}
	
		$tsize=12;
		if($layout == "vertical" && $length =="short") $tsize = 10;


		if($tflag != 1){
			$noscript_start = "<noscript>";
			$noscript_end = "</noscript>";
		}

		#
		#		OUTPUT HTML
		#

		echo $before_widget; 


		// Output title
		echo $before_title . $after_title; 
		//width:'.$width.'px;
		echo '<!-Currency Converter widget - HTML code - fx-rates.net -->
	     <div  style="background-color:'.$background_color.';border:2px solid #888;text-align:center;margin: auto; padding: 0px;margin-top:10px!important">';

			echo $noscript_start;
			echo '<div style="margin: 0px; padding: 0px;text-align:center;align:center;background-color:'.$border_color. ';border-bottom:1px solid #888;width:100%">
	     <a class="'.$calc_label.'label" 
	     style="font-size:'.$tsize.'px!important;line-height:16px!important;font-family:arial;text-weight:bold;margin-bottom:6px;text-decoration:none;color:#'.$text_color.'" href="'.$target_url.'">';

			echo $flag_string;
	
			echo $title.$flag_string2.'</a></div>';

			echo $noscript_end;


			echo'<script type="text/javascript" src="'.$widget_call_string.'"></script></div><!-end of code-->';





			echo $after_widget;


    }
  

}




?>