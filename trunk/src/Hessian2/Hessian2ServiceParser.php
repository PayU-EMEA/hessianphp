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

include_once 'Hessian2Parser.php';

class Hessian2ServiceParser extends Hessian2Parser{
		
	function parseTop(){
		$this->logMsg('Parsing top element');
		$this->parseVersion();
		$code = $this->read();
		$value = null;
		switch($code){
			case 'R':
				$value = $this->parseReply();
				break;
			case 'C':
				$value = $this->parseCall();
				break;
			case 'F':
				$value = $this->parseFault();
				break;
			case 'E':
				$value = $this->parseEnvelope();
				break;
		}
		return $value;
	}
	
	function parseVersion(){
		$version = $this->read(3);
	}
	
	function parseReply(){
		$this->logMsg('Parsing reply');
		return $this->parse(); 
	}
	
	function parseCall(){
		$this->logMsg('Parsing call');
		$call = new HessianCall();
		$call->method = $this->parse(null, 'string');
		$num = $this->parse(null, 'integer');
		for($i=0;$i<$num;$i++){
			$call->arguments[] = $this->parseCheck();
		}
		return $call;
	}
	
	function parseFault(){
		$this->logMsg('Parsing fault');
		$map = $this->parse(null, 'map');
		$fault = new HessianFault($map['message'], $map['code'], $map);
		throw $fault;
	}
	
	function parseEnvelope(){
		throw new Exception('Envelopes currently not supported');
	}
	
	function parsePacket(){
		throw new Exception('Packetc currently not supported');
	}
		
}