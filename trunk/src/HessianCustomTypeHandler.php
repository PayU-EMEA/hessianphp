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
 * Manages resolution for custom parsers and writers. It uses an internal cache of
 * found and not found handlers to speed up resolution in big object lists.
 * WARNING: do not use stdClass for, well, there may be dragons type of thing...
 * @author vsayajin
 *
 */
class HessianCustomTypeHandler{
	var $cacheTypes = array();
	var $notFound = array();
	var $handlers = array();
	
	function setHandlers($handlers){
		$this->handlers = $handlers;
	}
	
	function getHandler($obj){
		if(!$this->handlers)
			return false;
		if(!is_object($obj))
			return false;
		$class = get_class($obj);
		if(isset($this->cacheTypes[$class]))
			return $this->cacheTypes[$class];
		if(isset($this->notFound[$class]))
			return false;
		foreach($this->handlers as $type => $handler){
			if($obj instanceof $type){
				$real = $this->getRealHandler($handler);
				$this->cacheHandlers[$class] = $real;
				return $real;
			}
		}
		$this->notFound[$class] = true;
		return false;
	}
	
	function getRealHandler($definition){
		// TODO use object Factory
		if(is_string($definition)){
			if(!class_exists($definition))
				include_once($definition.'.php');
			return new $definition();
		}
		if(is_object($definition))
			return $definition;		
	}
}