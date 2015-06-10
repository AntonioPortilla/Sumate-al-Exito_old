<?php
class OptimizePress_Sections_Email_Marketing_Services {
	function sections(){
		static $sections;
		if(!isset($sections)){
			$sections = array(
				'aweber' => array(
					'title' => __('AWeber', OP_SN),
					'action' => array($this,'aweber'),
					'save_action' => array($this,'save_aweber')
				),
				'icontact' => array(
					'title' => __('iContact', OP_SN),
					'action' => array($this,'icontact'),
					'save_action' => array($this,'save_icontact')
				),
				'mailchimp' => array(
					'title' => __('MailChimp', OP_SN),
					'action' => array($this,'mailchimp'),
					'save_action' => array($this,'save_mailchimp')
				),
				'emma' => array(
					'title' => __('Emma', OP_SN),
					'action' => array($this,'emma'),
					'save_action' => array($this,'save_emma')
				),
				'infusionsoft' => array(
					'title' => __('InfusionSoft', OP_SN),
					'action' => array($this,'infusionsoft'),
					'save_action' => array($this,'save_infusionsoft')
				),
				'getresponse' => array(
					'title' => __('GetResponse', OP_SN),
					'action' => array($this,'getresponse'),
					'save_action' => array($this,'save_getresponse')
				),
				'oneshoppingcart' => array(
					'title' => __('1ShoppingCart', OP_SN),
					'action' => array($this,'oneshoppingcart'),
					'save_action' => array($this,'save_oneshoppingcart')
				),
				'officeautopilot' => array(
					'title' => __('OfficeAutopilot', OP_SN),
					'action' => array($this,'officeautopilot'),
					'save_action' => array($this,'save_officeautopilot')
				),
				'ontraport' => array(
					'title' => __('Ontraport', OP_SN),
					'action' => array($this,'ontraport'),
					'save_action' => array($this,'save_ontraport')
				),
				'gotowebinar' => array(
					'title' => __('GoToWebinar', OP_SN),
					'action' => array($this,'gotowebinar'),
					'save_action' => array($this,'save_gotowebinar')
				),
				'campaignmonitor' => array(
					'title' => __('CampaignMonitor', OP_SN),
					'action' => array($this,'campaignmonitor'),
					'save_action' => array($this,'save_campaignmonitor')
				),
			);
			$sections = apply_filters('op_edit_sections_email_marketing_services',$sections);
		}
		return $sections;
	}

	/* GoToWebinar */
	function gotowebinar(){
		echo op_load_section('gotowebinar', array(), 'email_marketing_services');
	}

	function save_gotowebinar($op){
		if ($gotowebinar = op_get_var($op['email_marketing_services'], 'gotowebinar_api_key')) {
			op_update_option('gotowebinar_api_key', $gotowebinar);
		}
	}

	/* Aweber */
	function aweber(){
		echo op_load_section('aweber', array(), 'email_marketing_services');
	}

	function save_aweber($op){
		if ($aweber = op_get_var($op, 'aweber')){
			op_update_option('aweber', $aweber);
		}
	}

	/* iContact */
	function icontact(){
		echo op_load_section('icontact', array(), 'email_marketing_services');
	}

	function save_icontact($op){
		$icontactUsername = op_get_var($op['email_marketing_services'], 'icontact_username');
		$icontactPassword = op_get_var($op['email_marketing_services'], 'icontact_password');

		if ($icontactUsername) {
			op_update_option('icontact_username', $icontactUsername);
		} else {
			op_delete_option('icontact_username');
		}

		if ($icontactPassword) {
			op_update_option('icontact_password', $icontactPassword);
		} else {
			op_delete_option('icontact_password');
		}

	}

	/* MailChimp */
	function mailchimp(){
		echo op_load_section('mailchimp', array(), 'email_marketing_services');
	}

	function save_mailchimp($op){
		if ($mailchimp = op_get_var($op['email_marketing_services'], 'mailchimp_api_key')){
			op_update_option('mailchimp_api_key', $mailchimp);
		} else {
			op_delete_option('mailchimp_api_key');
		}
	}

