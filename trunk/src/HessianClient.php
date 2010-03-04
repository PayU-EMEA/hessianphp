<?php
/**
 * HessianPHP 2 Copyright 2009 Manuel Gómez
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 * http://www.apache.org/licenses/LICENSE-2.0 
 * 
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

include_once 'HessianFactory.php';
include_once 'HessianTransport.php';

/**
 * Proxy to issue RPC calls to remote Hessian services 
 */
class HessianClient{
	private $url;
	private $options;
	private $typemap;
	protected $factory;
	
	/**
	 * Creates a new Client proxy, takes an url and an optional options object
	 * that can also be an array
	 * @param string $url
	 * @param mixed $options
	 */
	public function __construct($url, $options = null){
		$this->url = $url;
		$this->options = HessianOptions::resolveOptions($options);
		$this->typemap = new HessianTypeMap($this->options->typeMap);
		$this->factory = new HessianFactory();
	}
	
	/**
	 * Issues a call to a remote service. It will raise a HessianFault exception if 
	 * there is an error
	 * @param string $method Name of the method in the remote service
	 * @param array $arguments Optional arguments
	 * @return mixed
	 */
	public function __hessianCall($method, $arguments = array()){
		if(strpos($method, "__") === 0)
			throw new HessianException("Cannot call methods that start with __");
		$transport = $this->factory->getTransport($this->options);
		$writer = $this->factory->getWriter(null, $this->options);
		$writer->setTypeMap($this->typemap);

		$ctx = new HessianCallingContext();
		$ctx->writer = $writer;
		$ctx->transport = $transport;
		$ctx->options = $this->options;
		$ctx->typemap = $this->typemap;
		$ctx->call = new HessianCall($method, $arguments);
		$ctx->url = $this->url;
		
		foreach($this->options->interceptors as $interceptor){
			$interceptor->beforeRequest($ctx);
		}
		
		$payload = $writer->writeCall($method, $arguments);
		$stream = $transport->getStream($this->url, $payload, $this->options);
		$parser = $this->factory->getParser($stream, $this->options);
		$parser->setTypeMap($this->typemap);
		// TODO deal with headers, packets and the rest of aditional stuff
		$ctx->parser = $parser;
		$ctx->stream = $stream;
		$ctx->payload = $payload;
		
		foreach($this->options->interceptors as $interceptor){
			$interceptor->afterRequest($ctx);
		}
		$result = $parser->parseTop();
		return $result;
	}
	
	/**
	 * Magic function wrapper for the remote call. It will fail if called
	 * with methods that start with __ which are conventionally private
	 * @param string $method
	 * @param array $arguments
	 * @return mixed Result of the remote call
	 */
	public function __call($method, $arguments){
		return $this->__hessianCall($method, $arguments); 
	}
	
	/**
	 * Returns this client's current options
	 * @return HessianOptions
	 */
	public function __getOptions(){
		return $this->options;
	}
	
	/**
	 * Returns the current typemap for this client
	 * @return HessianTypeMap
	 */
	public function __getTypeMap(){
		return $this->typemap;
	}
}