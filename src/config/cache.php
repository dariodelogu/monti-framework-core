<?php
	return [
		/**
		 * The store used by the project, one of the keys of "stores"
		 */
		"default" => "file",

		/**
		 * Each store has the class of a Laminas cache adapter in "adapter",
		 * the other keys are the options of the adapter
		 */
		"stores" => [
			"file" => [
				"adapter" => \Laminas\Cache\Storage\Adapter\Filesystem::class,

				/**
				 * Folder of the cached files, it must be writable by the web server
				 */
				"cache_dir" => root_path("cache/data"),

				/**
				 * Lifetime in seconds of an entry, 0 means it never expires
				 */
				"ttl" => 0,

				/**
				 * Permissions of the created folders and files
				 */
				"dir_permission" => 0775,
				"file_permission" => 0664
			]
		]
	];
