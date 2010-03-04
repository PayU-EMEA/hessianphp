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
 * General container for type, class definition and object/array references used in parsers and writers 
 * @author vsayajin
 */
class HessianReferenceMap{
	var $reflist = array();
	var $typelist = array();
	var $classlist = array();
	var $objectlist = array();
	
	function incReference($obj = null){
		$this->reflist[] =  new HessianRef(count($this->objectlist));
		if($obj != null)
			$this->objectlist[] = $obj;
	}
	
	function getClassIndex($class){
		foreach($this->classlist as $index => $classdef){
			if($classdef->type == $class)
				return $index;
		}
		return false;
		//return array_search($class, $this->classlist);
	}
	
	function addClassDef(HessianClassDef $classdef){
		$this->classlist[] = $classdef;
		return count($this->classlist) - 1;
	}
	
	function getReference($object){
		return array_search($object, $this->objectlist, true);
	}
	
	function getTypeIndex($type){
		return array_search($type, $this->typelist, true);
	}

	function reset(){
		$this->objectlist = 
			$this->reflist = 
			$this->typelist = 
			$this->classlist = array();
	}
	
}