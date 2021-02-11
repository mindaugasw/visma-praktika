<?php
require_once(__DIR__.'/../autoload.php');

(new \App\Service\App($argv))->autoChooseCommand();
