<?php
	$map_id = preg_replace("/[^a-z_]+/i", "", $id ?? sha1(microtime() . rand(0, 9999)));
	$map_var = preg_replace("/[^a-z]+/i", "", $map_var ?? sha1(microtime() . rand(0, 9999)));
	$options = $options ?? [];
	$options["container"] = $map_id;
	$styles = isset($styles) && is_array($styles) ? $styles : [
		"default" => [
			"source" => "https://api.maptiler.com/maps/streets/style.json"
		]
	];
	$options["style"] = ($styles["default"] ?? $styles[0])["source"] . "?key=" . $api_key;
	$markers = !empty($markers) && is_array($markers) ? $markers : [];
	$controls = isset($controls) && is_bool($controls) ? $controls : true;
	$user_position = isset($user_position) && is_bool($user_position) ? $user_position : false;
	$user_position_options = isset($user_position_options) && is_array($user_position_options) ? $user_position_options : [];
?>

<?php $this->start_style("css"); ?>
	<link rel="stylesheet" type="text/css" href="https://unpkg.com/maplibre-gl@5.1.0/dist/maplibre-gl.css">
	<style>
		#<?=$map_id?> {
			min-height: 500px;
		}

		#quadrants-container > [id*="quadrant-"] {
			position: absolute;
			z-index: 10;
			width: 49%;
			padding: 10px;
		}

		#quadrant-1 {
			top: 0px;
			left: 0px;
		}

		#quadrant-2 {
			top: 0px;
			right: 0px;
			padding-right: 50px !important;
		}

		#quadrant-3 {
			bottom: 0px;
			left: 0px;
		}

		#quadrant-4 {
			bottom: 0px;
			right: 0px;
		}

		.maplibregl-popup-content.mapboxgl-popup-content * {
			outline: none;
		}

		.change-map-style .card {
			border-bottom: 2px solid transparent;
		}

		.map-view-change > .change-map-style:not(:first-child) {
			margin-top: 10px;
		}

		.change-map-style.active .card {
			border-bottom-color: var(--bs-primary)!important;
		}

		.card.position-mark {
			padding: 9px 11px;
			margin-bottom: 0px;
		}
	</style>
