<?php
	$attributes = $attributes ?? [];
	$attributes["class"] = "d-none " . ($attributes["class"] ?? "");
	$attributes["type"] = "file";
	$attributes["id"] = !empty($attributes["id"]) && is_string($attributes["id"]) ? $attributes["id"] : \App\Classes\Str::rand_letters_string();
	$id = $attributes["id"];
	$label = !empty($label) && is_string($label) ? $label : __("Carica");
	$btn_type = !empty($btn_type) && is_string($btn_type) ? $btn_type : "btn-primary";
	$inline = !empty($inline) && is_bool($inline) ? $inline : true;
	$init = isset($init) && is_bool($init) ? $init : true;
?>

<div class="custom-input-file <?=$inline ? 'd-inline' : ''?>" data-id="<?=$id?>">
	<label class="btn <?=$btn_type?> mb-0 font-weight-normal" for="<?=$id?>"><?=__($label)?></label>
	<div id="<?=$id?>-container">
		<span id="<?=$id?>-text" class="mt-3"></span>
		<span id="<?=$id?>-cancel" class="d-none cursor-pointer">&#10006;</span>
	</div>
	<input <?=generate_html_attributes($attributes ?? [])?>>
</div>

<?php $this->start_script(); ?>
	<script>
		var initInputFile = function(el) {
			var id = el.getAttribute("data-id");
			var input = el.querySelector("#" + id);
			var cancel = el.querySelector("#" + id + "-cancel");
			cancel.addEventListener("click", function() {
				el.querySelector("#" + id + "-text").innerHTML = "";
				this.classList.add("d-none");
				input.value = "";
			});
			input.addEventListener("change", function() {
				el.querySelector("#" + id + "-text").innerHTML = Array.from(this.files).map(file => file.name).join(", ");
				el.querySelector("#" + id + "-cancel").classList.remove("d-none");
			});
		}
	</script>
<?php $this->stop_script(["id" => "input_file"]); ?>
<?php
	if($init):
		$this->start_script();
			?>
				<script opt-group="js-ready">
					document.addEventListener("DOMContentLoaded", function() {
						document.querySelectorAll(".custom-input-file").forEach(function(el) {
							initInputFile(el);
						});
					});
				</script>
			<?php
		$this->stop_script();
	endif;
?>