<?php
	if (!defined('ABSPATH')) exit; // Exit if accessed directly

	/**
	 * CardConnect PHP REST Client Library
	 * Version: 1.0
	 * Copyright 2014, CardConnect (http://www.cardconnect.com)
	 *
	 * Permission to use, copy, modify, and/or distribute this software for any
	 * purpose with or without fee is hereby granted, provided that the above
	 * copyright notice and this permission notice appear in all copies.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
	 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
	 * FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
	 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
	 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
	 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
	 * PERFORMANCE OF THIS SOFTWARE.
	 */

	/** Modifications to this file by BlueChannel, Inc, 2021
	 *
	 *
	 *
	 *
	 */

	require 'pest/PestJSON.php';

	class CardConnectRestClient {
		private $url = "";
		private $user = "";
		private $password = "";

		private $OP_POST = "POST";
		private $OP_PUT = "PUT";
		private $OP_GET = "GET";
		private $OP_DELETE = "DELETE";

		private $ENDPOINT_AUTH = "auth";
		private $ENDPOINT_CAPTURE = "capture";
		private $ENDPOINT_VOID = "void";
		private $ENDPOINT_REFUND = "refund";
		private $ENDPOINT_INQUIRE = "inquire";
		private $ENDPOINT_SETTLESTAT = "settlestat";
		private $ENDPOINT_DEPOSIT = "deposit";
		private $ENDPOINT_PROFILE = "profile";
		private $ENDPOINT_INQUIRE_MERCHANT = "inquireMerchant";

		private $USER_AGENT = "CardConnectRestClient-PHP";
		private $CLIENT_VERSION = "1.0";


		/**
		 * Constructor to create a new CardConnectRestClient object
		 *
		 * @param string $ccurl CardConnect REST URL (https://sitename.prinpay.com:6443/cardconnect/rest/)
		 * @param string $user Username
		 * @param string $pass Password
		 */
		public function __construct($ccurl, $user, $pass) {
			if (self::isEmpty($ccurl)) throw new InvalidArgumentException("url parameter is required");
			if (self::isEmpty($user)) throw new InvalidArgumentException("username parameter is required");
			if (self::isEmpty($pass)) throw new InvalidArgumentException("password parameter is required");

			if (!self::endsWith($ccurl, "/")) $ccurl .= "/";

			$this->url = $ccurl;
			$this->username = $user;
			$this->password = $pass;
		}

		protected function rateLimit($request) {

			$delay = !empty($test = get_option('woocommerce_card_connect_settings')['cc_rate_limit']) ? (int)$test : 3; // in seconds from admin
			$string = $request["account"];
			$action = "place_order_" . $string;
			if (WC_Rate_Limiter::retried_too_soon($action)) {
				return true;
			} else {
				WC_Rate_Limiter::set_rate_limit($action, $delay);
			}
			return false;
		}

		protected function hammeredCard($request) {

			$is_banned = false;
			$banned_card_list = get_option('card_connect_banned_cards');

			if (in_array($request['account'], $banned_card_list)) {
				$is_banned = true;
			}
			$limit = !empty($test = get_option('woocommerce_card_connect_settings')['cc_attempts']) ? (int)$test : 3;
			// two options/transients to accomplish this:
			// one: set the card attempts in an transient that expires after a few days
			if (false === ($card_tries = get_transient($string = 'cc_' . $request["account"]))) {
				set_transient('cc_' . $request['account'], ['attempts' => 1], 2 * HOUR_IN_SECONDS);
				return false;
			}
			$card_tries['attempts']++;
			set_transient('cc_' . $request['account'], $card_tries);

			if ($is_banned) {
				if (array_key_exists('0', $banned_card_list)) {
					// cleanup from testing
					unset($banned_card_list[0]);
				}
				$banned_card_list[$request['account']] = $request['account'];
				update_option('card_connect_banned_cards', $banned_card_list, false);
				return 'banned';
			}
			if ($card_tries['attempts'] >= $limit) {
				$banned_card_list[$request['account']] = $request['account'];
				update_option('card_connect_banned_cards', $banned_card_list, false);
				return true;
			}

			// two: if limit met, set a banned list, that will be configurable by admin


			return false;
		}

		protected function hammeredOrder($request) {

			$order_id = (int)$request["orderid"];
			$max_tries = !empty($test = get_option('woocommerce_card_connect_settings')['order_attempts']) ? (int)$test : 3; // in seconds from admin
			if (false === ($card_tries = get_transient($string = 'cc_order_attempt_' . $order_id))) {
				// this code runs when there is no valid transient set
				set_transient('cc_order_attempt_' . $order_id, ['attempts' => 1], 7 * DAY_IN_SECONDS);
				return false;
			}
			if ($card_tries['attempts'] >= $max_tries) {
				return true;
			}
			$card_tries['attempts']++;
			set_transient('cc_order_attempt_' . $order_id, $card_tries);
			return false;
		}

		/**
		 * Sends an Authorize Transaction request via REST
		 *
		 * @param array $request Array representing an authorization request
		 * @return array Array representing an authorization response
		 */
		public function authorizeTransaction($request) {

			// double check that this is front end, non sub or pre request
			if (!isset($request["woo_type"]) && $request["woo_type"] !== 'sr') {
				// do security
				if ($this->rateLimit($request)) {
					return 'hammered';
				}
				if ($this->hammeredCard($request)) {
					return 'banned';
				}
				if ($this->hammeredOrder($request)) {
					return 'order_ban';
				}
			}
			return self::send($this->ENDPOINT_AUTH, $this->OP_PUT, $request);
		}


		/**
		 * Sends a Capture Transaction request via REST
		 *
		 * @param array $request Array representing a capture request
		 * @return array Array representing a capture response
		 */
		public function captureTransaction($request) {
			// do security
			if ($this->rateLimit($request)) {
				return 'hammered';
			}
			if ($this->hammeredCard($request)) {
				return 'banned';
			}
			if ($this->hammeredOrder($request)) {
				return 'order_ban';
			}
			return self::send($this->ENDPOINT_CAPTURE, $this->OP_PUT, $request);
		}


		/**
		 * Sends a Void Transaction request via REST
		 *
		 * @param array $request Array representing a void request
		 * @return array Array representing a void response
		 */
		public function voidTransaction($request) {
			return self::send($this->ENDPOINT_VOID, $this->OP_PUT, $request);
		}


		/**
		 * Sends a Refund Transaction request via REST
		 *
		 * @param array $request Array representing a refund request
		 * @return array Array representing a refund response
		 */
		public function refundTransaction($request) {
			return self::send($this->ENDPOINT_REFUND, $this->OP_PUT, $request);
		}


		/**
		 * Sends an Inquire Transaction request via REST
		 *
		 * @param string $merchid Merchant ID
		 * @param string $retref RetRef from previous authorization/capture response
		 * @return array Array representing an inquire response
		 */
		public function inquireTransaction($merchid, $retref) {
			if (self::isEmpty($merchid)) throw new InvalidArgumentException("Missing required parameter: merchid");
			if (self::isEmpty($retref)) throw new InvalidArgumentException("Missing required parameter: retref");

			$url = $this->ENDPOINT_INQUIRE . "/" . $retref . "/" . $merchid;
			return self::send($url, $this->OP_GET, null);
		}


		/**
		 * Sends a Settlement Status request via REST
		 *
		 * @param string $merchid Merchant ID
		 * @param string $date Settlement Date
		 * @return array Array representing the requested settlement status
		 */
		public function settlementStatus($merchid = "", $date = "") {
			if ((!self::isEmpty($merchid) && self::isEmpty($date)) || (self::isEmpty($merchid) && !self::isEmpty($date)))
				throw new InvalidArgumentException("Both merchid and date parameters are required, or neither");

			$url = '';
			if (self::isEmpty($merchid) || self::isEmpty($date)) {
				$url = $this->ENDPOINT_SETTLESTAT;
			} else {
				$url = $this->ENDPOINT_SETTLESTAT . "?date=" . $date . "&merchid=" . $merchid;
			}

			return self::send($url, $this->OP_GET, null);
		}


		/**
		 * Sends a Deposit Status request via REST
		 *
		 * @param string $merchid Merchant ID
		 * @param string $date Deposit Date
		 * @return array Array representing the requested deposit status
		 */
		public function depositStatus($merchid = "", $date = "") {
			if ((!self::isEmpty($merchid) && self::isEmpty($date)) || (self::isEmpty($merchid) && !self::isEmpty($date)))
				throw new InvalidArgumentException("Both merchid and date parameters are required, or neither");

			$url = '';
			if (self::isEmpty($merchid) || self::isEmpty($date)) {
				$url = $this->ENDPOINT_DEPOSIT;
			} else {
				$url = $this->ENDPOINT_DEPOSIT . "?merchid=" . $merchid . "&date=" . $date;
			}
			return self::send($url, $this->OP_GET, null);
		}


		/**
		 * Retrieves the specified profile via REST
		 *
		 * @param string $profileid Profile ID
		 * @param string $accountid Optional Account ID
		 * @param string $merchid Merchant ID
		 * @return array Array representing the retrieved profile
		 */
		public function profileGet($profileid, $accountid = "", $merchid) {
			if (self::isEmpty($profileid)) throw new InvalidArgumentException("Missing required parameter: profileid");
			if (self::isEmpty($merchid)) throw new InvalidArgumentException("Missing required parameter: merchid");

			$url = $this->ENDPOINT_PROFILE . "/" . $profileid . "/" . $accountid . "/" . $merchid;
			return self::send($url, $this->OP_GET, null);
		}


		/**
		 * Deletes the specified profile via REST
		 *
		 * @param string $profileid Profile ID
		 * @param string $accountid Optional Account ID
		 * @param string $merchid Merchant ID
		 * @return array Array representing the results of the profile deletion
		 */
		public function profileDelete($profileid, $accountid = "", $merchid) {
			if (self::isEmpty($profileid)) throw new InvalidArgumentException("Missing required parameter: profileid");
			if (self::isEmpty($merchid)) throw new InvalidArgumentException("Missing required parameter: merchid");

			$url = $this->ENDPOINT_PROFILE . "/" . $profileid . "/" . $accountid . "/" . $merchid;
			return self::send($url, $this->OP_DELETE, null);
		}

		public function inquireMerchant($merchid) {
			if (self::isEmpty($merchid)) throw new InvalidArgumentException("Missing required parameter: merchid");

			$url = $this->ENDPOINT_INQUIRE_MERCHANT . "/" . $merchid;
			return self::send($url, $this->OP_GET, null);
		}


		/**
		 * Creates or updates a profile via REST
		 *
		 * @param array $request Array representing the Profile create/update request
		 * @return array Array representing the profile creation
		 */
		public function profileCreate($request) {
			return self::send($this->ENDPOINT_PROFILE, $this->OP_PUT, $request);
		}


		// Returns true if a string is null or empty string
		static function isEmpty($s) {
			if (is_null($s)) return true;
			if (strlen($s) <= 0) return true;
			return false;
		}

		// Checks the last character of a string
		static function endsWith($s, $char) {
			return $char === "" || substr($s, -strlen($char)) === $char;
		}

		// Private method for sending HTTP REST request to CardConnect
		private function send($endpoint, $operation, $request) {

			$pest = new PestJSON($this->url);
			$pest->setupAuth($this->username, $this->password);
			$pest->curl_opts[CURLOPT_FOLLOWLOCATION] = false; // Not supported on hosts running safe_mode!
			$pest->curl_opts[CURLOPT_HTTPHEADER] = "Content-Type: application/json";
			$pest->curl_opts[CURLOPT_USERAGENT] = $this->USER_AGENT . " (v" . $this->CLIENT_VERSION . ")";

			$response = "";
			try {
				// Send request to rest service
				switch ($operation) {
					case ($this->OP_PUT):
						$response = $pest->put("/$endpoint", $request);
						break;
					case ($this->OP_GET):
						$response = $pest->get("/$endpoint", $request);
						break;
					case ($this->OP_POST):
						$response = $pest->post("/$endpoint", $request);
						break;
					case ($this->OP_DELETE):
						$response = $pest->delete("/$endpoint", $request);
						break;
				}
			} catch (Exception $e) {
				// SOF customization
				// this 'echo' causes problems since it invalidates the JSON response.  Use it for debugging only!
				//
				// echo "Caught exception when sending request : " .  $e->getMessage();
			}

			return $response;
		}
	}

?>
