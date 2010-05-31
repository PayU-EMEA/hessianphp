<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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