<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a stream of bytes used for reading
 * It doesn't use any of the string length functions typically used
 * for files because if can cause problems with encodings different than latin1
 * @author vsayajin
 */
class HessianStream{
	public $pos = 0;
	public $len;
	public $bytes = array();
	
	function __construct($data = null, $length = null){
		if($data)
			$this->setStream($data, $length);
	}
	
	function setStream($data, $length = null){
		$this->bytes = str_split($data);
		$this->len = count($this->bytes);
		$this->pos = 0;
	}
	
	public function peek($count = 1, $pos = null){
		if($pos == null)
			$pos = $this->pos;
		
		$data = '';
		for($i=0;$i<$count;$i++){
			if(isset($this->bytes[$pos]))
				$data .= $this->bytes[$pos];
			$pos++;
		}
		return $data;
	}

	public function read($count=1){
		if($count == 0)
			return;
		$data = '';
		for($i=0;$i<$count;$i++){
			if(isset($this->bytes[$this->pos]))
				$data .= $this->bytes[$this->pos];
			else
				throw new Exception('read past end of file: '.$this->pos);
			$this->pos++;
		}
		return $data;
	}
	
	public function readAll(){
		$this->pos = $this->len;
		return implode($this->bytes);		
	}

	public function write($data){
		$bytes = str_split($data);
		$this->len += count($bytes);
		$this->bytes = array_merge($this->bytes, $bytes);
	}
 
	public function flush(){}
	
	public function getData(){
		return implode($this->bytes);
	}
	
	public function close(){		
	}
}


