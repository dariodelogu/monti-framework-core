<?php
	$attributes = $attributes ?? [];
	$attributes["id"] = $id ?? $attributes["id"] ?? sha1(microtime());
	$attributes["class"] = $attributes["class"] ?? "";
	$attributes["type"] = $attributes["type"] ?? $type ?? "checkbox";
	$attributes["value"] = $attributes["value"] ?? $value ?? "";
	$attributes["name"] = $attributes["name"] ?? $name ?? "";

	$inline = isset($inline) && is_bool($inline) ? $inline : false;
	$native = isset($native) && is_bool($native) ? $native : false;

	if(!$native) {
		$attributes["class"] .= " form-check-input";
	}
?>
<?php $this->start_style(); ?>
	<style>
		input[type=checkbox] + label,
		input[type=radio] + label,
		.form-check-label label {
			font-weight: 400!important;
		}

		input[type=checkbox],
		input[type=radio],
		input[type=checkbox] + label,
		input[type=radio] + label,
		.form-check-label,
		.custom-check-text label {
			cursor: pointer;
		}
	</style>
<?php $this->stop_style([
	"id" => "html_elements_checkbox"
]); ?>

<?php if(!$native) { ?>
	<div class="d-inline-block">
		<div class="form-check">
			<input <?=generate_html_attributes($attributes)?> <?=isset($checked) && $checked ? 'checked' : ''?>>
			<label class="form-check-label" for="<?=$attributes["id"]?>"><?=$text ?? ""?></label>
		</div>
	</div>
<?php } else { ?>
	<div class="d-inline-block">
		<input <?=generate_html_attributes($attributes)?> <?=isset($checked) && $checked ? 'checked' : ''?>>
		<label for="<?=$attributes["id"]?>" class="pl-1"><?=$text ?? ""?></label>
	</div>
<?php } ?>