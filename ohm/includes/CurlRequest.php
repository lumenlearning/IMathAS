<?php

namespace OHM\Includes;

class CurlRequest implements HttpRequest
{
	private $handle = null;
	private $url = null;

	public function __construct()
	{
		$this->handle = curl_init();
	}

	public function reset()
	{
		$this->url = null;
		$this->handle = curl_init();
	}

	public function setUrl($url)
	{
		$this->url = $url;
		curl_setopt($this->handle, CURLOPT_URL, $url);
	}

	public function setOption($name, $value)
	{
		curl_setopt($this->handle, $name, $value);
	}

	public function execute()
	{
		$result = curl_exec($this->handle);

		if (!$result) {
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

