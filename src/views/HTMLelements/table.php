<?php
	$columns = !empty($columns) ? $columns : [];
	$rows = !empty($rows) ? $rows : [];
	$footer = !empty($footer) ? $footer : [];
	$id = !empty($id) && is_string($id) ? $id : \App\Classes\Str::rand_letters_string();
	$class = !empty($class) && is_string($class) ? $class : "";
	$responsive = isset($responsive) && is_bool($responsive) ? $responsive : true;
	$striped = isset($striped) && is_bool($striped) ? $striped : true;

	$advanced = isset($advanced) && is_bool($advanced) ? $advanced : false;
	$var_name = !empty($var_name) && is_string($var_name) ? $var_name : \App\Classes\Str::rand_letters_string();
	$options = $options ?? [];
	if(isset($options["remoteURL"])) {
		$options["fromRemote"] = true;
	}
	$options["name"] = $var_name;
?>
<?php if($advanced): ?>
	<?php $this->start_style() ?>
		<link rel="stylesheet" type="text/css" href="/src/evoTable/evo-table.css">
	<?php $this->stop_style() ?>
<?php endif; ?>

<div class="<?=$responsive ? 'table-responsive' : ''?>">
	<table class="table<?=$striped ? ' table-striped ' : ''?>table-hover <?=$class?>" id="<?=$id?>">
		<?php if(!empty($columns)): ?>
			<thead>
				<tr>
					<?php foreach($columns as $h) { ?>
						<th scope="col"><?=$h?></th>
					<?php } ?>
				</tr>
			</thead>
		<?php endif; ?>
		<tbody>
			<?php foreach($rows as $tr) { ?>
				<tr>
					<?php foreach($tr as $td) { ?>
						<td><?=$td?></td>
					<?php } ?>
				</tr>
			<?php } ?>
			<?php foreach($rows_raw ?? [] as $tr) { ?>
				<?= $tr ?>
			<?php } ?>
		</tbody>
		<?php if(!empty($footer)) { ?>
			<tfoot>
				<tr>
					<?php foreach($footer as $h) { ?>
						<td><?=$h?></td>
					<?php } ?>
				</tr>
			</tfoot>
		<?php } ?>
	</table>
</div>

<?php if($advanced): ?>
	<?php $this->start_script(); ?>
		<script type="text/javascript" src="/src/evoTable/EvoTable.js"></script>
	<?php $this->stop_script(["id" => "datatable"]); ?>
	<?php $this->start_script(); ?>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				<?=$var_name?> = new EvoTable(document.getElementById("<?=$id?>"), <?=json_encode($options)?>);
			});
		</script>
	<?php $this->stop_script(); ?>
<?php endif; ?>