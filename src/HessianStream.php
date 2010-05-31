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
 * @author vsayajin
 */
class HessianStream{
	public $data;
	public $pos = 0;
	public $last;
	public $len;
	
	function __construct($data = null, $length = null){
		if($data)
			$this->setStream($data, $length);
	}
	
	function setStream($data, $length = null){
		$this->data = $data;
		if (isset($length)) {
			$this->len = $length;
		} else {
			$this->len = strlen($data);
		}
		$this->pos = 0;
		$this->last = '';
	}
	
	public function peek($count = 1, $pos = null){
		if($pos == null)
			$pos = $this->pos;
		return substr($this->data, $pos, $count);
	}

	public function read($count=1){
		if($count == 0)
			return;
		$newpos = $this->pos + $count;
		if($newpos > $this->len)
			throw new Exception('read past end of file: '.$newpos);
		$this->last = $count == 1 ?
			$this->data[$this->pos] :
			substr($this->data, $this->pos, $count);
		//$this->last = substr($this->data, $this->pos, $count);
		$this->pos = $newpos;
		return $this->last;
	}
	
	public function readAll(){
		$this->pos = $this->len;
		return $this->data;		
	}

	public function write($data){
		$this->data .= $data;
		$this->len += strlen($data);
	}
 
	public function flush(){}
	
	public function getData(){
		return $this->data;
	}
	
	public function close(){		
	}
}


