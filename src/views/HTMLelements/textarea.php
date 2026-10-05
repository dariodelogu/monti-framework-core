<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control " . ($attributes["class"] ?? "");
?>

<textarea <?=generate_html_attributes($attributes ?? [])?>><?=$content ?? ""?></textarea>