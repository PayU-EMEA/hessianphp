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
 * Special writer for classes derived from Iterator. Hessian 2 protocol
 * Resolves and writes either a list or a map using the type information optioally.
 * @author vsayajin
 *
 */
class Hessian2IteratorWriter{
	var $usetype;
	var $writer;
	
	function isIterator($object){
		return is_object($object) && ($object instanceof Iterator);
	} 
	
	function write($writer, Iterator $list){
		$writer->logMsg('iterator writer');
		$writer->refmap->objectlist[] = $list; 
		
		$total = $this->getCount($list);
		$class = get_class($list);
		$type = $writer->typemap->getRemoteType($class);
		
		$mappedType = $type ? $type : $class; // OJO con esto
		
		$islist = HessianUtils::isListIterate($list);
		if($islist){
			list($stream, $terminate) = $this->listHeader($writer, $mappedType, $total);
			foreach($list as $value){
				$stream .= $writer->writeValue($value);
			}
			if($terminate)
				$stream .= 'Z';
			return $stream;
		} else {
			if($this->usetype && $mappedType){
				$stream = 'M';
				$stream .= $writer->writeType($mappedType);
			} else {
				$stream = 'H';
			}
			
			foreach($elements as $key => $value){
				$stream .= $writer->writeValue($key);
				$stream .= $writer->writeValue($value);
			}
			return $stream . 'Z';
		}
	}

	function listHeader($writer, $type, $total = false){
		$stream = '';
		$terminate = false;
		if($this->usetype && $type){ // typed
			if($total !== false){ // typed fixed length
				$stream = 'V';
				$stream .= $writer->writeType($type);
				$stream .= $writer->writeInt($total);
			} else { // typed variable length
				$stream = "\x55"; 
				$stream .= $writer->writeType($type);
				$terminate = true;
			}
		} else { // untyped
			if($total !== false){ //untyped fixed length
				$stream = "\x58"; 
				$stream .= $writer->writeInt($total);
			} else { // untyped variable length
				$stream = "\x57"; 
				$terminate = true;
			}
		}
		
		return array($stream, $terminate);
	}
	
	function getCount($list){
		if($list instanceof Countable)
			return count($list);
		return false;
	}
}