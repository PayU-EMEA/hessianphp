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
 * An interface for adapters that will work with UNIX timestamps
 * and datetime objects
 * @author vsayajin
 */
interface IHessianDatetimeAdapter{
	function toObject($timestamp, $utc = true);
	function toTimestamp($dateobj);
	function isDatetime($object);
}

/**
 * Default Datetime adapter that works with the built-in Datetime class of PHP5
 * @author vsayajin
 */
class HessianDatetimeAdapter implements IHessianDatetimeAdapter{
	function toObject($ts, $utc = false){
		if(!$utc)
			$date = date('c', $ts);
		else
			$date = gmdate('c', $ts);
		return new Datetime($date);	
	}
	function toTimestamp($dateobj){
		if(!($dateobj instanceof DateTime))
			throw new Exception('Date object not instance of DateTime');
		return $dateobj->format('U');
	}
	function isDatetime($object){
		return $object instanceof DateTime;
	}
} 