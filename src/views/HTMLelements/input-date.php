<?php
	$options = $options ?? [];
	$attributes = $attributes ?? [];
	$attributes["class"] = "form-control fp-input " . ($attributes["class"] ?? "");
	$attributes["id"] = $attributes["id"] ?? "fp-" . \App\Classes\Str::rand_letters_string();

	$time = isset($time) && is_bool($time) ? $time : false;
	$options["enableTime"] = (bool)($options["enableTime"] ?? $time);
	$attributes["type"] = "date";
	if($options["enableTime"]) {
		$attributes["type"] = "datetime-local";
	}

	$options["clearable"] = isset($options["clearable"]) && is_bool($options["clearable"]) ? $options["clearable"] : true;
	$options["noCalendar"] = (bool)($options["noCalendar"] ?? false);
	$options["allowInput"] = (bool)($options["allowInput"] ?? true);
	$options["time_24hr"] = (bool)($options["time_24hr"] ?? true);
	$init = (bool)($init ?? true);

	if(!$options["noCalendar"]) {
		//format displayed
		$options["altFormat"] = $options["altFormat"] ?? "d/m/Y";
	}
	else {
		$options["altFormat"] = $options["altFormat"] ?? "";
	}

	//format sent to server
	$options["dateFormat"] = $options["dateFormat"] ?? "Y-m-d";

	//enable altFormat usage
	$options["altInput"] = true;

	$options["locale"] = "it";

	if($options["enableTime"]) {
		if($options["noCalendar"]) {
			$options["dateFormat"] = "H:i";
			$options["altFormat"] = "H:i";
			$attributes["type"] = "time";
		}
		else {
			$options["dateFormat"] .= " H:i";
			$options["altFormat"] .= " H:i";
		}
	}
?>

<?php $this->start_style(); ?>
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<?php $this->stop_style(["id" => "input-date-style"]); ?>

<?php $this->start_script(); ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
	<script src="https://npmcdn.com/flatpickr/dist/l10n/it.js"></script>
	<script opt-group="input-date-group">
		initDateInput = function (el, options) {
		    options = options || {};
		    options.disableMobile = false;
		    //options.enableTime = options.enableTime || true;
		    options.dateFormat = options.dateFormat || "Y-m-d H:i";
		    options.altFormat = options.altFormat || "d/m/Y H:i";
		    options.altInput = typeof options.altInput == "boolean" ? options.altInput : true;
		    options.allowInput = typeof options.allowInput == "boolean" ? options.allowInput : true;
		    options.time_24hr = typeof options.time_24hr == "boolean" ? options.time_24hr : true;
		    //options.clearable = typeof options.clearable == "boolean" ? options.clearable : true;
		    options.locale = options.locale || "it";
		    if(!flatpickr.l10ns[options.locale]) {
		        delete options.locale;
		    }

		    if(typeof options.defaultDate === "string") {
		        if(options.defaultDate.indexOf(":") === -1) {
		            options.defaultDate += "\T00:00";
		        }
		        el.value = options.defaultDate;
		    }

		    /* Init only if not mobile device */
		    var init = typeof window.orientation === "undefined"
		    || window.navigator.userAgent.indexOf("Mobile") === -1
		    ||  !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
		    if(typeof el === "object" && el != null) {
		        if(el._flatpickr == undefined && init) {
		            var fp = el.flatpickr(options);
		        }
		        if(options.clearable) {
		            el.parentNode.querySelector(".clear-trigger").addEventListener("click", function() {
		                init ? fp.clear() : el.value = "";
		            });
		        }
		        return init ? fp : undefined;
		    }
		}
		flatpickr.localize(flatpickr.l10ns.it);
	</script>
<?php $this->stop_script(["id" => "input-date-script"]); ?>
<?php $this->start_script(); ?>
	<script opt-group="input-date-group">
		<?php if($init) { ?>
			document.addEventListener("DOMContentLoaded", function() { initDateInput(document.getElementById("<?=$attributes["id"]?>"), <?=json_encode($options)?>); });
		<?php } ?>
	</script>
<?php $this->stop_script(); ?>

<?php if($options["clearable"]) { ?>
	<div class="input-group">
	  <input <?=generate_html_attributes($attributes)?>>
	  <div class="input-group-append">
	    <span class="input-group-text clear-trigger"><i class="bi bi-x"></i></span>
	  </div>
	</div>
<?php } else { ?>
	<input <?=generate_html_attributes($attributes)?>>
<?php } ?>