<?php
class Event {
	private static $router = [];
	
	public function register ($event, $callback) {
		if (!isset(self::$router[$event])) {
			self::$router[$event] = [];
		}
		self::$router[$event][] = $callback;
	}

	public function trigger ($event, $args=[]) {
		if (!isset(self::$router[$event])) {
			return;
		}
		foreach (self::$router[$event] as $callback) {
			call_user_func($callback, $args);
		}
	}

	public static function __callstatic($event, $args=[]) {
		if (is_callable($args[0]) && count($args) == 1) {
			Config::register($event, $args[0]);
			return;
		}
		Event::trigger($event, $args);
	}
}