<?php $this->stop_style([
	"id" => "maptiler-map-css"
]); ?>
<?php $this->start_script("js"); ?>
	<script src="https://unpkg.com/maplibre-gl@5.1.0/dist/maplibre-gl.js"></script>
	<?php if(!empty($api_key)): ?>
		<script>
			var <?=$map_var?> = new maplibregl.Map(<?=json_encode($options)?>);

			<?=$map_var?>.onMarkerClick = function(element) {
				<?=$markerClick ?? ""?>
			}

			<?=$map_var?>.onPolygonClick = function(polygonData, evt) {}

			<?=$map_var?>.addMarker = function(markerData) {
				markerData.iconType = markerData.iconType || "img";
				var template = `
					<div
						class="marker-wrapper ` + (markerData.class || "") + `"
						style="
							` + (markerData.iconType == "img" ? `background-image: url(` + markerData.icon + `);` : '') + `
							background-size: cover;
							width: ` + (markerData.width || "36px") + `;
							height: ` + (markerData.height || "46px") + `;
							cursor: ` + (markerData.cursor || "pointer") + `;
							margin: 0 auto;
						"
						title="` + (markerData.title || "") + `"
						>
						` + (markerData.iconType == "svg" ? markerData.icon : '') + `
					</div>
				`;
				var element = document.createElement("div");
				element.setAttribute("class", "marker-wrapper " + (markerData.class || ""));
				element.setAttribute("style",
					(markerData.iconType == "img" ? `background-image: url(` + markerData.icon + `);` : '') + `
					background-size: cover;
					width: ` + (markerData.width || "36px") + `;
					height: ` + (markerData.height || "46px") + `;
					cursor: ` + (markerData.cursor || "pointer") + `;
					margin: 0 auto;
				`);
				element.title = markerData.title || "";
				element._data = markerData;
				element.innerHTML = markerData.iconType == "svg" ? markerData.icon : '';

				$(element).click(function() {
					<?=$map_var?>.onMarkerClick(element);
				});

				var marker = new maplibregl.Marker({element: element});

				element._marker = marker;
				
				if((typeof markerData.popup === "boolean") && markerData.popup) {
					var popup = new maplibregl.Popup(markerData.popupOptions || {})
						.setHTML('<div class="content-before"></div>' + markerData.popupContent + '<div class="content-after"></div>' || "");
					;
					marker.setPopup(popup);
				}
				try {
					marker.setLngLat([markerData.lng, markerData.lat]).addTo(<?=$map_var?>);
				}
				catch(error) {
					//console.log(error);
					//console.log(markerData);
				}

				<?=$map_var?>._markers.push(marker);

				return marker;
			}

			window.onresize = function() {
				<?=$map_var?>.resize();
			}

			<?=$map_var?>._markers = [];
			<?=$map_var?>._layers = [];
			<?=$map_var?>._sources = [];

			<?=$map_var?>._clearMarkers = function() {
				for(var i in <?=$map_var?>._markers) {
					<?=$map_var?>._markers[i].remove();
				}
				//<?=$map_var?>._markers = [];
			};

			<?=$map_var?>._clearLayers = function() {
				for(var i in <?=$map_var?>._layers) {
					if(<?=$map_var?>.getLayer(<?=$map_var?>._layers[i])) {
						<?=$map_var?>.removeLayer(<?=$map_var?>._layers[i]);
					}
				}
				<?=$map_var?>._layers = [];
			};

			<?=$map_var?>._clearSources = function() {
				for(var i in <?=$map_var?>._sources) {
					if(<?=$map_var?>.getSource(<?=$map_var?>._sources[i])) {
						<?=$map_var?>.removeSource(<?=$map_var?>._sources[i]);
					}
				}
				<?=$map_var?>._sources = [];
			};

			<?=$map_var?>._clear = function() {
				<?=$map_var?>._clearLayers();
				<?=$map_var?>._clearSources();
				<?=$map_var?>._clearMarkers();
			}

			<?=$map_var?>._setObjects = function() {
				if(typeof <?=$map_var?>.setPolygons === "function") {
					<?=$map_var?>.setPolygons();
				}
				if(typeof <?=$map_var?>.setLines === "function") {
					<?=$map_var?>.setLines();
				}
				if(typeof <?=$map_var?>.setPoints === "function") {
					<?=$map_var?>.setPoints();
				}
			}

			<?php if(count($styles) > 1): ?>
				$(".change-map-style").click(function(e) {
					e.preventDefault();
					$(".change-map-style.active").removeClass("active");
					$(this).addClass("active");
					<?=$map_var?>.setStyle($(this).attr("data-source") + "?key=<?=$api_key?>", {
						diff: false
					});
				});
				<?=$map_var?>.on("style.load", function () {
					if(typeof <?=$map_var?>.onStyleChange === "function") {
						<?=$map_var?>.onStyleChange();
					}
					<?=$map_var?>._setObjects();
				});
			<?php endif; ?>
		</script>
	<?php endif; ?>
<?php $this->stop_script([
	"id" => "maptiler-map-js"
]); ?>

<div>
	<div id="<?=$map_id?>"></div>
	<div id="quadrants-container">
		<div id="quadrant-1"><?= $quadrant_1 ?? "" ?></div>
		<div id="quadrant-2" class="text-right"><?= $quadrant_2 ?? "" ?></div>
		<div id="quadrant-3">
			<?php if(count($styles) > 1): ?>
				<div class="map-view-change d-inline-block mt-3">
					<?php $iteration = 0; ?>
					<?php foreach($styles as $index => $style): ?>
						<?php
							if(isset($styles["default"])) {
								$active = $index === "default";
							}
							else {
								$active = $iteration++ === 0;
							}
						?>
						<a href="#" class="change-map-style mr-2 mb-2 mb-sm-0 <?=$active ? "active" : ""?> d-inline-block" data-source="<?=$style["source"]?>">
							<span class="card p-1 mb-0"><?=$style["title"]?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if($user_position): ?>
				<a href="#" id="<?=$map_id?>-position-request" class="card py-1 px-2 position-mark d-inline-block">@fas("map-marker-alt")</a>
			<?php endif; ?>
			<?= $quadrant_3 ?? "" ?>
		</div>
		<div id="quadrant-4" class="text-right"><?= $quadrant_4 ?? "" ?></div>
	</div>
</div>