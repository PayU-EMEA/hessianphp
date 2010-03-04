<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class HessianBufferedStream{
	public $fp;
	public $pos = 0;
	public $last;
	public $len = 0;
	public $data;
	public $bufferSize = 1024;
	
	function __construct($fp, $bufferSize = 1024){
		if(!is_resource($fp))
			throw new Exception('Parameter fp must be a valid resource handle');
		$this->fp = $fp;
		$this->bufferSize = $bufferSize;
	}
	
	/*function __destruct(){
		$this->close();
	}*/
	
	function setStream($fp){
		$this->fp = $fp;
		$this->data = '';
		$this->len = 0;
	}
	
	public function peek($count = 1, $pos = null){
		if($pos == null)
			$pos = $this->pos;
		$newpos = $this->pos + $count;
		$this->checkRead($newpos);
		return substr($this->data, $pos, $count);
	}
	
	public function read($count=1){
		if($count == 0)
			return;
		$newpos = $this->pos + $count;
		$this->checkRead($newpos);
		$this->last = $count == 1 ?
			$this->data[$this->pos] :
			substr($this->data, $this->pos, $count);
		$this->pos = $newpos;
		return $this->last;
	}
	
	public function checkRead($newpos){
		if(feof($this->fp) && $newpos > $this->len)
			throw new Exception('read past end of file: '.$newpos);
		if($newpos > $this->len){
			while($this->len < $newpos){
				$this->data .= fread($this->fp, $this->bufferSize);
				$this->len = strlen($this->data);
			}
		}
	}
	
	public function EOF(){
		return feof($this->fp);
	}
	
	public function write($data){
		$this->data .= $data;
		$len = fwrite($this->fp, $data);
		$this->len += $len;
	}
	
	public function readAll(){
		$this->data .= stream_get_contents($this->fp);
		/*while(!feof($this->fp)){
			$this->data .= fread($this->fp, $this->bufferSize);
		}*/
		$this->len = strlen($this->data);
		return $this->data;		
	}
	
	public function flush(){
		fpassthru($this->fp);
		fflush($this->fp);
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function close(){
		@fclose($this->fp);
	}
}