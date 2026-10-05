<?php
	namespace App\System;

	class File {

		/**
		 * Check if the given MIME type or extension is an image.
		 * 
		 * @param string $data MIME type or extension
		 * @return bool
		 */
		public static function isImage(string $data) {
			return isset(self::image_MIME()[$data]) || in_array($data, self::image_MIME());
		}

		/**
		 * Check if the given MIME type or extension is a video.
		 * 
		 * @param string $data MIME type or extension
		 * @return bool
		 */
		public static function isVideo(string $data) {
			return isset(self::video_MIME()[$data]) || in_array($data, self::video_MIME());
		}

		/**
		 * Get the file extension from a filename string.
		 * 
		 * @param string $string Filename
		 * @return string
		 */
		public static function getExtensionFromString(string $string) {
			return pathinfo($string)["extension"];
		}

		/**
		 * Get the file extension from a MIME type.
		 * 
		 * @param string $mime MIME type
		 * @return string|false
		 */
		public static function getExtensionFromMIME(string $mime) {
			return array_search($mime, self::all_MIME());
		}

		/**
		 * Get the MIME type from a file extension.
		 * 
		 * @param string $extension File extension
		 * @return string|false
		 */
		public static function getMIME(string $extension) {
		   return self::all_MIME()[$extension] ?? false;
		}

		/**
		 * Get all image MIME types.
		 * 
		 * @return array
		 */
		public static function image_MIME() {
			return [
				"bmp" => "image/bmp",
				"png" => "image/png",
				"cod" => "image/cis-cod",
				"gif" => "image/gif",
				"ief" => "image/ief",
				"jpe" => "image/jpeg",
				"jpeg" => "image/jpeg",
				"jpg" => "image/jpeg",
				"jfif" => "image/pipeg",
				"svg" => "image/svg+xml",
				"tif" => "image/tiff",
				"tiff" => "image/tiff",
				"ras" => "image/x-cmu-raster",
				"cmx" => "image/x-cmx",
				"ico" => "image/x-icon",
				"pnm" => "image/x-portable-anymap",
				"pbm" => "image/x-portable-bitmap",
				"pgm" => "image/x-portable-graymap",
				"ppm" => "image/x-portable-pixmap",
				"rgb" => "image/x-rgb",
				"xbm" => "image/x-xbitmap",
				"xpm" => "image/x-xpixmap",
				"xwd" => "image/x-xwindowdump",
			];
		}

		/**
		 * Get all application MIME types.
		 * 
		 * @return array
		 */
		public static function application_MIME() {
			return [
				"evy" => "application/envoy",
				"fif" => "application/fractals",
				"spl" => "application/futuresplash",
				"hta" => "application/hta",
				"acx" => "application/internet-property-stream",
				"hqx" => "application/mac-binhex40",
				"doc" => "application/msword",
				"dot" => "application/msword",
				"*" => "application/octet-stream",
				"bin" => "application/octet-stream",
				"class" => "application/octet-stream",
				"dms" => "application/octet-stream",
				"exe" => "application/octet-stream",
				"lha" => "application/octet-stream",
				"lzh" => "application/octet-stream",
				"oda" => "application/oda",
				"axs" => "application/olescript",
				"pdf" => "application/pdf",
				"prf" => "application/pics-rules",
				"p10" => "application/pkcs10",
				"crl" => "application/pkix-crl",
				"ai" => "application/postscript",
				"eps" => "application/postscript",
				"ps" => "application/postscript",
				"rtf" => "application/rtf",
				"setpay" => "application/set-payment-initiation",
				"setreg" => "application/set-registration-initiation",
				"xla" => "application/vnd.ms-excel",
				"xlc" => "application/vnd.ms-excel",
				"xlm" => "application/vnd.ms-excel",
				"xls" => "application/vnd.ms-excel",
				"xlt" => "application/vnd.ms-excel",
				"xlw" => "application/vnd.ms-excel",
				"msg" => "application/vnd.ms-outlook",
				"sst" => "application/vnd.ms-pkicertstore",
				"cat" => "application/vnd.ms-pkiseccat",
				"stl" => "application/vnd.ms-pkistl",
				"pot" => "application/vnd.ms-powerpoint",
				"pps" => "application/vnd.ms-powerpoint",
				"ppt" => "application/vnd.ms-powerpoint",
				"mpp" => "application/vnd.ms-project",
				"wcm" => "application/vnd.ms-works",
				"wdb" => "application/vnd.ms-works",
				"wks" => "application/vnd.ms-works",
				"wps" => "application/vnd.ms-works",
				"hlp" => "application/winhlp",
				"bcpio" => "application/x-bcpio",
				"cdf" => "application/x-cdf",
				"z" => "application/x-compress",
				"tgz" => "application/x-compressed",
				"cpio" => "application/x-cpio",
				"csh" => "application/x-csh",
				"dcr" => "application/x-director",
				"dir" => "application/x-director",
				"dxr" => "application/x-director",
				"dvi" => "application/x-dvi",
				"gtar" => "application/x-gtar",
				"gz" => "application/x-gzip",
				"hdf" => "application/x-hdf",
				"ins" => "application/x-internet-signup",
				"isp" => "application/x-internet-signup",
				"iii" => "application/x-iphone",
				"js" => "application/x-javascript",
				"latex" => "application/x-latex",
				"mdb" => "application/x-msaccess",
				"crd" => "application/x-mscardfile",
				"clp" => "application/x-msclip",
				"dll" => "application/x-msdownload",
				"m13" => "application/x-msmediaview",
				"m14" => "application/x-msmediaview",
				"mvb" => "application/x-msmediaview",
				"wmf" => "application/x-msmetafile",
				"mny" => "application/x-msmoney",
				"pub" => "application/x-mspublisher",
				"scd" => "application/x-msschedule",
				"trm" => "application/x-msterminal",
				"wri" => "application/x-mswrite",
				"cdf" => "application/x-netcdf",
				"nc" => "application/x-netcdf",
				"pma" => "application/x-perfmon",
				"pmc" => "application/x-perfmon",
				"pml" => "application/x-perfmon",
				"pmr" => "application/x-perfmon",
				"pmw" => "application/x-perfmon",
				"p12" => "application/x-pkcs12",
				"pfx" => "application/x-pkcs12",
				"p7b" => "application/x-pkcs7-certificates",
				"spc" => "application/x-pkcs7-certificates",
				"p7r" => "application/x-pkcs7-certreqresp",
				"p7c" => "application/x-pkcs7-mime",
				"p7m" => "application/x-pkcs7-mime",
				"p7s" => "application/x-pkcs7-signature",
				"sh" => "application/x-sh",
				"shar" => "application/x-shar",
				"swf" => "application/x-shockwave-flash",
				"sit" => "application/x-stuffit",
				"sv4cpio" => "application/x-sv4cpio",
				"sv4crc" => "application/x-sv4crc",
				"tar" => "application/x-tar",
				"tcl" => "application/x-tcl",
				"tex" => "application/x-tex",
				"texi" => "application/x-texinfo",
				"texinfo" => "application/x-texinfo",
				"roff" => "application/x-troff",
				"t" => "application/x-troff",
				"tr" => "application/x-troff",
				"man" => "application/x-troff-man",
				"me" => "application/x-troff-me",
				"ms" => "application/x-troff-ms",
				"ustar" => "application/x-ustar",
				"src" => "application/x-wais-source",
				"cer" => "application/x-x509-ca-cert",
				"crt" => "application/x-x509-ca-cert",
				"der" => "application/x-x509-ca-cert",
				"pko" => "application/ynd.ms-pkipko",
				"zip" => "application/zip",
			];
		}

		/**
		 * Get all audio MIME types.
		 * 
		 * @return array
		 */
		public static function audio_MIME() {
			return [
				"au" => "audio/basic",
				"snd" => "audio/basic",
				"mid" => "audio/mid",
				"rmi" => "audio/mid",
				"mp3" => "audio/mpeg",
				"aif" => "audio/x-aiff",
				"aifc" => "audio/x-aiff",
				"aiff" => "audio/x-aiff",
				"m3u" => "audio/x-mpegurl",
				"ra" => "audio/x-pn-realaudio",
				"ram" => "audio/x-pn-realaudio",
				"wav" => "audio/x-wav",
			];
		}

		/**
		 * Get all message MIME types.
		 * 
		 * @return array
		 */
		public static function message_MIME() {
			return [
				"mht" => "message/rfc822",
				"mhtml" => "message/rfc822",
				"nws" => "message/rfc822",
			];
		}

		/**
		 * Get all text MIME types.
		 * 
		 * @return array
		 */
		public static function text_MIME() {
			return [
				"css" => "text/css",
				"323" => "text/h323",
				"htm" => "text/html",
				"html" => "text/html",
				"stm" => "text/html",
				"uls" => "text/iuls",
				"bas" => "text/plain",
				"c" => "text/plain",
				"h" => "text/plain",
				"txt" => "text/plain",
				"sql" => "text/plain",
				"rtx" => "text/richtext",
				"sct" => "text/scriptlet",
				"tsv" => "text/tab-separated-values",
				"htt" => "text/webviewhtml",
				"htc" => "text/x-component",
				"etx" => "text/x-setext",
				"vcf" => "text/x-vcard",
				"csv" => "text/csv"
			];
		}

		/**
		 * Get all video MIME types.
		 * 
		 * @return array
		 */
		public static function video_MIME() {
			return [
				"mp2" => "video/mpeg",
				"mpa" => "video/mpeg",
				"mpe" => "video/mpeg",
				"mpeg" => "video/mpeg",
				"mpg" => "video/mpeg",
				"mpv2" => "video/mpeg",
				"mp4" => "video/mp4",
				"mov" => "video/quicktime",
				"qt" => "video/quicktime",
				"lsf" => "video/x-la-asf",
				"lsx" => "video/x-la-asf",
				"asf" => "video/x-ms-asf",
				"asr" => "video/x-ms-asf",
				"asx" => "video/x-ms-asf",
				"avi" => "video/x-msvideo",
				"movie" => "video/x-sgi-movie",
			];
		}

		/**
		 * Get all x-world MIME types.
		 * 
		 * @return array
		 */
		public static function x_world_MIME() {
			return [
				"flr" => "x-world/x-vrml",
				"vrml" => "x-world/x-vrml",
				"wrl" => "x-world/x-vrml",
				"wrz" => "x-world/x-vrml",
				"xaf" => "x-world/x-vrml",
				"xof" => "x-world/x-vrml",
			];
		}
		
		/**
		 * Get all supported MIME types.
		 * 
		 * @return array
		 */
		public static function all_MIME() {
			return array_merge(
				self::image_MIME(),
				self::application_MIME(),
				self::audio_MIME(),
				self::message_MIME(),
				self::text_MIME(),
				self::video_MIME(),
				self::x_world_MIME()
			);
		}
	}