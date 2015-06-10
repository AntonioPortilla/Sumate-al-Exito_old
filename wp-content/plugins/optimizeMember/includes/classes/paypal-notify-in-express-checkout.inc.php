<?php
/**
* optimizeMember's PayPal IPN handler ( inner processing routine ).
*
* Copyright: © 2009-2011
* {@link http://www.optimizepress.com/ optimizePress, Inc.}
* ( coded in the USA )
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package optimizeMember\PayPal
* @since 110720
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_plugin__optimizemember_paypal_notify_in_express_checkout"))
	{
		/**
		* optimizeMember's PayPal IPN handler ( inner processing routine ).
		*
		* @package optimizeMember\PayPal
		* @since 110720
		*/
		class c_ws_plugin__optimizemember_paypal_notify_in_express_checkout
			{
				/**
				* optimizeMember's PayPal IPN handler ( inner processing routine ).
				*
				* @package optimizeMember\PayPal
				* @since 110720
				*
				* @param array $vars Required. An array of defined variables passed by {@link optimizeMember\PayPal\c_ws_plugin__optimizemember_paypal_notify_in::paypal_notify()}.
				* @return array|bool The original ``$paypal`` array passed in ( extracted ) from ``$vars``, or false when conditions do NOT apply.
				*/
				public static function cp ($vars = array ()) /* Conditional phase for ``c_ws_plugin__optimizemember_paypal_notify_in::paypal_notify()``. */
					{
						extract($vars); /* Extract all vars passed in from: ``c_ws_plugin__optimizemember_paypal_notify_in::paypal_notify()``. */
						/**/
						if (/**/(!empty ($paypal["txn_type"]) && preg_match ("/^express_checkout$/i", $paypal["txn_type"]))/**/
						&& (empty ($paypal["payment_status"]) || empty ($payment_status_issues) || !preg_match ($payment_status_issues, $paypal["payment_status"]))/**/
						&& (!empty ($paypal["txn_id"]))/**/)
							{
								eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_plugin__optimizemember_during_paypal_notify_before_express_checkout", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
								/**/
								if (!get_transient ($transient_ipn = "s2m_ipn_" . md5 ("optimizemember_transient_" . $_paypal_s)) && set_transient ($transient_ipn, time (), 31556926 * 10))
									{
										$paypal["optimizemember_log"][] = "optimizeMember `txn_type` identified as ( `express_checkout` ).";
										/**/
										$processing = $during = true; /* Yes, we ARE processing this. */
										/**/
										$paypal["optimizemember_log"][] = "The `txn_type` does not require any action on the part of optimizeMember.";
										$paypal["optimizemember_log"][] = "optimizeMember Pro handles Express Checkout events on-site, with an IPN proxy.";
										/**/
										eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
										do_action ("ws_plugin__optimizemember_during_paypal_notify_during_express_checkout", get_defined_vars ());
										unset ($__refs, $__v); /* Unset defined __refs, __v. */
									}
								else /* Else, this is a duplicate IPN. Must stop here. */
									{
										$paypal["optimizemember_log"][] = "Not processing. Duplicate IPN.";
										$paypal["optimizemember_log"][] = "optimizeMember `txn_type` identified as ( `express_checkout` ).";
										$paypal["optimizemember_log"][] = "Duplicate IPN. Already processed. This IPN will be ignored.";
									}
								/**/
								eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_plugin__optimizemember_during_paypal_notify_after_express_checkout", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
								/**/
								return apply_filters ("c_ws_plugin__optimizemember_paypal_notify_in_express_checkout", $paypal, get_defined_vars ());
							}
						else
							return apply_filters ("c_ws_plugin__optimizemember_paypal_notify_in_express_checkout", false, get_defined_vars ());
					}
			}
	}
?>