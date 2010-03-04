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

/**
 * Defines a contract for object creation used by the decoders
 */
interface IHessianObjectFactory{
	public function getObject($type);
}

/**
 * Used for custom parsers that handle a particular datatype
 * @author vsayajin
 */
interface IHessianCustomParser{
	function parse($parser, $data);
}

/**
 * Used for custom writers that handle a particular class type
 * @author vsayajin
 */
interface IHessianCustomWriter{
	function write($writer, $data);
}

class HessianCallingContext{
	public $writer;
	public $parser;
	public $transport;
	public $typemap;
	public $options;
	public $stream;
	public $isClient = true;
	public $call;
	public $url;
	public $payload;
}

/**
 * Defines a contract for an interceptor that executes around calls to remote services
 * in both client and servers
 * @author vsayajin
 */
interface IHessianInterceptor{
	function beforeRequest(HessianCallingContext $context);
	function afterRequest(HessianCallingContext $context);
}