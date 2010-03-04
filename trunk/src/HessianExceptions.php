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
 * Represents an error while parsing an input stream
 * @author vsayajin
 */
class HessianParsingException extends Exception {
	// TODO custom constructors
	public $position;
	public $details;
};

/**
 * Remote Exception, nuff said
 * @author vsayajin
 *
 */
class HessianFault extends Exception{
	var $detail;
	
	function __construct($message = '', $code = '', $detail = null){
		$this->message = $message;
		$this->code = $code;
		$this->detail = $detail;
	}
}