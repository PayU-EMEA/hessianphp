<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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