<?php
	namespace App\System;

	class Session {
		/**
		 * Starts the session
		 */
		public static function start() {
			if(session_status() === PHP_SESSION_NONE) {
				session_start();
			}
		}

		/**
		 * Stops the session
		 */
		public static function stop() {
			if(session_status() !== PHP_SESSION_NONE) {
				session_destroy();
				$_SESSION = null;
				unset($_SESSION);
			}
		}
		
		/**
		 * Get session status
		 *
		 * @return     string
		 */
		public static function status() {
			switch(session_status()) {
				case 0:
					return "disabled";
				break;
				case 1:
					return "none";
				break;
				case 2:
					return "active";
				break;
			}
		}

		/**
		 * Set a session value.
		 *
		 * @param      string  $name   The session value name
		 * @param      mixed   $value  The session value
		 */
		public static function set(string $name, $value = null) {
			$_SESSION[$name] = $value;
		}

		/**
		 * Get a session value.
		 *
		 * @param      string  $name     The session value name
		 * @param      mixed   $default  Default value if $name not found
		 *
		 * @return     mixed   Session value
		 */
		public static function get(string $name, $default = null) {
			return self::contains($name) ? $_SESSION[$name] : $default;
		}

		/**
		 * Deletes a value from session.
		 *
		 * @param      string  $name   The session value name
		 */
		public static function delete(string $name) {
			if(self::contains($name)) {
				$_SESSION[$name] = null;
				unset($_SESSION[$name]);
			}
		}

		/**
		 * Checks if session contains a value
		 *
		 * @param      string  $name   The session value name
		 *
		 * @return     mixed   Session value
		 */
		public static function contains(string $name) {
			return isset($_SESSION[$name]);
		}

		/**
		 * Get session messages
		 *
		 * @param      string  $key      The session message key
		 * @param      mixed   $default  Default value if $key not found
		 *
		 * @return     mixed
		 */
		public static function get_message(string $key, $default = null) {
			return dot_notation_array($key, \Session::get("messages", []), $default);
		}

		/**
		 * Get session inputs
		 *
		 * @param      string  $key      The session input key
		 * @param      mixed   $default  Default value if $key not found
		 *
		 * @return     mixed
		 */
		public static function get_input(string $key, $default = null) {
			return dot_notation_array($key, \Session::get("inputs", []), $default);
		}

		

		/**
		 * Adds navigation history to session.
		 *
		 * @param      \App\System\Router\Route  $route
		 *
		 * @return     bool  True on success, False otherwise
		 */
		public static function push_history(\App\System\Router\Route $route) {
			if(strtoupper(\App\System\Http\ServerRequest::get()->getMethod()) !== "GET") {
				return false;
			}
			$history = \Session::get_history();
			$count = count($history);
			if($count >= 10) {
				$history = array_slice($history, -9);
				$count = count($history);
			}
			$last = null;
			if($count == 1) {
				$last = $history[0];
			}
			if($count > 1) {
				$last = $history[$count - 1];
			}
			if(is_null($last) || ($last["url"] != $route->toUrl())) {
				$history[] = [
					"params" => $route->getParams(),
					"url" => $route->toUrl()
				];
				\Session::set("navigation_history", $history);
				return true;
			}
			return false;
		}

		/**
		 * Gets the session navigation history.
		 *
		 * @return     array
		 */
		public static function get_history(?int $index = null) {
			$history = \Session::get("navigation_history", []);
			if(is_null($index)) {
				return $history;
			}
			if($index < 0) {
				$index += count($history);
			}
			if(isset($history[$index])) {
				return $history[$index];
			}
			return null;
		}
	}