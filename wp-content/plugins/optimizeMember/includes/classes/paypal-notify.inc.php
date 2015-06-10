<?php
/**
* optimizeMember's PayPal IPN handler.
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
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_plugin__optimizemember_paypal_notify"))
	{
		/**
		* optimizeMember's PayPal IPN handler.
		*
		* @package optimizeMember\PayPal
		* @since 3.5
		*/
		class c_ws_plugin__optimizemember_paypal_notify
			{
				/**
				* Handles PayPal IPN processing.
				*
				* @package optimizeMember\PayPal
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				*
				* @return null|inner Return-value of inner routine.
				*/
				public static function paypal_notify ()
					{
						if (!empty ($_GET["optimizemember_paypal_notify"])) /* Call inner routine? */
							{
								return c_ws_plugin__optimizemember_paypal_notify_in::paypal_notify ();
							}
					}
			}
	}
?>