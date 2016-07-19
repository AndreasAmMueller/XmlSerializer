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
class XmlSerializerTest extends \PHPUnit_Framework_TestCase
{

	public function testSimpleTest()
	{
		$obj = new \stdClass();
		$obj->eins = 'one';
		$obj->zwei = 'two';

		$expected = '<?xml version="1.0"?>'.PHP_EOL.'<stdClass><eins>one</eins><zwei>two</zwei></stdClass>'.PHP_EOL;

		$xml = new XmlSerializer();
		$xml->Formatted = false;
		$serialized = $xml->Serialize($obj);

		$this->assertEquals($expected, $serialized, "Simple Serialization failed.");

		$deserialized = $xml->Deserialize($expected);

		$this->assertEquals($obj->eins, $deserialized->eins, "Simple Deserialize failed on first object.");
		$this->assertEquals($obj->zwei, $deserialized->zwei, "Simple Deserialize failed on second object.");
	}
	
	public function testAdvancedTest()
	{
		$xml = new XmlSerializer();
		$xml->Formatted = false;
		
		$date = new \stdClass();
		$date->year = '2015';
		$date->month = '9';
		$date->day = '25';

		$object = new \stdClass();
		$object->firstname = "Andreas";
		$object->lastname = "Mueller";
		$object->today = $date;
		$object->languages = array('C#', 'PHP', '...');
		$object->meetings = array('Breakfast' =>  $date, 'Lunch' => '12:30', 'Dinner' => '19:00');
		
		$expected = '<?xml version="1.0"?>'.PHP_EOL.'<stdClass><firstname>Andreas</firstname><lastname>Mueller</lastname><today><year>2015</year><month>9</month><day>25</day></today><languages>C#</languages><languages>PHP</languages><languages>...</languages><meetings><key>Breakfast</key><value><year>2015</year><month>9</month><day>25</day></value></meetings><meetings><key>Lunch</key><value>12:30</value></meetings><meetings><key>Dinner</key><value>19:00</value></meetings></stdClass>'.PHP_EOL;
		
		try
		{
			$xml->Serialize($object);
			$this->assertFalse(true, "Advanced Serialize failed => No Exception thrown on disabled assoc. Arrays.");
		}
		catch (\Exception $e)
		{
			// Well done
		}
		
		$xml->AllowAssociativeArray = true;
		$serialized = $xml->Serialize($object);
		
		$this->assertEquals($expected, $serialized, "Advanced Serialization failed.");
		
		$deserialized = $xml->Deserialize($expected);
		
		$this->assertEquals($object->firstname, $deserialized->firstname, "Advanced Deserialization failed on: firstname.");
		$this->assertEquals($object->lastname, $deserialized->lastname, "Advanced Deserialization failed on: lastname.");
		$this->assertEquals($object->today->year, $deserialized->today->year, "Advanced Deserialization failed on: today->year.");
		$this->assertEquals($object->today->month, $deserialized->today->month, "Advanced Deserialization failed on: today->month.");
		$this->assertEquals($object->today->day, $deserialized->today->day, "Advanced Deserialization failed on: today->day.");
		$this->assertEquals($object->languages[0], $deserialized->languages[0], "Advanced Deserialization failed on: languages[0].");
		$this->assertEquals($object->languages[1], $deserialized->languages[1], "Advanced Deserialization failed on: languages[1].");
		$this->assertEquals($object->languages[2], $deserialized->languages[2], "Advanced Deserialization failed on: languages[2].");
		$this->assertEquals($object->meetings["Breakfast"]->year, $deserialized->meetings["Breakfast"]->year, "Advanced Deserialization failed on: meetings[Breakfast]->year.");
		$this->assertEquals($object->meetings["Breakfast"]->month, $deserialized->meetings["Breakfast"]->month, "Advanced Deserialization failed on: meetings[Breakfast]->month.");
		$this->assertEquals($object->meetings["Breakfast"]->day, $deserialized->meetings["Breakfast"]->day, "Advanced Deserialization failed on: meetings[Breakfast]->day.");
		$this->assertEquals($object->meetings["Lunch"], $deserialized->meetings["Lunch"], "Advanced Deserialization failed on: meetings[Lunch].");
		$this->assertEquals($object->meetings["Dinner"], $deserialized->meetings["Dinner"], "Advanced Deserialization failed on: meetings[Dinner].");
	}

}

?>
