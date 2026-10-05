<?php
	router()->get("/sitemap.xml", [\App\System\Document\Document::class, "sitemap"])->name("sitemap.show");
	router()->get("/sitemap", [\App\System\Document\Document::class, "generate"])->name("sitemap.generate");

	router()->get("/robots.txt", [\App\System\Document\Document::class, "robots"])->name("robots");
	router()->get("/llms.txt", [\App\System\Document\Document::class, "llms"])->name("llms");
	router()->get("/site.webmanifest", [\App\System\Document\Document::class, "webmanifest"])->name("webmanifest");