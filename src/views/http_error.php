<?php $this->parent(view("html")) ?>

<?php $this->start_style() ?>
	<style>
		.error-main {
			min-height: 100vh;
		}
		
		.error-wrapper {
			display: flex;
			align-items: center;
			min-height: 100vh;
			backdrop-filter: blur(10px);
		}

		.error-wrapper > div {
			flex: 1 1 auto;
			text-align: center;
		}
		
		.error-code {
			font-size: 10rem;
			line-height: 0.8;
			margin-top: -30px;
		}
		
		.error-message {
			font-size: 2.5rem;
		}
	</style>
<?php $this->stop_style() ?>

<?php $this->start_section("body") ?>
	<div class="error-main">
		<div class="error-wrapper">
			<div>
				<div class="error-code mb-3"><?=$code?></div>
				<div class="error-message"><?=$message?></div>
			</div>
		</div>
	</div>
<?php $this->stop_section() ?>