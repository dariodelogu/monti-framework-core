<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control " . ($attributes["class"] ?? "");
	$attributes["type"] = "number";
?>

<input <?=generate_html_attributes($attributes ?? [])?>>