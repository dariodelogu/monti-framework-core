<?php
	namespace App\System\Document;

	use App\System\Domain;

	/**
	 * Handles the generation and management of HTML document tags,
	 * such as meta tags, links, styles, scripts, manifest, robots, and sitemap.
	 * Implements the singleton pattern.
	 */
	class Document {

		/**
		 * Page title.
		 *
		 * @var string
		 */
		public string $title = "";

		/**
		 * Text to append to the title.
		 *
		 * @var string
		 */
		public string $title_append = "";

		/**
		 * Canonical URL of the page.
		 *
		 * @var string
		 */
		public string $canonical = "";

		/**
		 * Document meta tags.
		 *
		 * @var array
		 */
		private $meta_tags = [],
			$preload = [],
			$link_tags = [],
			$styles = [],
			$scripts = [],
			$inline_scripts = []
		;

		/**
		 * Singleton instance of the class.
		 *
		 * @var Document|null
		 */
		public static $instance = null;

		/**
		 * Returns all links added to the document.
		 *
		 * @return array
		 */
		public function getLinks() {
			return $this->link_tags;
		}

		/**
		 * Returns the singleton instance of the Document class.
		 *
		 * @return Document
		 */
		public static function get() {
			if(is_null(self::$instance)) {
				self::$instance = new static;
			}
			return self::$instance;
		}

		/**
		 * Constructor. Sets the CSRFToken meta tag.
		 */
		public function __construct() {
			$this->setMetaTag("CSRFToken", [
				"name" => "CSRFToken",
				"content" => \App\System\Session::get("CSRFToken")
			]);
		}

		/**
		 * Builds the <title> content: page title + title_append, with the project name appended as a brand suffix. If that would exceed $max_length (Google truncates titles at roughly this length in search results), the brand suffix is dropped so the page-specific, keyword-bearing part is never the part that gets cut.
		 *
		 * @param int $max_length Soft length budget for the full title, brand included.
		 * @return string
		 */
		public function renderTitleTagContent(int $max_length = 60): string {
			$page_part = $this->title . $this->title_append;
			$full = implode(" - ", array_filter([$page_part, \Project::get()->name]));
			return mb_strlen($full) > $max_length ? $page_part : $full;
		}

		/**
		 * Generates and outputs the XML sitemap.
		 * Terminates script execution.
		 *
		 * @return void
		 */
		public function sitemap() {
			$path = root_path("src/seo/sitemap.php");
			abort_if(!file_exists($path), 404);
			$content = include($path);
			header("Content-type: application/xml");
			echo $content;
			exit();
		}

		/**
		 * Generates and outputs the robots.txt file.
		 * Terminates script execution.
		 *
		 * @return void
		 */
		public function robots() {
			$text = 
			"	User-agent: *
				Disallow: /admin
				Disallow: /src
				Allow: /robots.txt

				Sitemap: " . Domain::getOrigin() . "/sitemap.xml
			";

			$path = root_path("src/seo/robots.php");
			if(file_exists($path)) {
				header("Content-type: text/plain");
				$text = trim(str_replace("\t", "", include($path)));
			}

			header("Content-type: text/plain");
			echo str_replace("\t", "", $text);
			exit();
		}

		/**
		 * Generates and outputs the llms.txt file.
		 * Terminates script execution.
		 *
		 * @return void
		 */
		public function llms() {
			$path = root_path("src/seo/llms.php");
			abort_if(!file_exists($path), 404);
			$content = include($path);
			header("Content-type: text/plain");
			echo str_replace("\t", "", $content);
			exit();
		}

		/**
		 * Generates and outputs the web manifest file.
		 * Terminates script execution.
		 *
		 * @return void
		 */
		public function webmanifest() {
			$project = \Project::get();
			$path = public_path("src/icons/site.webmanifest");
			$manifest = [];
			if(file_exists($path)) {
				$manifest = json_decode(file_get_contents($path), true);
			}
			$manifest["name"] = $project->name;
			$manifest["short_name"] = $project->name;
			$manifest["theme_color"] = config("app.colors.theme", "");
			$manifest["background_color"] = config("app.colors.theme", "");
			$manifest["display"] = "fullscreen";
			$manifest["start_url"] = Domain::getOrigin();
			if(!empty($project->description)) {
				$manifest["description"] = $project->description;
			}
			header("Content-type: application/manifest+json");
			echo json_encode($manifest);
			exit();
		}

		/**
		 * Adds a meta tag to the document.
		 *
		 * @param string $id         Meta tag identifier. Unique key for the meta tag.
		 * @param array  $attributes Meta tag attributes. Associative array of meta tag attributes.
		 *
		 * @return $this
		 */
		public function setMetaTag(string $id, array $attributes) {
			$meta = new Tag("meta");
			$meta->attributes = $attributes;
			$this->meta_tags[$id] = $meta;
			return $this;
		}

		/**
		 * Returns all meta tags, or a specific one if provided.
		 *
		 * @param string|null $tag Meta tag identifier. If provided, returns the specific meta tag.
		 *
		 * @return mixed
		 */
		public function getMetaTags($tag = null) {
			if(is_string($tag)) {
				return $this->meta_tags[$tag] ?? null;
			}
			return $this->meta_tags;
		}

		/**
		 * Adds a link tag to the document.
		 *
		 * @param array $attributes Associative array of link tag attributes.
		 * @param array $options    Additional options for the link tag (e.g. id, preload).
		 *
		 * @return $this
		 */
		public function appendLink(array $attributes, array $options = []) {
			$options["id"] = $options["id"] ?? sha1(microtime());
			$id = $options["id"];
			$link = new Tag("link");
			$link->attributes = $attributes;
			$this->link_tags[/* $id */] = [
				"id" => $id,
				"element" => $link,
				"options" => $options
			];
			if(!empty($options["preload"])) {
				$this->appendPreload("style", $link, $options);
			}
			return $this;
		}

		/**
		 * Adds a <link rel="alternate" hreflang="..."> for every active language of the project
		 * (self-reference included, as recommended by Google), for the current named route.
		 * Requires the current route to have a name (routes without ->name() are skipped).
		 *
		 * @return $this
		 */
		public function appendHreflangAlternates() {
			$route = router()->currentRoute();
			if(!$route || !$route->getName()) {
				return $this;
			}
			foreach(\Project::get()->getLanguages() as $lang) {
				$path = router()->urlForLanguage($lang, $route);
				if($path === null) {
					continue;
				}
				$this->appendLink([
					"rel" => "alternate",
					"hreflang" => $lang,
					"href" => \Domain::getOrigin() . $path
				]);
			}
			return $this;
		}

		/**
		 * Adds a CSS style to the document.
		 *
		 * @param string $content    CSS content to be added.
		 * @param array  $attributes Associative array of style tag attributes.
		 * @param array  $options    Additional options for the style tag (e.g. id, group).
		 *
		 * @return $this
		 */
		public function appendStyle(string $content, array $attributes = [], array $options = []) {
			$id = $options["id"] ?? sha1(microtime());
			$style = new Tag("style");
			$style->attributes = $attributes;
			$style->content = $content;
			$this->styles[$id] = [
				"element" => $style,
				"options" => $options
			];
			return $this;
		}

		/**
		 * Adds a JS script to the document.
		 *
		 * @param array $attributes Associative array of script tag attributes.
		 * @param array $options    Additional options for the script tag (e.g. id, preload).
		 *
		 * @return $this
		 */
		public function appendScript(array $attributes, array $options = []) {
			$id = $options["id"] ?? sha1(microtime());
			$script = new Tag("script");
			$script->attributes = $attributes;
			$this->scripts[$id] = [
				"element" => $script,
				"options" => $options
			];
			if(!empty($options["preload"])) {
				$this->appendPreload("script", $script, $options);
			}
			return $this;
		}

		/**
		 * Adds an inline JS script to the document.
		 *
		 * @param string $content    JavaScript code to be added inline.
		 * @param array  $attributes Associative array of script tag attributes.
		 * @param array  $options    Additional options for the inline script (e.g. id, group).
		 *
		 * @return $this
		 */
		public function appendInlineScript(string $content, array $attributes = [], array $options = []) {					
			$id = $options["id"] ?? sha1(microtime());
			$script = new Tag("script");
			$script->attributes = $attributes;
			$script->content = $content;
			$this->inline_scripts[$id] = [
				"element" => $script,
				"options" => $options
			];
			return $this;
		}

		/**
		 * Adds a preload tag for style or script.
		 *
		 * @param string $as      Type of resource to preload ("style" or "script").
		 * @param \App\System\Document\Tag    $element Tag element to preload.
		 * @param array  $options Additional options for the preload tag (e.g. id, crossorigin).
		 *
		 * @return void|false
		 */
		private function appendPreload(string $as, Tag $element, array $options = []) {
			$id = $options["id"] ?? sha1(microtime());
			if($element->get_tag_name() == "style") {
				$href = $element->attributes["href"] ?? "";
			}
			else if($element->get_tag_name() == "script") {
				$href = $element->attributes["src"] ?? "";
			}
			else {
				return false;
			}
			$element = new Tag("link");
			$element->attributes["href"] = $href;
			$element->attributes["rel"] = "preload";
			$element->attributes["as"] = $as;
			$element->attributes["crossorigin"] = "";
			if(!empty($options["preload-crossorigin"])) {
				$element->attributes["crossorigin"] = $options["preload-crossorigin"];
			}
			else {
				
			}
			$this->preload[$id] = $element;
		}

		/**
		 * Groups and concatenates styles or scripts by group.
		 *
		 * @param array $elements Array of style or script elements to group.
		 *
		 * @return string
		 */
		private function groupStylesScripts(array $elements) {
			$result = "";
			$groups = [];
			foreach($elements as $element) {
				if(isset($element["options"]["group"])) {
					$group = $element["options"]["group"];
					if(!isset($groups[$group])) {
						$groups[$group] = [$element];
					}
					else {
						$groups[$group][] = $element;
					}
				}
				else {
					$result .= $element["element"]->render();
				}
			}
			foreach($groups as $elements_coll) {
				$wrap_el = null;
				foreach($elements_coll as $i => $element) {
					if($i == 0) {
						$wrap_el = clone $element["element"];
					}
					else if(!is_null($wrap_el)) {
						$wrap_el->content .= $element["element"]->content;
					}
				}
				if(!is_null($wrap_el)) {
					$result .= $wrap_el->render();
				}
			}
			return $result;
		}

		/**
		 * Returns rendered meta tags.
		 *
		 * @return string
		 */
		public function renderMetaTags() {
			$meta_tags = "";
			foreach($this->meta_tags as $meta) {
				$meta_tags .= $meta->render();
			}
			return $meta_tags;
		}

		/**
		 * Prints rendered meta tags.
		 *
		 * @return void
		 */
		public function printMetaTags() {
			echo $this->renderMetaTags();
		}

		/**
		 * Returns rendered links and styles.
		 *
		 * @return string
		 */
		public function renderStyles() {
			$links = "";
			$unique_links = [];
			foreach($this->link_tags as $link) {
				if(isset($unique_links[$link["id"]])) {
					continue;
				}
				$unique_links[$link["id"]] = $link;
			}
			foreach($unique_links as $link) {
				$links .= $link["element"]->render();
			}
			$styles = $this->groupStylesScripts($this->sort_styles_scripts($this->styles));
			return $links . $styles;
		}

		/**
		 * Prints rendered links and styles.
		 *
		 * @return void
		 */
		public function printStyles() {
			echo $this->renderStyles();
		}

		/**
		 * Sorts styles and scripts by parsing progression.
		 *
		 * @param array $scripts Array of style or script elements to sort.
		 *
		 * @return array
		 */
		private function sort_styles_scripts(array $scripts) {
			//sort scripts from last to first (from parent view to last child view)
			$sort = $scripts;
			usort($sort, function($x, $y) {
				$x_ts = $x["options"]['parsing_progressive'] ?? 0;
				$y_ts = $y["options"]['parsing_progressive'] ?? 0;

				if($x_ts === $y_ts) {
					return 0;
				}

				return $x_ts < $y_ts ? 1 : -1;
			});
			return $sort;
		}

		/**
		 * Returns rendered scripts (external and inline).
		 *
		 * @return string
		 */
		public function renderScripts() {
			$scripts = $this->groupStylesScripts($this->sort_styles_scripts($this->scripts));
			$inline_scripts = "";
			foreach($this->sort_styles_scripts($this->inline_scripts) as $inline_script) {
				$inline_scripts .= $inline_script["element"]->render();
			}
			return $scripts . $inline_scripts;
		}

		/**
		 * Prints rendered scripts.
		 *
		 * @return void
		 */
		public function printScripts() {
			echo $this->renderScripts();
		}

		/**
		 * Prints favicon links if available.
		 *
		 * @return void|null
		 */
		public function printFaviconLinks() {
			if(!file_exists(public_path("src/icons/apple-touch-icon.png"))) {
				return null;
			}
			echo '
				<link rel="icon" type="image/png" href="/src/icons/favicon-96x96.png" sizes="96x96" />
				<link rel="icon" type="image/svg+xml" href="/src/icons/favicon.svg" />
				<link rel="shortcut icon" href="/src/icons/favicon.ico" />
				<link rel="apple-touch-icon" sizes="180x180" href="/src/icons/apple-touch-icon.png" />
				<link rel="manifest" href="/site.webmanifest" />
			';
		}
	}