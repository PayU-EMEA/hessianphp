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
 * Represents a parsing rule with a type and a calling function
 * @author vsayajin
 */
class HessianParsingRule{
	var $type;
	var $func;
	var $desc;
	
	function __construct($type = '', $func = '', $desc = ''){
		$this->type = $type;
		$this->func = $func;
		$this->desc = $desc;
	}
}

/**
 * Contains the sequence of rules and start symbols that match the rules.
 * Resolves a rule based on a symbol and optionally checks for expected outcomes; 
 * @author vsayajin
 */
class HessianRuleResolver{
	public $rules = array();
	public $symbols = array();

	/**
	 * Takes a symbol and resolves a parsing rule to apply. Optionally it can
	 * check if the type resolved matches an expected type 
	 * @param string/int $symbol
	 * @param string $typeExpected
	 * @return HessianParsingRule rule to evaluate
	 */
	function resolveSymbol($symbol, $typeExpected = ''){
		$num = ord($symbol);
		if(!isset($this->symbols[$num]))
			throw new HessianParsingException("Code not recognized: 0x".dechex($num));
		$ruleIndex = $this->symbols[$num]; 
		$rule = $this->rules[$ruleIndex];
		if($typeExpected){
			if(!$this->checkType($rule, $typeExpected))
				throw new HessianParsingException("Type $typeExpected expected");
		}
		return $rule;
	}
	
	function checkType($rule, $types){
		$checks = explode(',', $types);
		foreach($checks as $type){
			if($rule->type == trim($type))
				return true;	
		}
		return false;
	}
	
	function loadRulesFromFile($file){
		//if(!file_exists($file))
		//	throw new HessianParsingException("Could not load parsing rules from file $file");
		include_once $file;
		$this->rules = $rules;
		$this->symbols = $symbols;
	}
}

/**
 * Interface used in parsers that need to ignore the incoming parsing value and continue
 * with the next in the stream
 * @author vsayajin
 */
interface HessianIgnoreCode{}

/**
 * Hold information on declared classes in the incoming payload
 * @author vsayajin
 */
class HessianClassDef implements HessianIgnoreCode{
	var $type;
	var $remoteType;
	var $props = array();
}

/**
 * Results from parsing a call to a local object
 * @author vsayajin
 *
 */
class HessianCall{
	var $method;
	var $arguments = array();
	
	function __construct($method='', $arguments=array()){
		$this->method = $method;
		$this->arguments = $arguments;
	}
}

/**
 * Represents an index to a reference. This hack is necessary for handling arrays
 * references
 * @author vsayajin
 *
 */
class HessianRef{
	var $index;
	
	static function isRef($val){
		return $val instanceof HessianRef;
	}
	
	static function getIndex($list){
		return new HessianRef($list);
	}
		
	function __construct($list){
		if(is_array($list))
			$this->index = count($list) - 1;
		else $this->index = $list;
	}
}