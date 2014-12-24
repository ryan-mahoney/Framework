<?php
date_default_timezone_set('UTC');
require __DIR__.'/../vendor/autoload.php';
(new Opine\Framework())->frontController();
