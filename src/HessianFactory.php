<?php
/*
 * This file is part of the HessianPHP package.
 * (c) 2004-2010 Manuel Gómez
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include_once 'HessianInterfaces.php';
include_once 'HessianExceptions.php';
include_once 'HessianParsing.php';
include_once 'HessianOptions.php';
include_once 'HessianUtils.php';
include_once 'HessianCustomTypeHandler.php';
include_once 'HessianReferenceMap.php';
include_once 'HessianTypeMap.php';
include_once 'HessianStream.php';
include_once 'HessianDatetimeAdapter.php';

define('HESSIAN_PHP_VERSION', '2.0');

/**
 * Default implementation of an object factory 
 */
class HessianObjectFactory implements IHessianObjectFactory{
	var $options;
	public function setOptions(HessianOptions $options){
		$this->options = $options;
	}
	
	public function getObject($type){
		if(!class_exists($type)) {
			if(isset($this->options->strictType) && $this->options->strictType)
				throw new Exception("Type $type cannot be found for object instantiation, check your type mappings");
			$obj = new stdClass();
			$obj->__type = $type;
			return $obj;
		}
		return new $type();
	}
}

/**
 * Handles que creation of components for assembling Hessian clients and servers
 * It contains the basic assembly configuration for these components.
 * @author vsayajin
 *
 */
class HessianFactory{
	var $protocols = array();
	var $transports = array();
	static $cacheRules = array();
	
	/**
	 * Returns a specialized HessianParser object based on the options object
	 * @param HessianStream $stream input stream
	 * @param HessianOptions $options configuration options
	 */
	function getParser($stream, $options){
		$version = $options->version;
		if($options->detectVersion)
			$version = $this->detectVersion($stream);
		$config = $this->getConfig($version);
		$class = $config['parser']; 
		$inc = $config['folder'].'/'. $class .'.php';	
		include_once $inc;
		$resolver = $this->getRulesResolver($version, $config);
		$parser = new $class($resolver, $stream, $options);
		$parser->dateAdapter = $this->getComponent('HessianDatetimeAdapter', $options->dateAdapter);
		$parser->setCustomHandlers($options->customParsers);
		$parser->objectFactory = $this->getComponent('HessianObjectFactory', $options->objectFactory);
		$parser->objectFactory->setOptions($options);
		return $parser;
	}
	
	/**
	 * Returns a specialized HessianWriter object based on the options object
	 * @param HessianStream $stream output stream
	 * @param HessianOptions $options configuration options
	 */
	function getWriter($stream, $options){
		$version = $options->version;
		if($options->detectVersion)
			$version = $this->detectVersion($stream);
		$config = $this->getConfig($version);
		$class = $config['writer']; 
		$inc = $config['folder'].'/'. $class .'.php';	
		include_once $inc;
		$writer = new $class($options);
		$writer->dateAdapter = $this->getComponent('HessianDatetimeAdapter', $options->dateAdapter);
		$handlers = array_merge($config['customWriters'], 
				$options->customWriters);
		$writer->setCustomHandlers($handlers);
		return $writer;
	}
	
	function getComponent($default, $definition = null){
		$objdef = $default;
		if($definition)
			$objdef = $definition;
		if(is_object($objdef))
			return $objdef;	
		if(is_string($objdef)){
			if(!class_exists($objdef))
				include_once($objdef.'.php'); // hacky at best
			return new $objdef();
		}
	}
	
	/**
	 * Creates a parsing helper object (rules resolver) that uses a protocol
	 * rule file to parse the incomin stream. It caches the rules for further
	 * use.
	 * @param Integer $version Protocol version
	 * @param array $config local component configuration
	 */
	public function getRulesResolver($version, $config = null){
		if(isset(self::$cacheRules[$version]))
			return self::$cacheRules[$version];
		if(!$config)
			$config = $this->getConfig(version);
		$rulesPath = $config['folder'].'/'.$config['parsingRules'];
		$resolver = new HessianRuleResolver();
		$resolver->loadRulesFromFile($rulesPath);
		self::$cacheRules[$version] = $resolver;
		return $resolver;
	}
	
	/**
	 * Receives a stream and iterates over que registered protocol handlers
	 * in order to detect which version of Hessian is it
	 * @param HessianStream $stream
	 * @return integer Protocol version detected
	 */
	function detectVersion($stream){
		foreach($this->protocols as $version => $config){
			$callback = $config['detectVersion'];
			$res = $this->$callback($stream);
			if($res)
				return $version;		
		}
		throw new Exception("Cannot detect protocol version on stream");
	}
	
	function getConfig($version){
		if(!isset($this->protocols[$version]))
			throw new Exception("No configuration for version $version protocol");
		return $this->protocols[$version];
	}
	
	function getTransport(HessianOptions $options){
		$type = $options->transport;
		if(is_object($type))
			return $type;
		if(!isset($this->transports[$type]))
			throw new HessianException("The transport of type $type cannot be found");
		$class = $this->transports[$type];
		$trans = $this->getComponent($class);
		$trans->testAvailable();
		return $trans; 
	}
	
	function __construct(){
		$this->protocols = array(
			'2'=>array(
				'folder' => 'Hessian2',
				'parsingRules' => 'hessian2rules.php',
				'parser' => 'Hessian2ServiceParser',
				'writer' => 'Hessian2ServiceWriter',
				'detectVersion' => 'detectHessian2',
				'customWriters' => array('Iterator' => 'Hessian2IteratorWriter')
			),	
			'1' => array(
				'folder' => 'Hessian1',
				'parsingRules' => 'hessian1rules.php',
				'parser' => 'Hessian1ServiceParser',
				'writer' => 'Hessian1ServiceWriter',
				'detectVersion' => 'detectHessian1',
				'customWriters' => array('Iterator' => 'Hessian1IteratorWriter')
			)
			
		);
		$this->transports = array(
			'CURL' => 'HessianCURLTransport',
			'http' => 'HessianHttpStreamTransport'
		);
	}
	
	// custom version detection functions
	
	function detectHessian2($stream){
		$version = $stream->peek(3, 0);
		return $version == "H\x02\x00";
	}
	
	function detectHessian1($stream){
		$head = $stream->peek(1, 0);
		if($head == 'f')
			return true;
		$head = $stream->peek(3, 0);
		return $head == "c\x01\x00" || $head == "r\x01\x00";
	}
	
}




