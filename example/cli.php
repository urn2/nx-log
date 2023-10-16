<?php
include '../vendor/autoload.php';
const AGREE_LICENSE=true;
date_default_timezone_set('Asia/Shanghai');

class app_cli extends nx\app{
	use \nx\parts\log\file, \nx\parts\output\cli;
	public function main():void{
		$this->log('ok');
		$this->out('done.');
	}
}
(new app_cli())->run();
