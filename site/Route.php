<?php
class Route {
	public function collections () {
		return [
			['p' => 'blogs', 's' => 'blog', 'templates' => ['black', 'red']],
			['p' => 'pages', 's' => 'page']
		];
	}

	public function forms () {
		return [
			['contact']
		];
	}

	public function custom (&$app) {
		$app->get('/', function () {
			echo 'Homepage';
		});
	}

	public function separation () {
		return [
			'templatePath' => __DIR__ . '/html/'
		];
	}
}