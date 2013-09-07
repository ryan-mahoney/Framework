<?php
class Generate {
	private $projectName = false;
	private $root = false; 
	private $mode = 'server';
	private $collections = [];

	public static function run ($path) {
		$generate = new Generate($path);
	}

	public function __construct ($projectName, $path, $mode='development') {
		$this->root = $path;
		$this->projectName = $projectName;
		$this->mode = $mode;
		$this->collections();
		$this->fixtures();
		$this->layouts();
		$this->templates();
		$this->forms();
		$this->separations();
		$this->admins();
		$this->events();
		$this->custom();
		$this->masterCache();
	}

	private function collections () {
		//read collections

		//read packages

		//write generated file

		//creeate file cache
	}

	private function fixtures () {
		//write fixtures to static files
	}

	private function layouts () {
		//generate a layout for each collection and singular if they don't already exist
	}

	private function templates () {
		//generate a template for each collection and singular if they don't already exist
	}

	private function forms () {
		//read forms

		//read packages

		//creeate file cache
	}

	private function separations () {
		//generate a separation config for each collection and singular if they don't already exist

		//use mode to determine where to get data from: local, development, production
	}

	private function intranets () {
		//read admin instranets

		//read packages

		//creeate file cache
	}

	private function events () {
		//read admins

		//read packages

		//creeate file cache
	}

	private function custom () {
		//read mvc setup

		//read packages

		///create file cache
	}

	private function masterCache () {
		//create maste route config file for everything -- on disk and in ram
	}
}

Generate::run($argv[1], $argv[2], $argv[3]);