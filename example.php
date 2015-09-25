<?php

require_once __DIR__.'/src/XmlSerializer.php';

// initialize new XmlSerializer
$serializer = new AMWD\XmlSerializer();

// create test object
$date = new stdClass();
$date->year = 2015;
$date->month = 9;
$date->day = 25;

$object = new stdClass();
$object->firstname = "Andreas";
$object->lastname = "Mueller";
$object->today = $date;
$object->languages = array('C#', 'PHP', '...');
$object->meetings = array('Breakfast' =>  '09:00', 'Lunch' => '12:30', 'Dinner' => '19:00');

// serialize test object to XML document
// flag indicates function to format whitespaces before return
$xml = $serializer->Serialize($object, true);

// print out XML document of object
echo $xml.PHP_EOL;

// deserialize XML document back to object
$obj = $serializer->Deserialize($xml);

// and print deserialized object
print_r($obj);
?>
