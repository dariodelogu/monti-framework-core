<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control p-0 " . ($attributes["class"] ?? "");
	$attributes["type"] = "color";
?>

<div class="overflow-hidden rounded"><input <?=generate_html_attributes($attributes ?? [])?>></div>