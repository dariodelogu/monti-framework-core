<?php
    namespace App\System;

    class Bootstrap {

        public function init() {
            //\App\System\MVC\View\View::addSourcePath(__DIR__ . "/views/HTMLelements", "HTMLElement");
            //\App\System\MVC\View\View::addSourcePath(__DIR__ . "/views", "generic");
            \App\System\MVC\View\View::addSourcePath(__DIR__ . "/../views");
        }
    }