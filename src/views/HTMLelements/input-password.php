<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control " . ($attributes["class"] ?? "");
?>
<input type="password"<?=generate_html_attributes($attributes)?>>