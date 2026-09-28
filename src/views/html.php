<?php
	$document = \Document::get();
	$document
	->appendLink([
		"rel" => "stylesheet",
		"href" => "/src/bootstrap-5/bootstrap.min.css"
	], ["id" => "bs5-css"])
	->appendScript([
		"src" => "/src/bootstrap-5/bootstrap.bundle.min.js"
	], ["id" => "bs5-js"]);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?? \Language::get()?>">
	<head>
		<meta charset="UTF-8">
		<title><?=implode(" - ", array_filter([$document->title . $document->title_append, \Project::get()->name]))?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="theme-color" content="<?=config("app.colors.theme", "")?>">
		<?php $document->printMetaTags() ?>
		<link rel="canonical" href="<?=$document->canonical?>">
		<?php $document->printFaviconLinks() ?>
		<?php $this->section("head") ?>
		<?php $document->printStyles() ?>
	</head>
	<body>
		<?php $this->section("body") ?>
		<?php $document->printScripts() ?>
		<?php $this->section("js") ?>
	</body>
</html>