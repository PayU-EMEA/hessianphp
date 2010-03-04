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
 * Configuration options for use in Hessian services, both client and server
 */
class HessianOptions{
	public $version = 2;
	/**
	 * Name of the transport to use
	 * @var string
	 */
	public $transport = 'CURL';
	/**
	 * Array of custom options to pass to the transport object
	 * @var array
	 */
	public $transportOptions;
	public $detectVersion = false;
	public $objectFactory;
	public $typeMap = array();
	public $headers = array();
	public $interceptors = array();
	public $customWriters = array();
	public $customParsers = array();
	public $dateAdapter;
	public $timeZone;
	public $saveRaw = false;
	
	public $serviceName = '';
	public $displayInfo = false;
	public $ignoreOutput = false;	
	
	/**
	 * Takes an array and matches the corresponding properties in this object
	 * @param array $arr
	 */
	public function fromArray(array $arr){
		foreach($arr as $key=>$value){
			if(isset($this->$key))
				$this->$key = $value;
		}
	}
	
	/**
	 * Tries to resolve a HessianOptions object from either an object or an array.
	 * Always return an options object
	 * @param mixed $object variable to resolve
	 * @return HessianOptions
	 */
	public static function resolveOptions($object){
		$options = new HessianOptions();
		if($object == null)
			return $options;
		if($object instanceof HessianOptions)
			return $object;
		else if(is_array($object))
			$options->fromArray($object);
		else if(is_object($object)){
			$arr = (array)$object;
			$options->fromArray($arr);
		}
		return $options;
	}
}