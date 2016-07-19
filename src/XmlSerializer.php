<?php

/**
 * XmlSerializer.php
 *
 * (c) Andreas Mueller <webmaster@am-wd.de>
 */

namespace AMWD;

class_exists('SimpleXMLElement') || user_error('SimpleXMLElement class is required for XmlSerializer', E_USER_ERROR);
class_exists('DOMDocument') || user_error('DOMDocument class is recommended for XmlSerializer', E_USER_WARNING);

/**
 * Xml serialization for PHP.
 *
 * Xml serialization of objects to string and vice versa.
 * Inspired by Microsofts C# XmlSerializer class.
 * Uses SimpleXML for serialization.
 *
 * @package    AMWD
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @license    MIT - http://am-wd.de/index.php?p=about#license
 * @link       https://bitbucket.org/BlackyPanther/xmlserializer
 * @version    v1.1-20160719 | stable
 */
class XmlSerializer
{

	// --- Fields
	// ===========================================================================

	/**
	 * Data array for all properties
	 * @var array
	 */
	private $data;

	/**
	 * Internal version number
	 * @var string
	 */
	private $version = "1.0";
	
	/**
	 * Remind the name of the root element to write it back on serialization.
	 * @var string
	 */
	private $rootName = "";
	
	/**
	 * Gets or sets a value indicating whether associative arrays are allowed.
	 *
	 * Associative arrays are difficult to parse. Therefore they are not supported in C#
	 * and you need to allow them here explicitly.
	 *
	 * @var bool
	 */
	public $AllowAssociativeArray;
	
	/**
	 * Gets or sets a value indicating whether the serialized output is formatted with whitespaces.
	 * 
	 * @var bool
	 */
	public $Formatted = true;



	// --- 'magic' methods
	// ===========================================================================

	/**
	 * Initializes a new instance of XmlSerializer.
	 *
	 * @return XmlSerializer
	 */
	function __construct()
	{
		$this->AllowAssociativeArray = false;
	}

	/**
	 * 'magic' get method for all properties
	 *
	 * @param string $name name of the property
	 * @return mixed
	 */
	function __get($name)
	{
		if (array_key_exists($name, $this->data))
		{
			return $this->data[$name];
		}

		$trace = debug_backtrace();
		trigger_error('Undefined key for __get(): '
		              .$name.' in '
		              .$trace[0]['file'].' at row '
		              .$trace[0]['line']
		  , E_USER_NOTICE);

		return null;
	}

	/**
	 * 'magic' set method for all properties
	 *
	 * @param string $name name of the property
	 * @param mixed $value value of the property
	 * @return void
	 */
	function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * 'magic' check if property exists
	 *
	 * @param string $name name of the property
	 * @return bool
	 */
	function __isset($name)
	{
		return isset($this->data[$name]);
	}

	/**
	 * 'magic' property delete
	 *
	 * @param string $name name of the property
	 * @return void
	 */
	function __unset($name)
	{
		if (isset($this->data[$name]))
		{
			unset($this->data[$name]);
		}
	}

	/**
	 * 'magic' override for strings representation
	 *
	 * @return string
	 */
	function __toString()
	{
		return 'XmlSerializer v'.$this->version.' by AM.WD - http://am-wd.de';
	}

	// --- Getter/Setter
	// ===========================================================================
	public function GetRootName()
	{
		return $this->rootName;
	}


	// --- Public Methods
	// ===========================================================================

	/**
	 * Serializes the specified object and returns the XML document as string.
	 *
	 * @param  mixed   $obj       Object to serialialize.
	 * @param  string  $nodename  Name of the root element on serialization. If empty the property 'rootName' is used.
	 *
	 * @return string
	 *
	 * @uses \SimpleXMLElement to create XML structure.
	 * @uses \DOMDocument to format XML document.
	 */
	public function Serialize($obj, $nodename = '')
	{
		if (is_object($obj))
		{
			if (empty($nodename) && empty($this->rootName))
			{
				$root = new \SimpleXMLElement('<'.get_class($obj).'/>');
			}
			else if (empty($nodename) && !empty($this->rootName))
			{
				$root = new \SimpleXMLElement('<'.$this->rootName.'/>');
			}
			else
			{
				$root = new \SimpleXMLElement('<'.$nodename.'/>');
			}
			
			$this->SerializeObject($obj, $root);
		}
		else if (is_array($obj))
		{
			$root = new \SimpleXMLElement('<Array/>');
			$this->SerializeArray($obj, $root);
		}
		else
		{
			$name = gettype($obj);
			$root = new \SimpleXMLElement('<'.$name.'>'.$obj.'</'.$name.'>');
		}

		if ($this->Formatted && class_exists('DOMDocument'))
		{
			$dom = new \DOMDocument("1.0");
			$dom->preserveWhiteSpace = true;
			$dom->formatOutput = true;
			$dom->loadXml($root->asXML());

			return $dom->saveXml();
		}

		return $root->saveXML();
	}

