<?php
    namespace App;

    class Bootstrap {

        public function init() {
            \App\System\MVC\View\View::addSourcePath(__DIR__ . "/views");
            \App\System\CLI::register([
                "setup" => \App\CLI\Setup::class,
                "cache:clear" => \App\CLI\CacheClear::class,
                "cache:prune" => \App\CLI\CachePrune::class,
            ]);
            \App\System\Project::pushModule("MontiCore", MontiCoreController::class);
        }
    }
