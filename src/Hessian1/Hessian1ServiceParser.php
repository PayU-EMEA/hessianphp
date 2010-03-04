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

include_once 'Hessian1Parser.php';

class Hessian1ServiceParser extends Hessian1Parser{

	function parseTop(){
		$code = $this->read();
		$value = null;
		switch($code){
			case 'r':
				$version = $this->read(2);
				$value = $this->parseReply();
				break;
			case 'f':
				$value = $this->parseFault();
				break;
			case 'c':
				$version = $this->read(2);
				$value = $this->parseCall();
				break;
			default:
				throw new HessianParsingException("Unrecognized code $code at the start of the stream");
		}
		return $value;
	}
	
	function parseCall(){
		$this->parseHeaders();
		$code = $this->read();
		if($code != 'm') {
			throw new HessianParsingException('Hessian Parser, Malformed call: Expected m'); 
		}
		$call = new HessianCall();
		$call->method = $this->parseString($code, ord($code));
		$end = false;
		do{
			$code = $this->read();
			if($code == 'z')
				$end = true;
			else
				$call->arguments[] = $this->parseCheck($code);
		} while(!$end);
		return $call;
	}
	
	function parseReply(){
		$this->parseHeaders();
		$code = $this->read(1);
		if($code == 'f'){
			$value = $this->parseFault();
		} else
			$value = $this->parseCheck($code);
		if($this->read(1) == 'z')
			return $value;
	}
	
	function parseFault(){
		$code = $this->read(1);
		while($code != 'z'){
			$key = $this->parse($code);
			$value = $this->parse();
			$map[$key] = $value;
			$code = $this->read(1);
		}
		$fault = new HessianFault($map['message'], $map['code'], $map);
		throw $fault;
	}

	function parseHeaders(){
		$code = $this->stream->peek();
		if($code != 'H')
			return;
		throw new Exception('Headers currently not supported');			
		// TODO headers
	}
	
}