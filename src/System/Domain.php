<?php
	namespace App\System;

	class Domain {

		/**
		 * Get the current host.
		 *
		 * @return string|null The current host.
		 */
		public static function getHost() {
			return \App\System\Env::get("HTTP_HOST", null);
		}

		/**
		 * Get the origin URL.
		 *
		 * @return string The origin URL.
		 */
		public static function getOrigin() {
			return \App\System\Env::get("REQUEST_SCHEME") . "://" . self::getHost();
		}
	}