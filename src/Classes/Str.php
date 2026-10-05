<?php
	namespace App\Classes;

	class Str {
		/**
		 * Removes accents from string
		 *
		 * @param      string  $string
		 *
		 * @return     string
		 */
		public static function remove_accents(string $string) : string {
			$replaces = [
				'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
			];
			$result = str_replace(array_keys($replaces), $replaces, $string);
			return $result;
		}

		/**
		 * Removes anything that is not a letter, number, or space
		 *
		 * @param      string  $string
		 * @param      array   $except  Characters to keep
		 *
		 * @return     string
		 */
		public static function remove_symbols(string $string, array $except = []) : string {
			$result = mb_strtolower($string);
			$result = self::remove_accents($string);
			return trim(preg_replace("/[^a-zA-Z0-9 " . implode("", $except) . "]/", "", $result));
		}

		/**
		 * Removes accents, makes string lowercase, replaces multiple spaces with $space
		 *
		 * @param      string  $string
		 * @param      string  $space          Character to replace spaces
		 * @param      array   $include_chars  Array => value pairs for additional
		 *                                     substitutions
		 *
		 * @return     string  Sanitized string
		 */
		public static function sanitize_string(string $string, string $space = "", array $include_chars = []) : string {
			$result = mb_strtolower($string);
			$result = self::remove_accents($string);
			//$result = trim(preg_replace("/[^a-z0-9 ]/", "", $result));
			$result = preg_replace("/( )+/", $space, $result);
			if(!empty($include_chars)) {
				foreach($include_chars as $char => $replace) {
					if(is_string($char)) {
						$result = str_replace($char, $replace, $result);
					}
					else {
						$result = str_replace($replace, "", $result);
					}
				}
			}
			return trim($result);
		}

		/**
		 * Generates a random string of $lengh length
		 * 
		 * Max length 40 chars.
		 * To be used for purposes where safety is not necessary.
		 *
		 * @param      int     $length
		 *
		 * @return     string
		 */
		public static function rand_str($length = 40): string {
			$str = substr(sha1(microtime() . rand(0, 9999)), 0, $length);
			return $str;
		}

		/**
		 * Generates an only letters string of $lenght length
		 * 
		 * To be used for purposes where safety is not necessary.
		 * Eg. For the id of an HTML element.
		 *
		 * @param      int     $length  The length
		 *
		 * @return     string
		 */
		public static function rand_letters_string($length = 10): string {
			return preg_replace("/[0-9]+/", "", self::rand_str($length));
		}

		/**
		 * Remove all spaces from given string
		 *
		 * @param string $subject The subject string
		 * @param string $replace Optional replace for spacing
		 * @return void
		 */
		public static function remove_spaces(string $subject, string $replace = "") {
			return preg_replace("/\s+/i", $replace, $subject);
		}
	}