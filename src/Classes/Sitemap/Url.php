<?php
	namespace App\Classes\Sitemap;

	class Url {
		private $alternates = [];

		public 	$loc = "",
				$lastmod = "",
				$changefreq = "yearly",
				$priority = "1.0"
		;

		public function render() {
			$result = '
				<url>
					<loc>' . ($this->loc ?? "") . '</loc>
					<lastmod>' . ($this->lastmod ?? new \DateTime())->format("c") . '</lastmod>
					<changefreq>' . ($this->changefreq ?? "monthly") . '</changefreq>
					<priority>' . ($this->priority ?? "0.7") . '</priority>
			';
			foreach($this->alternates ?? [] as $locale => $url) {
				$result .= '
					<xhtml:link xmlns:xhtml="http://www.w3.org/1999/xhtml" rel="alternate" hreflang="' . ($locale ?? "") . '" href="' . ($url ?? "") . '"/>';
			}
			$result .= '
				</url>
			';
			return $result;
		}

		public function addAlternate(string $locale, string $url) {
			$locale = str_replace("_", "-", $locale);
			$this->alternates[$locale] = $url;
		}
	}