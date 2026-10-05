<?php
	namespace App\System\MVC\View;

	class View {

		/**
		 * The path to the view file.
		 * @var string
		 */
		private string $view_path = "";

		/**
		 * Arguments passed to the view.
		 * @var array
		 */
		private array $args = [];

		/**
		 * Reference to the parent view, if any.
		 * @var View|null
		 */
		private ?View $parent = null;

		/**
		 * Sections defined within the view.
		 * @var array
		 */
		private array $sections = [];

		/**
		 * The name of the currently opened section.
		 * @var string
		 */
		private string $opened_section = "";

		/**
		 * Indicates if a style section is currently opened.
		 * @var bool
		 */
		private bool $opened_style = false;

		/**
		 * Indicates if a script section is currently opened.
		 * @var bool
		 */
		private bool $opened_script = false;

		/**
		 * Progressive counter for parsing operations.
		 * @var int
		 */
		public static int $parsing_progressive = 0;

		/**
		 * List of available source paths for views.
		 * @var array
		 */
		public static array $available_source_paths = [
			"namespace" => [],
			"no_namespace" => [],
		];

		public static array $available_modules_source_paths = [];

		/**
		 * Creates a new instance.
		 *
		 * @param      string  $view_path  The view path
		 * @param      array   $args       The view arguments
		 */
		public function __construct(string $view_path = "", array $args = []) {
			if(empty($view_path) || !self::exists($view_path)) {
				self::notFound($view_path);
			}
			$this->view_path = $view_path;
			$this->args = $args;
		}

		/**
		 * Adds a source path for modules views with a namespace.
		 *
		 * @param string $path       The path to add as a source for modules views.
		 * @param string $namespace  The namespace for the views.
		 *
		 * @throws \Exception If the namespace is already in use.
		 */
		public static function addModuleSourcePath(string $path, string $namespace) {
			if(isset(self::$available_modules_source_paths[$namespace])) {
				throw new \Exception("Modules views namespace \"$namespace\" already in use, choose another namespace.");
			}
			self::$available_modules_source_paths[$namespace] = $path;
		}

		/**
		 * Adds a source path for views, optionally with a namespace.
		 *
		 * @param string $path      The path to add as a source for views.
		 * @param string|null $namespace  The namespace for the views (optional).
		 *
		 * @throws \Exception If the namespace is already in use.
		 */
		public static function addSourcePath(string $path, ?string $namespace = null) {
			if($namespace === null) {
				self::$available_source_paths["no_namespace"][] = $path;
				return null;
			}
			if(isset(self::$available_source_paths["namespace"][$namespace])) {
				throw new \Exception("Views namespace \"$namespace\" already in use, choose another namespace.");
			}
			self::$available_source_paths["namespace"][$namespace] = $path;
		}

		/**
		 * Initialize all source paths from configuration.
		 *
		 * Loads view paths from the configuration and registers them for each section.
		 * 
		 * @see config("views.paths")
		 */
		public static function initSourcePaths() {
			foreach(config("views.paths.main") as $path) {
				self::$available_source_paths["no_namespace"][] = $path;
			}
		}

		/**
		 * Throw an exception when a view is not found
		 *
		 * @param      string      $view   The view
		 *
		 * @throws     \Exception  (description)
		 */
		public static function notFound(string $view) {
			throw new \Exception('View "' . $view . '" not found');
		}

		/**
		 * Check if a view exists
		 *
		 * @param      string  $path   The view path without file extension
		 *
		 * @return     bool  True if found, False otherwise
		 */
		public static function exists(string $path) {
			return file_exists($path . ".php");
		}
		
		/**
		 * Get a module view from filename
		 *
		 * @param      string  $filename  The view filename
		 * @param      array   $args      The view arguments
		 *
		 * @return     \App\System\MVC\View
		 */
		public static function module(string $filename, array $args = []) {
			$namespace = null;
			if(strpos($filename, "::") !== false) {
				$exp = explode("::", $filename, 2);
				$namespace = $exp[0];
				$filename = $exp[1];
			}
			if($namespace === null) {
				throw \Exception("Missing namespace for module view $filename");
			}
			$paths = array_map(fn($path) => $path . "/" . $namespace, [root_path("src/views")]);
			$paths[] = self::$available_modules_source_paths[$namespace];
			//dd($paths);
			foreach($paths as $path) {
				$path .= "/" . $filename;
				if(self::exists($path)) {
					$view_path = $path;
					break;
				}
			}
			if(!isset($view_path)) {
				self::notFound($filename);
			}

			return new static($view_path, $args);
		}
		
		/**
		 * Get a view from filename
		 *
		 * @param      string  $filename  The view filename
		 * @param      array   $args      The view arguments
		 *
		 * @return     \App\System\MVC\View
		 */
		public static function get(string $filename, array $args = []) {
			$namespace = null;
			if(strpos($filename, "::") !== false) {
				$exp = explode("::", $filename, 2);
				$namespace = $exp[0];
				$filename = $exp[1];
			}
			if($namespace !== null && isset(self::$available_source_paths["namespace"][$namespace])) {
				$path = self::$available_source_paths["namespace"][$namespace] . "/" . $filename;
				if(self::exists($path)) {
					return new static($path, $args);
				}
				self::notFound($namespace . "::" . $filename);
			}
			foreach(self::$available_source_paths["no_namespace"] ?? [] as $path) {
				$path .= "/" . $filename;
				if(self::exists($path)) {
					$view_path = $path;
					//break;
					//Break commented so will continue loop in other paths searching for the view
					//Paths should be in order of priority
					//Default folder first
				}
			}
			if(!isset($view_path)) {
				self::notFound($filename);
			}

			return new static($view_path, $args);
		}

		/**
		 * Get the view path
		 *
		 * @return     string  The view path
		 */
		public function getPath() {
			return $this->view_path;
		}

		/**
		 * Get the view arguments
		 *
		 * @return     array  The view arguments
		 */
		public function getArguments(): array {
			return $this->args;
		}

		/**
		 * Set the view parent
		 *
		 * @param      \App\System\MVC\View\View  $parent  Parent view
		 */
		public function parent(\App\System\MVC\View\View $parent) {
			$this->parent = $parent;
		}

		/**
		 * Set section position inside the view
		 *
		 * @param      string  $name     The section name
		 * @param      mixed   $default  Default content
		 */
		public function section(string $name, ?string $default = null) {
			echo $this->sections[$name] ?? $default ?? "";
		}

		/**
		 * Starts style section.
		 */
		public function start_style() {
			if($this->opened_style) {
				throw new \Exception("You can only start one style section at a time");
			}
			$this->opened_style = true;
			ob_start();
		}

		/**
		 * Stops style section.
		 *
		 * @param      array|null  $options  Section options
		 */
		public function stop_style(?array $options = []) {
			$content = ob_get_contents();
			ob_end_clean();
			$dom = new \DOMDocument();
			libxml_use_internal_errors(true);
			//Prevents free text from being inserted into a <p> tag
			$dom->loadHTML('<v-parsing>' . $content . '</v-parsing>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
			$options["parsing_progressive"] = self::$parsing_progressive++;
			foreach($dom->getElementsByTagName("style") as $index => $style) {
				$attributes = dom_element_attributes($style);
				$id = ($options["id"] ?? \App\Classes\Str::rand_letters_string()) . "-" . $index;
				$tag_options = $options;
				$tag_options["id"] = $id;
				foreach($attributes as $attr => $value) {
					if($attr == "opt-group") {
						$tag_options["group"] = $value;
						$style->removeAttribute($attr);
					}
					//unset($attributes[$attr]);
				}
				$content = $style->nodeValue;
				$options["minify"] = isset($options["minify"]) && is_bool($options["minify"]) ? $options["minify"] : false;
				if($options["minify"]) {
					$content = minify_css($content);
				}
				\Document::get()->appendStyle($content, $attributes, $tag_options);
			}
			foreach($dom->getElementsByTagName("link") as $index => $link) {
				$options["id"] = ($options["id"] ?? \App\Classes\Str::rand_letters_string()) . "-" . $index;
				\Document::get()->appendLink(dom_element_attributes($link), $options);
			}
			$this->opened_style = false;
		}

		/**
		 * Starts script section.
		 */
		public function start_script() {
			if($this->opened_script) {
				throw new \Exception("You can only start one script section at a time");
			}
			$this->opened_script = true;
			ob_start();
		}

		/**
		 * Stops script section.
		 *
		 * @param      array|null  $options  Section options
		 */
		public function stop_script(?array $options = []) {
			$content = ob_get_contents();
			ob_end_clean();
			$dom = new \DOMDocument();
			libxml_use_internal_errors(true);
			//Evita che il testo libero venga inserito in un tag p
			$dom->loadHTML('<v-parsing>' . $content . '</v-parsing>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
			$options["parsing_progressive"] = round(microtime(true) * 1000);
			foreach($dom->getElementsByTagName("script") as $index => $script) {
				$attributes = dom_element_attributes($script);
				$id = ($options["id"] ?? \App\Classes\Str::rand_letters_string()) . "-" . $index;
				$tag_options = $options;
				$tag_options["id"] = $id;
				foreach($attributes as $attr => $value) {
					if($attr == "opt-group") {
						$tag_options["group"] = $value;
						$script->removeAttribute($attr);
					}
					//unset($attributes[$attr]);
				}
				if(empty(trim($script->nodeValue))) {
					\Document::get()->appendScript(dom_element_attributes($script), $tag_options);
				}
				else {
					/*preg_match_all("/<script[\\d\\w=\\\"'\\/. ]?>(.*)<\\/script>/is", $content, $match);*/
					preg_match_all("/<script.*?>(.*)<\/script>/is", $content, $match);
					$node_value = "";
					try {
						$node_value = $match[1][0];
					}
					catch(\Throwable $t) {}
					\Document::get()->appendInlineScript($node_value, dom_element_attributes($script), $tag_options);
				}
			}
			$this->opened_script = false;
		}

		/**
		 * Starts a section.
		 *
		 * @param      string  $name   The section name
		 */
		public function start_section(string $name, ?string $content = null) {
			if(!empty($this->opened_section)) {
				throw new \Exception("You can only start one content section at a time");
			}
			ob_start();
			$this->opened_section = $name;
			$this->setSectionContent($this->opened_section, $content ?? "");
		}

		/**
		 * Stops a section.
		 */
		public function stop_section() {
			$content = ob_get_contents();
			ob_end_clean();
			$this->setSectionContent($this->opened_section, $content);
			$this->opened_section = "";
		}

		/**
		 * Merge view sections content with given sections content
		 *
		 * @param      array  $sections  Sections to merge
		 */
		public function mergeSections(array $sections) {
			foreach($sections as $name => $content) {
				$this->setSectionContent($name, $content);
			}
		}

		/**
		 * Sets the section content
		 * 
		 * If the section does not exist it will be first filled with an empty string.
		 * If it exists, and a child view has a section with the same name, it is concatenated to have the order parent + child.
		 *
		 * @param      string  $name     Section name
		 * @param      string  $content  Section content
		 */
		public function setSectionContent(string $name, string $content) {
			if(!isset($this->sections[$name])) {
				$this->sections[$name] = "";
			}
			//concat from most internal to most external
			//with child will result in parent + child output
			//without child will result in the given content + "" (empty string)
			$this->sections[$name] = $content . $this->sections[$name];
		}

		/**
		 * Compile the view content
		 *
		 * @return     string  The compiled content
		 */
		public function compile() {
			extract($this->args, EXTR_OVERWRITE);
			ob_start();
			include $this->view_path . ".php";
			$content = ob_get_contents();
			@ob_end_clean();
			return $content;
		}

		/**
		 * Resolve and build the view.
		 *
		 * @return     string  The resultg view
		 */
		public function build() {
			$content = $this->compile();
			if($this->parent) {
				//merge child sections content with parent section content for multi level hierarchy
				$this->parent->mergeSections($this->sections);
				$content = $this->parent->build();
			}
			return $content;
		}

		/**
		 * Build and print the view.
		 */
		public function render() {
			echo $this->build();
		}

		/**
		 * Returns a string representation of the object.
		 * When you print the \App\System\MVC\View object
		 * Eg. using echo
		 *
		 * @return     string  String representation of the object.
		 */
		public function __toString() {
			$string = $this->build();
			return is_string($string) ? $string : "";
		}
	}