<?php

/**
 * XmlSerializer.php
 *
 * (c) Andreas Mueller <webmaster@am-wd.de>
 */

namespace AMWD;

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
 * @version    v1.0-20150925 | in developement
 */
class XmlSerializer {

	// --- Fields
	// ===========================================================================

	/**
	 * data array for all properties
	 * @var array
	 */
	private $data;

	/**
	 * internal version number
	 * @var string
	 */
	private $version = "1.0";

	// --- 'magic' methods
	// ===========================================================================

	/**
	 * Initializes a new instance of XmlSerializer.
	 *
	 * @return XmlSerializer
	 */
	function __construct() {
		if (!class_exists('SimpleXMLElement')) {
			$trace = debug_backtrace();
			trigger_error('Missing SimpleXML class in '
										.$trace[0]['file'].' at row '
										.$trace[0]['line']
										, E_USER_ERROR);
		}
	}

	/**
	 * 'magic' get method for all properties
	 *
	 * @param string $name name of the property
	 * @return mixed
	 */
	function __get($name) {
		if (array_key_exists($name, $this->data)) {
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
	function __set($name, $value) {
		$this->data[$name] = $value;
	}

	/**
	 * 'magic' check if property exists
	 *
	 * @param string $name name of the property
	 * @return bool
	 */
	function __isset($name) {
		return isset($this->data[$name]);
	}

	/**
	 * 'magic' property delete
	 *
	 * @param string $name name of the property
	 * @return void
	 */
	function __unset($name) {
		if (isset($this->data[$name])) {
			unset($this->data[$name]);
		}
	}

	/**
	 * 'magic' override for strings representation
	 *
	 * @return string
	 */
	function __toString() {
		return 'XmlSerializer v'.$this->version.' by AM.WD';
	}

	/**
	 * Serializes the specified object and returns the XML docuement as string.
	 *
	 * @param mixed $obj Object to serialialize.
	 * @param bool $formatted Set this flag to true if the XML document shold be returned in a formatted way.
	 *
	 * @return string
	 *
	 * @uses \SimpleXMLElement to create XML structure.
	 * @uses \DOMDocument to format XML document.
	 */
	public function Serialize($obj, $formatted = false) {
		if (is_object($obj)) {
			$root = new \SimpleXMLElement('<'.get_class($obj).'/>');

			$this->SerializeObject($obj, $root);
		} else if (is_array($obj)) {
			$root = new \SimpleXMLElement('<Array/>');

			$this->SerializeArray($obj, $root);
		} else {
			$name = gettype($obj);
			$root = new \SimpleXMLElement('<'.$name.'>'.$obj.'</'.$name.'>');
		}

		if ($formatted) {
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
	public function Deserialize($str) {
		libxml_use_internal_errors(true);

		if (!($xml = simplexml_load_string($str))) {
			throw new \InvalidArgumentException("XML document seems not to be valid");
		}

		return $xml;
	}

	/**
	 * Serializes an object to XML.
	 *
	 * @param mixed $obj Object to serialize.
	 * @param \SimpleXMLElement $node current xml node to add object to.
	 *
	 * @return void
	 */
	private function SerializeObject($obj, $node) {
		$properties = get_object_vars($obj);

		foreach ($properties as $key => $value) {
			if (is_object($value)) {
				$new = $node->addChild($key);
				$this->SerializeObject($value, $new);
			} else if (is_array($value)) {
				$this->SerializeArray($value, $node, $key);
			} else {
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
	private function SerializeArray($array, $node, $childname = null) {
		$assoc = $this->is_assoc($array);

		foreach ($array as $key => $value) {
			$name = ($childname == null) ? gettype($value) : $childname;

			if (is_object($value)) {
				if ($assoc) {
					$kvp = $node->addChild(get_class($value));
					$kvp->addChild('key', $key);
					$new = $kvp->addChild('value');
				} else {
					$new = $node->addChild(get_class($value));
				}

				$this->SerializeObject($value, $new);
			} else if (is_array($value)) {
				if ($assoc) {
					$kvp = $node->addChild($name);
					$kvp->addChild('key', $key);
					$new = $kvp->addChild('value');
				} else {
					$new = $node->addChild($name);
				}

				$this->SerializeArray($value, $new);
			} else {
				if ($assoc) {
					$kvp = $node->addChild($name);
					$kvp->addChild('key', $key);
					$kvp->addChild('value', $value);
				} else {
					$node->addChild($name, $value);
				}
			}
		}
	}

	/**
	 * Checks if given array is associative or iterative.
	 *
	 * @param array $array Array to check.
	 *
	 * @return bool
	 */
	private function is_assoc($array) {
		$key = key($array);
		return !is_int($key);
	}

}

?>
