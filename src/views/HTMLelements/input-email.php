<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control " . ($attributes["class"] ?? "");
?>
<input type="email"<?=generate_html_attributes($attributes)?>>