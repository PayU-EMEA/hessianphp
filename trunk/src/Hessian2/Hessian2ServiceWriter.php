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

include_once 'Hessian2Writer.php';

class Hessian2ServiceWriter extends Hessian2Writer{

	// web services
	
	function writeCall($method, $params = array()){
		$this->logMsg("call $method");
		$stream = $this->writeVersion();
		$stream .= 'C';
		$stream .= $this->writeString($method);
		$stream .= $this->writeInt(count($params));
		foreach($params as $param){
			$stream .= $this->writeValue($param);
		}
		return $stream;
	}
	
	function writeFault(Exception $ex, $detail = null){
		$this->logMsg("fault");
		$stream = $this->writeVersion();
		$stream .= 'F';
		$arr['message'] = $ex->getMessage();
		$arr['code'] = $ex->getCode();
		$arr['file'] = $ex->getFile();
		$arr['trace'] = $ex->getTraceAsString();
		$arr['detail'] = $detail;
		$stream .= $this->writeMap($arr);
		return $stream;
	}
	
	function writeReply($value){
		$this->logMsg("reply");
		$stream = $this->writeVersion();
		$stream .= 'R';
		$stream .= $this->writeValue($value);
		return $stream;
	}
	
	function writeVersion(){
		return "H\x02\x00";
	}

}