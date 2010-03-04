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
 * Represents a stream of bytes used for reading
 * @author vsayajin
 */
class HessianStream{
	public $data;
	public $pos = 0;
	public $last;
	public $len;
	
	function __construct($data = null){
		if($data)
			$this->setStream($data);
	}
	
	function setStream($data){
		$this->data = $data;
		$this->len = strlen($data);
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


