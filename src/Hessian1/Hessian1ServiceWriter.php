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

include_once 'Hessian1Writer.php';

class Hessian1ServiceWriter extends Hessian1Writer{
	function writeHeaders(){
		// something
		return '';
	}
	
	function writeFault(Exception $ex, $detail = null){
		$code = $ex->getCode();
		$message = $ex->getMessage();
		$trace = $ex->getTraceAsString();		
		$stream = 'f';
		$stream .= $this->writeString('code');
		$stream .= $this->writeString($code);
		$stream .= $this->writeString('message');
		$stream .= $this->writeString($message);
		// OJO puede ser false o null o lo que sea, por lo pronto no es null
		if(!is_null($detail)){
			$stream .= $this->writeString('detail');
			$stream .= $this->writeValue($detail);
		}
		if(!is_null($trace)){
			$stream .= $this->writeString('trace');
			$stream .= $this->writeValue($trace);
		}
		$stream .= 'z';
		return $stream;
	}
	
	/**
	 * Writes a Hessian reply with a return object. If a fault has been set, it writes the fault instead
	 *  
	 * @param mixed object Object to be returned in the reply
	 * @return string Hessian reply
	 **/
	function writeReply($value){
		$stream = "r\x01\x00";
		$stream .= $this->writeHeaders();
		$stream .= $this->writeValue($value);
		$stream .= "z";
		return $stream;
	}

	/**
	 * Writes a Hessian method call and serializes arguments.
	 *  
	 * @param string method Method to be called
	 * @param array params Arguments of the method
	 * @return string Hessian call
	 **/
	function writeCall($method, $params = array()){
		$stream = "c\x01\x00m" . $this->writeStringData($method);
		$stream .= $this->writeHeaders();
		foreach($params as $param){
			$stream .= $this->writeValue($param);
		}
		$stream .= "z";
		return $stream;
	}
	
}