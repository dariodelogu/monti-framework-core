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
		 * Fuori da TESTING forza sempre https: dietro un reverse proxy che termina il TLS e passa a PHP
		 * in chiaro, sia REQUEST_SCHEME che X-Forwarded-Proto possono risultare "http" se il proxy non
		 * e' configurato per inoltrarlo, rendendo ogni URL auto-generato (canonical, sitemap, hreflang)
		 * erroneamente "http://" anche su un sito servito solo in HTTPS.
		 *
		 * @return string The origin URL.
		 */
		public static function getOrigin() {
			if(!env_var("TESTING", false)) {
				return "https://" . self::getHost();
			}
			$forwarded = \App\System\Env::get("HTTP_X_FORWARDED_PROTO");
			$scheme = $forwarded ? trim(explode(",", $forwarded)[0]) : \App\System\Env::get("REQUEST_SCHEME");
			return $scheme . "://" . self::getHost();
		}
	}