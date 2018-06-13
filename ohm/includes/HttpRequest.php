<?php

namespace OHM\Includes;

interface HttpRequest
{
	public function __construct();

	public function reset();

	public function setUrl($url);

	public function setOption($name, $value);

	public function execute();

	public function getInfo($name);

	public function close();
}

