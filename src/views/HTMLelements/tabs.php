<?php
	$tab_id = $tab_id ?? \App\Classes\Str::rand_letters_string();
?>
<?php if(count($tabs) == 1) { ?>
	<?php
		$first_key = array_key_first($tabs);
		$link_key = $tabs[$first_key]["link_key"] ?? preg_replace("/[^a-z]/i", "-", mb_strtolower($tabs[$first_key]["title"]));
		$attributes = isset($tabs[$first_key]["attributes"]) ? $tabs[$first_key]["attributes"] : [];
	?>
	<div id="<?= $link_key ?>">
		<?= $tabs[$first_key]["content"]?>
	</div>
<?php } else { ?>
	<ul class="nav nav-tabs" id="<?= $tab_id ?>" role="tablist">
		<?php foreach($tabs as $tab) { ?>
			<?php
				$link_key = $tab["link_key"] ?? preg_replace("/[^a-z]/i", "-", mb_strtolower($tab["title"]));
				$attributes = isset($tab["attributes"]) ? $tab["attributes"] : [];
			?>
			<li class="nav-item">
				<a class="nav-link h-100 <?= ($tab["active"] ?? null) == true ? "active" : "" ?>" id="<?= $link_key ?>-tab" data-bs-toggle="tab" <?= generate_html_attributes($attributes)?> href="#<?= $link_key ?>" role="tab" aria-controls="<?= $link_key ?>" aria-selected="true"><?= $tab["title"]?></a>
			</li>
		<?php } ?>
	</ul>
	<div class="tab-content" id="<?= $tab_id ?>Content">
		<?php foreach($tabs as $tab) { ?>
			<?php $link_key = $tab["link_key"] ?? preg_replace("/[^a-z]/i", "-", mb_strtolower($tab["title"])); ?>
			<div class="tab-pane fade pt-3 <?= ($tab["active"] ?? null) == true ? "active show" : "" ?>" id="<?= $link_key ?>" role="tabpanel" aria-labelledby="<?= $link_key ?>-tab"><?= $tab["content"]?></div>
		<?php } ?>
	</div>
<?php } ?>

<?php $this->start_script(); ?>
	<script opt-group="view-tabs">
		Array.from(document.querySelectorAll("#<?=$tab_id?> a")).forEach(function (triggerEl) {
			var tabTrigger = new bootstrap.Tab(triggerEl)
			triggerEl.addEventListener('click', function (e) {
				e.preventDefault();
				tabTrigger.show();
			});
		});
	</script>
<?php $this->stop_script(); ?>