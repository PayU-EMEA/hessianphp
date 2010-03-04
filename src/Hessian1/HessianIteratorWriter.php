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
 * Special writer for classes derived from Iterator. Hessian 1 protocol
 * Resolves and writes either a list or a map using the type information optioally.
 * @author vsayajin
 *
 */
class HessianIteratorWriter{
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
		
		$islist = HessianUtils::isListIterate($list);
		if($islist){
			$stream = 'V';
			if($this->usetype && $type)
				$stream .= $writer->writeType($type);
			if($total !== false)
				$stream .= $writer->writeInt($total);
			foreach($list as $value){
				$stream .= $writer->writeValue($value);
			}
			$stream .= 'z';
			return $stream;
		} else {
			$stream = 'M';
			if($this->usetype){
				$mapType = $type ? $type : $class;
				$stream .= $writer->writeType($mapType);
			}
			foreach($elements as $key => $value){
				$stream .= $writer->writeValue($key);
				$stream .= $writer->writeValue($value);
			}
			return $stream . 'z';
		}
	}

	function getCount($list){
		if($list instanceof Countable)
			return count($list);
		return false;
	}
}