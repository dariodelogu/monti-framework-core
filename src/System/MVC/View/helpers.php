<?php
	/**
	 * Helper for \App\System\MVC\View\View::get
	 *
	 * @param      string  $filename  The view filename
	 * @param      array   $args      The view arguments
	 *
	 * @return     \App\System\MVC\View\View
	 */
	function view(string $filename, array $args = []) {
		return \App\System\MVC\View\View::get($filename, $args);
	}

	/**
	 * Helper for \App\System\MVC\View\View::module
	 *
	 * @param      string  $filename  The view filename
	 * @param      array   $args      The view arguments
	 *
	 * @return     \App\System\MVC\View\View
	 */
	function module_view(string $filename, array $args = []) {
		return \App\System\MVC\View\View::module($filename, $args);
	}

	/**
	 * Gets the attributes of a DOMElement
	 *
	 * @param      \DOMElement  $element
	 *
	 * @return     array
	 */
	function dom_element_attributes(\DOMElement $element) {
		$attributes = [];
		foreach($element->attributes as $attr) {
			$name = $attr->name;
			$value = empty($attr->value) ? true : $attr->value;
			$attributes[$name] = $value;
		}
		return $attributes;
	}