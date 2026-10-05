<?php
	namespace App\System\Document;

	class Tag {
		// The name of the HTML tag (e.g., "div", "span")
		private string $tag_name = "";

		// Associative array of HTML attributes (e.g., ["class" => "my-class"])
		public array $attributes = [];

		// Content inside the tag (e.g., "Hello World")
		public string $content = "";

		/**
		 * Constructor
		 * 
		 * @param string $tag_name The name of the HTML tag
		 */
		public function __construct(string $tag_name) {
			$this->tag_name = mb_strtolower($tag_name);
		}

		/**
		 * Get the tag name
		 * 
		 * @return string The name of the tag
		 */
		public function get_tag_name() {
			return $this->tag_name;
		}

		/**
		 * Check if the tag is a void (self-closing) HTML tag
		 * 
		 * @return bool True if void tag, false otherwise
		 */
		private function is_void_tag() {
			$void = [
				"area", "base", "br", "col", "embed", "hr", "img",
				"input", "link", "meta", "source", "track", "wbr"
			];
			return in_array($this->tag_name, $void);
		}

		/**
		 * Render the HTML tag as a string
		 * 
		 * @return string The HTML representation of the tag
		 */
		public function render() {
			$closing = "";
			if(!$this->is_void_tag()) {
				$closing = $this->content . "</" . $this->tag_name . ">";
			}
			return "<" . $this->tag_name . " " . generate_html_attributes($this->attributes) . ($this->is_void_tag() ? " />" : ">") . $closing;
		}
	}