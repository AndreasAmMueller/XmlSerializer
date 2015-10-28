# XmlSerializer

-----

As I tried many different approaches to get a simple to handle XML serializer, I had some issues with all of them (I think, I just didn't find the right one) and started to create my own attempt --- inspired by Microsofts [XmlSerializer](https://msdn.microsoft.com/en-us/library/system.xml.serialization.xmlserializer.aspx?cs-save-lang=1&cs-lang=csharp) class.

## Example

```php
<?php

// include needed class
// only dependency is PHP's own SimpleXML
require_once __DIR__.'/XmlSerializer/src/XmlSerializer.php';

// initialize new XmlSerializer
$serializer = new AMWD\XmlSerializer();

// explicitly allow associative arrays (Dictionary/Hashtable)
$serializer->AssociativeArray(true);

// create test objects
$date = new stdClass();
$date->year = 2015;
$date->month = 9;
$date->day = 25;

$object = new stdClass();
$object->firstname = "Andreas";
$object->lastname = "Mueller";
$object->today = $date;
$object->languages = array('C#', 'PHP', '...');
$object->meetings = array('Breakfast' =>  $date, 'Lunch' => '12:30', 'Dinner' => '19:00');

// serialize test object to XML document
// flag indicates function to format whitespaces before return
$xml = $serializer->Serialize($object, true);

// print out XML document of object
echo $xml.PHP_EOL;

// deserialize XML document back to object
$obj = $serializer->Deserialize($xml, true);

// and print deserialized object
print_r($obj);

?>

```

__Output:__

```
<?xml version="1.0"?>
<stdClass>
  <firstname>Andreas</firstname>
  <lastname>Mueller</lastname>
  <today>
    <year>2015</year>
    <month>9</month>
    <day>25</day>
  </today>
  <languages>C#</languages>
  <languages>PHP</languages>
  <languages>...</languages>
  <meetings>
    <key>Breakfast</key>
    <value>
      <year>2015</year>
      <month>9</month>
      <day>25</day>
    </value>
  </meetings>
  <meetings>
    <key>Lunch</key>
    <value>12:30</value>
  </meetings>
  <meetings>
    <key>Dinner</key>
    <value>19:00</value>
  </meetings>
</stdClass>



SimpleXMLElement Object
(
  [firstname] => Andreas
  [lastname] => Mueller
  [today] => SimpleXMLElement Object
  (
    [year] => 2015
    [month] => 9
    [day] => 25
  )

  [languages] => Array
  (
    [0] => C#
    [1] => PHP
    [2] => ...
  )

  [meetings] => Array
  (
    [0] => SimpleXMLElement Object
    (
      [key] => Breakfast
      [value] => SimpleXMLElement Object
      (
        [year] => 2015
        [month] => 9
        [day] => 25
      )
    )

    [1] => SimpleXMLElement Object
    (
      [key] => Lunch
      [value] => 12:30
    )

    [2] => SimpleXMLElement Object
    (
      [key] => Dinner
      [value] => 19:00
    )
  )
)
```

As you can see, all the magic stuff will be inside the class and you just need to call the simple functions to get it done.

-----

## Changelog

__2015-10-28__

- Added UnitTest
- Added Makefile for less work
- Associative arrays are errors by default
  * Associative arrays (dictionaries) are difficult to parse
  * Explicitly enable assoc. arrays with `AssociativeArray(true)`

__2015-09-25__

- Initial release
- very basic functionality
- only `Serialize()` and `Deserialize()` available
- no properties/configuration possible

-----

### LICENSE
My scripts are published under [MIT License](https://am-wd.de/?p=about#license).
