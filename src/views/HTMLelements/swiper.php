<?php
	$id = $id ?? \App\Classes\Str::rand_letters_string();

	$var = $var ?? ("swiper" . $id);

	$options = isset($options) && is_array($options) ? $options : [];

	$pagination = (isset($pagination) && is_bool($pagination) && $pagination) || isset($options["pagination"]);
	//$navigation puo' essere: false (nessuna navigazione), true (bottoni di default dentro lo slider),
	//oppure un array ["prevEl" => "#selettore", "nextEl" => "#selettore"] per bottoni HTML esterni gia' presenti in pagina;
	//in questo caso lo slider non genera markup proprio, usa solo quei selettori.
	$custom_navigation = is_array($navigation ?? null);
	$navigation_enabled = $custom_navigation || ($navigation ?? false) === true;
	$scrollbar = isset($scrollbar) && is_bool($scrollbar) ? $scrollbar : false;
	$zoom = (isset($zoom) && is_bool($zoom) && $zoom) || isset($options["zoom"]);

	if($pagination) {
		$pagination_default = [
			"el" => "#" . $id . " .swiper-pagination"
		];
		$options["pagination"] = array_merge($pagination_default, $options["pagination"] ?? []);
	}

	if($navigation_enabled) {
		$options["navigation"] = $custom_navigation ? $navigation : [
			"prevEl" => "#" . $id . " .swiper-button-prev",
			"nextEl" => "#" . $id . " .swiper-button-next",
		];
	}

	if($scrollbar) {
		$options["scrollbar"] = [
			"el" => "#" . $id . " .swiper-scrollbar"
		];
	}

	if($zoom) {
		$zoom_default = [
			"minRatio" => 1
		];
		$options["zoom"] = array_merge($zoom_default, $options["zoom"] ?? []);
	}
?>

<?php $this->start_style() ?>
	<link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.1/swiper-bundle.css" crossorigin="anonymous" referrerpolicy="no-referrer" onload="this.onload=null;this.rel='stylesheet'">
	<!-- niente fallback <noscript>: viene "spacchettato" dal motore di view di questo componente
	(a differenza del blocco head statico di ris_home.php), producendo un <link> bloccante duplicato.
	Accettabile: senza JS lo slider Swiper stesso non funzionerebbe comunque. -->
	<style>
		:root {
			--swiper-theme-color: var(--primary)!important;
		}
	</style>
<?php $this->stop_style([
	"id" => "swiper-init-css"
]); ?>

<!-- Slider main container -->
<div class="swiper" id="<?=$id?>">
	<!-- Additional required wrapper -->
	<div class="swiper-wrapper">
		<!-- Slides -->
		<?php foreach($slides ?? [] as $slide): ?>
			<div class="swiper-slide<?=!empty($slide["class"]) ? " " . $slide["class"] : ""?>"<?php foreach($slide["attrs"] ?? [] as $attr_name => $attr_value): ?> <?=$attr_name?>="<?=$attr_value?>"<?php endforeach; ?>>
				<?php if(!empty($slide["title"])): ?>
					<div><?=$slide["title"]?></div>
				<?php endif; ?>
				<?php if($zoom): ?>
					<div class="swiper-zoom-container">
				<?php endif; ?>
						<?=$slide["content"]?>
				<?php if($zoom): ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if($pagination): ?>
		<div class="swiper-pagination"></div>
	<?php endif; ?>
	<?php if($navigation_enabled && !$custom_navigation): ?>
		<div class="swiper-button-prev"></div>
		<div class="swiper-button-next"></div>
	<?php endif; ?>
	<?php if($scrollbar): ?>
		<div class="swiper-scrollbar"></div>
	<?php endif; ?>
</div>

<?php $this->start_script(); ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.1/swiper-bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php $this->stop_script([
	"id" => "swiper-init-js"
]); ?>

<?php $this->start_script(); ?>
	<script opt-group="swiper-init-group">
		document.addEventListener("DOMContentLoaded", function() {
			<?=$var?> = new Swiper('#<?=$id?>', <?=json_encode($options)?>);
		});
	</script>
<?php $this->stop_script(); ?>