<?php
	$attributes = $attributes ?? [];
	$attributes["id"] = !empty($attributes["id"]) && is_string($attributes["id"]) ? $attributes["id"] : \App\Classes\Str::rand_letters_string();
	$attributes["class"] = "d-none";
	$id = $attributes["id"];
	$var_name = $var_name ?? str_replace("-", "_", $id);
	$preview_style = $preview_style ?? "tab";
?>

<?php $this->start_style() ?>
	<link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css"/>
<?php $this->stop_style(["id" => "markdown_style_init"]) ?>

<div id="<?=$id?>-editor-container"></div>
<textarea <?=generate_html_attributes($attributes ?? [])?>><?=htmlspecialchars($content ?? "")?></textarea>

<?php $this->start_script() ?>
	<script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
<?php $this->stop_script(["id" => "markdown_script_init"]) ?>
<?php $this->start_script() ?>
	<script opt-group="markdown-editor-init">
		<?=$var_name?> = new toastui.Editor({
			el: document.querySelector('#<?=$id?>-editor-container'),
			height: "<?=$height ?? "auto"?>",
			initialEditType: 'markdown',
			previewStyle: '<?=$preview_style?>',
			initialValue: document.getElementById("<?=$attributes["id"]?>").value,
			usageStatistics: false,
			events: {
				change: function() {
					document.getElementById('<?=$id?>').value = <?=$var_name?>.getMarkdown();
				}
			}
		});
	</script>
<?php $this->stop_script() ?>