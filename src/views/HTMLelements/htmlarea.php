<?php
	$attributes = $attributes ?? [];
	$attributes["id"] = !empty($attributes["id"]) && is_string($attributes["id"]) ? $attributes["id"] : \App\Classes\Str::rand_letters_string();
	$id = $attributes["id"];
	$editor_var = $editor_var ?? str_replace("-", "_", $id);
	$height = isset($height) && is_numeric($height) ? $height : "300";

	$basic_layout = isset($basic_layout) && is_bool($basic_layout) ? $basic_layout : false;

	$toolbar = [
		"style" => [
			'style' => !$basic_layout,
		],
		"font" => [
			'bold' => true,
			'underline' => true,
			'italic' => true,
			'clear' => true,
		],
		"fontname" => [
			'fontname' => !$basic_layout,
		],
		"fontsize" => [
			'fontsize' => !$basic_layout,
		],
		"height" => [
			'height' => !$basic_layout,
		],
		"color" => [
			'color' => true,
		],
		"para" => [
			'ul' => true,
			'ol' => true,
			'paragraph' => true/*!$basic_layout*/,
		],
		"table" => [
			'table' => !$basic_layout,
		],
		"insert" => [
			'link' => !$basic_layout,
			'picture' => !$basic_layout,
			'video' => false,
		],
		"view" => [
			'fullscreen' => false,
			'codeview' => !$basic_layout,
			'help' => false
		]
	];

	$toolbar_parse = [];
	foreach($toolbar as $index => $options) {
		$item = array_keys(array_filter($options));
		if(!empty($item)) {
			$toolbar_parse[] = [$index, $item];
		}
	}
?>

<?php $this->start_style() ?>
	<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
	<style>
		.note-editor.note-frame.panel.panel-default {
			border-radius: .25rem;
		}

		.note-editor.note-frame.panel.panel-default .dropdown-toggle::after {
			display: none !important;
		}
	</style>
<?php $this->stop_style(["id" => "htmlarea_style_init"]) ?>

<textarea <?=generate_html_attributes($attributes ?? [])?>></textarea>

<?php $this->start_script() ?>
	<script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
	<script>
		var editorsInstances = {};
		var initHTMLArea = function(selector, options) {
			options = options || {};
			var obj = $(selector);
			obj.attr("data-editor", "<?=$editor_var?>");
			var instance = obj.summernote({
				height: options.height || 300,
				toolbar: options.toolbar || [],
				lineHeights: ["0", "0.4", "0.6", "0.8", "1.0", "1.2", "1.4", "1.5", "1.6", "1.8", "2.0", "3.0"],
			});
			editorsInstances["<?=$editor_var?>"] = instance;
			return instance;
		}
	</script>
<?php $this->stop_script(["id" => "htmlarea_script_init"]) ?>
<?php $this->start_script() ?>
	<script opt-group="htmlarea-editor-init">
		<?=$editor_var?> = initHTMLArea("#<?=$id?>", {
			height: <?=$height?>,
			toolbar: <?=json_encode($toolbar_parse)?>
		});
		<?=$editor_var?>.summernote("code", `<?=$content ?? ""?>`);
	</script>
<?php $this->stop_script() ?>