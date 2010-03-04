<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RContract for a network request to remote services 
 */
interface IHessianTransport {
	/**
	 * Executes a POST request to a remote Hessian service and returns a
	 * HessianStream for reading data 
	 * @param string $url url of the remote service
	 * @param binary $data binary data payload
	 * @param HessianOptions $options optional parameters for the transport
	 * @return HessianStream input stream
	 */
	function getStream($url, $data, $options);
	/**
	 * Tests wether the transport is available in this installation 
	 */
	function testAvailable();
	function getMetadata();
}

/**
 * Hessian request using the CURL library
 */
class HessianCURLTransport implements IHessianTransport{
	var $metadata;
	var $rawData;
	
	function testAvailable(){
		if(!function_exists('curl_init'))
			throw new Exception('You need to enable the CURL extension to use the curl transport');
	}
	
	function getMetadata(){
		return $this->metadata;
	}
	
	function getStream($url, $data, $options){
		$ch = curl_init($url);
		if(!$ch)
			throw new Exception("curl_init error for url $url.");
		if(!empty($options->transportOptions))
			curl_setopt_array($ch, $options->transportOptions);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/binary"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
		$result = curl_exec($ch);
		if($result === false)
			throw new Exception("curl_exec error for url $url");
		if(!empty($options->saveRaw))
			$this->rawData = $result;
		$this->metadata = curl_getinfo($ch);
		curl_close($ch);
		$stream = new HessianStream($result);
		return $stream;
	}

}

/**
 * Hessian request using PHP's http streaming context
 */
class HessianHttpStreamTransport implements IHessianTransport{
	var $metadata;
	var $options;
	var $rawData;

	function testAvailable(){
		if(!ini_get('allow_url_fopen'))
			throw new Exception("You need to enable allow_url_fopen to use the stream transport");
	}
	
	function getMetadata(){
		return $this->metadata;
	}
	
	function getStream($url, $data, $options){
		$params = array(
		  'http'=> array (
		    'method'=>"POST",
		    'header'=>"Content-Type: application/binary\r\n" .
		              "Content-Length: ".strlen($data)."\r\n",
			'timeout' => 3,
			'content' => $data
			)
		);
		/*$context = stream_context_create($opts);
		if(empty($options->transportOptions['use.file_get_contents'])){		
			$fp = fopen($url, 'rb', false, $context);
			$res = stream_get_contents($fp);
			$this->metadata = stream_get_meta_data($fp);
			fclose($fp); 
		} else
			$res = file_get_contents($url, FILE_BINARY , $context);*/
			
		// TODO check the $php_errormsg thing
		$ctx = stream_context_create($params);
		$fp = fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		} 
		$this->metadata = stream_get_meta_data($fp);
		fclose($fp);
		if(!empty($options->saveRaw))
			$this->rawData = $response;
		$stream = new HessianStream($response);
		return $stream;
	}

}