	/* Emma */
	function emma(){
		echo op_load_section('emma', array(), 'email_marketing_services');
	}

	function save_emma($op){

		if ($account_id = op_get_var($op['email_marketing_services'], 'emma_account_id')){
			op_update_option('emma_account_id', $account_id);
		} else {
			op_delete_option('emma_account_id');
		}
		if ($public_key = op_get_var($op['email_marketing_services'], 'emma_public_key')){
			op_update_option('emma_public_key', $public_key);
		} else {
			op_delete_option('emma_public_key');
		}
		if ($private_key = op_get_var($op['email_marketing_services'], 'emma_private_key')){
			op_update_option('emma_private_key', $private_key);
		} else {
			op_delete_option('emma_private_key');
		}

	}

	/* CampaignMonitor */
	function campaignmonitor(){
		echo op_load_section('campaignmonitor', array(), 'email_marketing_services');
	}

	function save_campaignmonitor($op){
		if ($campaignmonitor = op_get_var($op['email_marketing_services'], 'campaignmonitor_api_key')){
			op_update_option('campaignmonitor_api_key', $campaignmonitor);
		} else {
			op_delete_option('campaignmonitor_api_key');
		}
	}

	/* InfusionSoft */
	function infusionsoft(){
		echo op_load_section('infusionsoft', array(), 'email_marketing_services');
	}

	function save_infusionsoft($op){
		$accountId = op_get_var($op['email_marketing_services'], 'infusionsoft_account_id');
		$apiKey = op_get_var($op['email_marketing_services'], 'infusionsoft_api_key');

		if ($accountId) {
			op_update_option('infusionsoft_account_id', $accountId);
		} else {
			op_delete_option('infusionsoft_account_id');
		}

		if ($apiKey) {
			op_update_option('infusionsoft_api_key', $apiKey);
		} else {
			op_delete_option('infusionsoft_api_key');
		}
	}

	/* GetResponse */
	function getresponse(){
		echo op_load_section('getresponse', array(), 'email_marketing_services');
	}

	function save_getresponse($op){
		$apiKey = op_get_var($op['email_marketing_services'], 'getresponse_api_key');
		$apiUrl = op_get_var($op['email_marketing_services'], 'getresponse_api_url');

		if ($apiKey) {
			op_update_option('getresponse_api_key', $apiKey);
		} else {
			op_delete_option('getresponse_api_key');
		}

		if ($apiUrl) {
			op_update_option('getresponse_api_url', $apiUrl);
		} else {
			op_delete_option('getresponse_api_url');
		}
	}

	/* 1ShoppingCart */
	function oneshoppingcart(){
		echo op_load_section('oneshoppingcart', array(), 'email_marketing_services');
	}

	function save_oneshoppingcart($op){}

	/* OfficeAutopilot */
	function officeautopilot(){
		echo op_load_section('officeautopilot', array(), 'email_marketing_services');
	}

	function save_officeautopilot($op){
		$appId = op_get_var($op['email_marketing_services'], 'officeautopilot_app_id');
		$apiKey = op_get_var($op['email_marketing_services'], 'officeautopilot_api_key');

		if ($appId) {
			op_update_option('officeautopilot_app_id', $appId);
		} else {
			op_delete_option('officeautopilot_app_id');
		}

		if ($apiKey) {
			op_update_option('officeautopilot_api_key', $apiKey);
		} else {
			op_delete_option('officeautopilot_api_key');
		}
	}

	/* Ontraport */
	function ontraport(){
		echo op_load_section('ontraport', array(), 'email_marketing_services');
	}

	function save_ontraport($op){
		$appId = op_get_var($op['email_marketing_services'], 'ontraport_app_id');
		$apiKey = op_get_var($op['email_marketing_services'], 'ontraport_api_key');

		if ($appId) {
			op_update_option('ontraport_app_id', $appId);
		} else {
			op_delete_option('ontraport_app_id');
		}

		if ($apiKey) {
			op_update_option('ontraport_api_key', $apiKey);
		} else {
			op_delete_option('ontraport_api_key');
		}
	}
}