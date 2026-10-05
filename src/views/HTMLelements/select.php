<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control " . ($attributes["class"] ?? "");
	if(in_array("multiple", array_values($attributes))) {
		$attributes["class"] .= " select-multiple-no-ctrl";
	}
	$value = $value ?? [];
	$value = is_array($value) ? $value : [$value];
	$id = $id ?? \App\Classes\Str::rand_letters_string();
	$attributes["id"] = $attributes["id"] ?? $id;
?>

<select <?=generate_html_attributes($attributes)?>>
	<?php if($empty ?? true) { ?>
		<option value="<?=$empty_value ?? ""?>"><?=$empty_text ?? ""?></option>
	<?php } 
	foreach($options ?? [] as $opt) {
		if(isset($opt["label"])) { ?>
			<optgroup label="<?=$opt["label"]?>">
				<?php foreach($opt["options"] ?? [] as $o) { ?>
					<option value="<?=$o['value']?>" <?=in_array($o['value'], $value) ? 'selected' : ''?> <?=generate_html_attributes($o["attributes"] ?? [])?>><?=$o['text']?></option>
				<?php } ?>
			</optgroup>
		<?php } else { ?>
			<option value="<?=$opt['value']?>" <?=in_array($opt['value'], $value) ? 'selected' : ''?> <?=generate_html_attributes($opt["attributes"] ?? [])?>><?=$opt['text']?></option>
		<?php }
	} ?>
</select>