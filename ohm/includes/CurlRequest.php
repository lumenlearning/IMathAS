<?php

namespace OHM;

require_once(__DIR__ . "/HttpRequest.php");

class CurlRequest implements HttpRequest
{
	private $handle = null;
	private $url = null;

	public function __construct($url)
	{
		$this->handle = curl_init($url);
		$this->url = $url;
	}

	public function setOption($name, $value)
	{
		curl_setopt($this->handle, $name, $value);
	}

	public function execute()
	{
		$result = curl_exec($this->handle);

		if (null == $result) {
			error_log(get_class() . " - For URL: " . $this->url);
			error_log(get_class() . " - cURL error: " . curl_error($this->handle));
		}

		return $result;
	}

	public function getInfo($name)
	{
		return curl_getinfo($this->handle, $name);
	}

	public function close()
	{
		curl_close($this->handle);
	}
}

