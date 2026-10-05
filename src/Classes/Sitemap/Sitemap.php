<?php
	namespace App\Classes\Sitemap;

	class Sitemap {

		private $urls = [];

		public function addUrl(Url $url) {
			$this->urls[] = $url;
		}

		public function render() {
			$result  = '
				<?xml version="1.0" encoding="UTF-8"?>
				<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
			';

			foreach($this->urls as $url) {
				$result .= $url->render();
			}

			$result .= '
				</urlset>
			';
			$result = preg_replace("/[\t\r\n]+/", "", $result);
			return $result;
		}

		public function display() {
			header("Content-type: application/xml");
			echo $this->render();
			exit();
		}
	}