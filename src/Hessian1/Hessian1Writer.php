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

class Hessian1Writer{
	var $dateAdapter;
	var $refmap;
	var $customHandler;
	var $typemap;
	var $log = array();
	
	function __construct(){
		$this->refmap = new HessianReferenceMap();
		$this->typemap = new HessianTypeMap();
		$this->customHandler = new HessianCustomTypeHandler();
	}
	
	function logMsg($msg){
		$this->log[] = $msg;
	}
	
	function setCustomHandlers($handlers){
		$this->customHandler->setHandlers($handlers);
	}
	
	function setTypeMap($typemap){
		$this->typemap = $typemap;
	}	
	
	function writeValue($value, $stream = null){
		$type = gettype($value);
		$dispatch = '';
		// TODO usar algun type helper
		switch($type){
			case 'integer': $dispatch = 'writeInt' ;break;
			case 'boolean': $dispatch = 'writeBool' ;break;
			case 'string': $dispatch = 'writeString' ; break;
			case 'double': $dispatch = 'writeDouble' ; break;
			case 'array': $dispatch = 'handleArray' ; break;
			case 'object': $dispatch = 'writeMap' ;break;
			case 'NULL': return 'N';
			case 'resource': $dispatch = 'writeResource' ; break;
			default: 
				throw new Exception("Handler for type $type not implemented");
		}
		$this->logMsg("dispatch $dispatch");
		$data = $this->$dispatch($value);
		return $data;	
	}

	function handleArray($array){
		if(empty($array))
			return 'N';
		$refindex = $this->refmap->getReference($array);
		if($refindex !== false){
			return $this->writeReference($refindex);
		}
		if(HessianUtils::isListFormula($array))
			return $this->writeList($array);
		return $this->writeMap($array);
	}
	
	function writeBool($value){
		return $value ? 'T' : 'F';
	}

	function writeString($value){
		return 'S' . $this->writeStringData($value);
	}

	function writeStringData($value){
		$stream = pack('n',strlen($value));
		$stream .= utf8_encode($value);
		return $stream;
	}
	
	function writeHeader($value){
		return 'H'. $this->writeStringData($value);
	}

	function writeBytes($value){
		return 'B' . $this->writeStringData($value);
	}

	function writeInt($value){
		return 'I' . $this->writeIntData($value);
	}

	function writeLong($value){
		$stream = 'L';
		$less = $value>>32;
		$res = $value / HessianUtils::pow32; //pow(2,32);
		$stream .= pack('N2',$res,$less);
		return $stream;
	}

	function writeDate($value){
		$ts = $this->dateAdapter->toTimestamp($value);
		$this->logMsg("writeDate $ts");
		$stream = 'd';
	    $ts = $ts * 1000;
		$res = $ts / HessianUtils::pow32;
		$stream .= pack('N', $res);
		$stream .= pack('N', $ts);
		return $stream;
	}

	// OJO que no se sabe si la representacion interna de PHP sea 64 bit IEEE 754
	function writeDouble($value){
		$stream = 'D';
		$stream .= HessianUtils::doubleBytes($value);
		return $stream;
	}

	function writeReference($value){
		$this->logMsg("writeReference $value");
		return 'R' . $this->writeIntData($value);
	}

	function writeType($type){
		return 't' . $this->writeStringData($value);
	}
	
	function writeIntData($value){
		$stream = pack('c', ($value >> 24));
		$stream .= pack('c', ($value >> 16));
		$stream .= pack('c', ($value >> 8));
		$stream .= pack('c', $value);
		return $stream;
	}
	
	function writeList($list){
		$refindex = $this->refmap->getReference($list);
		if($refindex !== false){
			return $this->writeReference($refindex);
		}
		
		$this->refmap->objectlist[] = &$list;
		$stream = 'V';
		//$stream .= $this->writeType('');
		if(!empty($list)){
			$stream .= 'l' . $this->writeIntData(count($list));
			foreach($list as $val){
				$stream .= $this->writeValue($val);
			}
		}
		$stream .= 'z';
		return $stream;
	}
	
	function writeMap($value){
		if($this->dateAdapter->isDatetime($value))
			return $this->writeDate($value);
		
		$refindex = $this->refmap->getReference($value);
		if($refindex !== false){
			return $this->writeReference($refindex);
		}

		$handler = $this->customHandler->getHandler($value);
		if($handler)
			return $handler->write($this, $value);
		
		//if($this->iteratorWriter->isIterator($value))
		//	return $this->iteratorWriter->write($value);
			
		$stream = "M";
		// type handling for local classes
		$stream .= 't';
		if(is_object($value)) {
			$localType = get_class($value);
			$type = $this->typemap->getRemoteType($localType);
			if(!$type) $type = $localType;
			$stream .= $this->writeStringData($type);
		}
		else
			$stream .= $this->writeStringData('');
		
		if(is_array($value)) {
			$this->refmap->objectlist[] = &$value;
			// arrays
			foreach($value as $key => $val){
				$stream .= $this->writeValue($key);
				$stream .= $this->writeValue($val);
			}
		}
		if(is_object($value)) {
			// classes
			$this->refmap->objectlist[] = $value;
			$vars = get_object_vars($value);
			foreach($vars as $varName => $varValue){
				$stream .= $this->writeValue($varName);
				$stream .= $this->writeValue($value->$varName);
			}
		}

		$stream .= 'z';
		return $stream;
	}

	function writeResource($handle){
		$type = get_resource_type($handle);
		$stream = '';
		if($type == 'file' || $type == 'stream'){
			while (!feof($handle)) {
				$content = fread($handle, 32768);
				$tag = 'b';
				if(feof($handle))
					$tag = 'B';
				//echo strlen($content).'<br>';
				$stream .= $tag . pack('n',strlen($content));
				$stream .= $content;
			}
			fclose($handle);
			return $stream;
		} else {
			throw new HessianParsingException("Cannot handle resource of type '$type'");	
		}
	}
	
	
	
}