<?php
/**
 * XmlSerializerTest.php
 *
 * (c) Andreas Mueller <webmaster@am-wd.de>
 */

namespace AMWD;
require_once __DIR__.'/../src/XmlSerializer.php';

/**
 * Test collection to check functionality of Xml Serializer.
 *
 * @package    AMWD
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @link       https://bitbucket.org/BlackyPanther/xmlserializer
 */
class XmlSerializerTest extends \PHPUnit_Framework_TestCase {

	public function testSimpleTest() {
		$obj = new \stdClass();
		$obj->eins = 'one';
		$obj->zwei = 'two';

		$expected = '<?xml version="1.0"?>'.PHP_EOL.'<stdClass><eins>one</eins><zwei>two</zwei></stdClass>'.PHP_EOL;

		$xml = new XmlSerializer();
		$serialized = $xml->Serialize($obj);

		$this->assertEquals($expected, $serialized);

		$deserialized = $xml->Deserialize($expected);

		$this->assertEquals($obj->eins, $deserialized->eins);
		$this->assertEquals($obj->zwei, $deserialized->zwei);
	}

}

?>
