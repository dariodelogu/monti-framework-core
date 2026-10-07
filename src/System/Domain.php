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
		 * Prefers X-Forwarded-Proto over REQUEST_SCHEME: behind a reverse proxy that terminates TLS
		 * and forwards to the app over plain HTTP, REQUEST_SCHEME would otherwise always read "http",
		 * making every self-referencing URL (canonical, sitemap, hreflang) wrongly "http://" even on
		 * an HTTPS-only site.
		 *
		 * @return string The origin URL.
		 */
		public static function getOrigin() {
			$forwarded = \App\System\Env::get("HTTP_X_FORWARDED_PROTO");
			$scheme = $forwarded ? trim(explode(",", $forwarded)[0]) : \App\System\Env::get("REQUEST_SCHEME");
			return $scheme . "://" . self::getHost();
		}
	}