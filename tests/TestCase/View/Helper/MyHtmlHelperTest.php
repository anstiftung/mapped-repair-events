<?php
declare(strict_types=1);
use App\View\Helper\MyHtmlHelper;
use Cake\View\View;

class MyHtmlHelperTest extends \PHPUnit\Framework\TestCase
{

    private MyHtmlHelper $MyHtmlHelper;

    public function setUp(): void
    {
        $this->MyHtmlHelper = new MyHtmlHelper(new View());
    }

    public function testGetCarbonFootprintAsStringSun()
    {
        $this->assertGetCarbonFootprintAsString(910000000, '910.000 t CO2 bzw. 3.823.529.412 km mit dem Flugzeug, entspricht ca. 25,5x der mittleren Entfernung zur Sonne');
    }

    public function testGetCarbonFootprintAsStringSun2()
    {
        $this->assertGetCarbonFootprintAsString(349532710.3, '349.533 t CO2 bzw. 1.468.624.833 km mit dem Flugzeug, entspricht ca. 10x der mittleren Entfernung zur Sonne');
    }

    public function testGetCarbonFootprintAsStringSun3()
    {
        $this->assertGetCarbonFootprintAsString(8800000, '8.800 t CO2 bzw. 36.974.790 km mit dem Flugzeug, entspricht ca. 97,5x der Entfernung zum Mond');
    }

    public function testGetCarbonFootprintAsStringMars2()
    {
        $this->assertGetCarbonFootprintAsString(4400000, '4.400 t CO2 bzw. 18.487.395 km mit dem Flugzeug, entspricht ca. 48,5x der Entfernung zum Mond');
    }

    public function testGetCarbonFootprintAsStringMars3()
    {
        $this->assertGetCarbonFootprintAsString(1580000, '1.580 t CO2 bzw. 6.638.655 km mit dem Flugzeug, entspricht ca. 17,5x der Entfernung zum Mond');
    }

    public function testGetCarbonFootprintAsStringMoon()
    {
        $this->assertGetCarbonFootprintAsString(200000, '200 t CO2 bzw. 840.336 km mit dem Flugzeug, entspricht ca. 21x um den Äquator');
    }

    public function testGetCarbonFootprintAsStringEarth()
    {
        $this->assertGetCarbonFootprintAsString(52000, '52 t CO2 bzw. 218.487 km mit dem Flugzeug, entspricht ca. 5,5x um den Äquator');
    }

    public function testGetCarbonFootprintAsStringEarthSmall()
    {
        $this->assertGetCarbonFootprintAsString(200, '200 kg CO2 bzw. 840 km mit dem Flugzeug');
    }

    public function testGetCarbonFootprintAsStringEarthSmall2()
    {
        $this->assertGetCarbonFootprintAsString(4, '4 kg CO2 bzw. 17 km mit dem Flugzeug');
    }

    private function assertGetCarbonFootprintAsString($carbonFootprintSum, $expected)
    {
        $result = $this->MyHtmlHelper->getCarbonFootprintAsString($carbonFootprintSum);
        $this->assertEquals($expected, $result);
    }
}