	/**
	 * Deserializes the XML document contained by the string.
	 *
	 * @param string $str Serialized XML document.
	 *
	 * @return mixed
	 */
	public function Deserialize($str)
	{
		libxml_use_internal_errors(true);

		if (!($xml = simplexml_load_string($str)))
		{
			throw new \InvalidArgumentException("XML document seems not to be valid");
		}
		
		preg_match("/<([^?].*)>/", $str, $matches);
		if (!empty($matches[1]))
		{
			$this->rootName = $matches[1];
		}
		
		if ($this->AllowAssociativeArray)
		{
			$xml = $this->RebuildAssociative($xml);
		}
		
		return $xml;
	}

	// --- Protected Methods
	// ===========================================================================

	/**
	 * Checks if given array is associative or iterative.
	 *
	 * @param array $array Array to check.
	 *
	 * @return bool
	 */
	protected static function is_assoc($array)
	{
		$key = key($array);
		return is_array($array) && !is_int($key);
	}

	/**
	 * Serializes an object to XML.
	 *
	 * @param mixed $obj Object to serialize.
	 * @param \SimpleXMLElement $node current xml node to add object to.
	 *
	 * @return void
	 */
	protected function SerializeObject($obj, $node)
	{
		$properties = get_object_vars($obj);

		foreach ($properties as $key => $value)
		{
			if (is_object($value))
			{
				$new = $node->addChild($key);
				$this->SerializeObject($value, $new);
			}
			else if (is_array($value))
			{
				$this->SerializeArray($value, $node, $key);
			}
			else
			{
				$node->addChild($key, $value);
			}
		}
	}

	/**
	 * Serializes an array to XML.
	 *
	 * If given array is associative, the key will be saved as attribute.
	 *
	 * @param mixed[] $array Array to serialize.
	 * @param \SimpleXMLElement $node current xml node to add array to.
	 * @param string $childname name of node to create.
	 *
	 * @return void
	 */
	protected function SerializeArray($array, $node, $childname = null)
	{
		$assoc = self::is_assoc($array);
		
		if ($assoc)
		{
			if (!$this->AllowAssociativeArray)
			{
				throw new \UnexpectedValueException("Associative arrays are not allowed");
			}
			
			foreach ($array as $key => $value)
			{
				$name = ($childname == null) ? gettype($value) : $childname;
				
				if (is_object($value))
				{
					$name = ($childname == null) ? get_class($value) : $childname;
					
					$kvp = $node->addChild($name);
					$kvp->addChild('key', $key);
					$new = $kvp->addChild('value');
					$this->SerializeObject($value, $new);
				}
				else if (is_array($value))
				{
					$kvp = $node->addChild($name);
					$kvp->addChild('key', $key);
					$new = $kvp->addChild('value');
					$this->SerializeArray($value, $new);
				}
				else
				{
					$kvp = $node->addChild($name);
					$kvp->addChild('key', $key);
					$kvp->addChild('value', $value);
				}
			}
		}
		else
		{
			foreach ($array as $key => $value)
			{
				$name = ($childname == null) ? gettype($value) : $childname;
				
				if (is_object($value))
				{
					$name = ($childname == null) ? get_class($value) : $childname;
					
					$new = $node->addChild($name);
					$this->SerializeObject($value, $new);
				}
				else if (is_array($value))
				{
					$new = $node->addChild($name);
					$this->SerializeArray($value, $new);
				}
				else
				{
					$node->addChild($name, $value);
				}
			}
		}
	}
	
	/**
	 * Tries to rebuild the Functionality of associative arrays.
	 * 
	 * @param  mixed  $obj  Input for the function. Can be every datatype.
	 * 
	 * @return mixed
	 */
	protected function RebuildAssociative($obj)
	{
		switch (gettype($obj))
		{
			case 'object':
				$ar = (array)$obj;
				if (count($ar) == 1 && isset($ar[0]))
				{
					return $ar[0];
				}
				else if (isset($obj->key) && isset($obj->value))
				{
					$tmp = array();
					$tmp[$ar['key']] = $ar['value'];
					return $tmp;
				}
				else
				{
					$res = new \stdClass();
					
					foreach ($ar as $key => $value)
					{
						switch (gettype($value))
						{
							case 'array':
								$res->$key = array();
								if (isset($value[0]->key) && isset($value[0]->value))
								{
									foreach ($value as $i => $kvp)
									{
										$tmp = $this->RebuildAssociative($kvp);
										$res->$key[array_keys($tmp)[0]] = $tmp[array_keys($tmp)[0]];
									}
								}
								else
								{
									foreach ($value as $k => $v)
									{
										$res->$key[$k] = $v;
									}
								}
								break;
							default:
								$res->$key = $value;
								break;
						}
					}
					
					/*$ar = (array)$res;
					if (count($ar) == 1)
					{
						$key = array_keys($ar)[0];
						return $res->$key;
					}*/
					
					return $res;
				}
				break;
			default:
				return $obj;
		}
	}
}

?>