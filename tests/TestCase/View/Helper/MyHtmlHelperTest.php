<?php
use App\View\Helper\MyHtmlHelper;
use Cake\View\View;

class MyHtmlHelperTest extends \PHPUnit\Framework\TestCase
{

    public function setUp(): void
    {
        $this->MyHtmlHelper = new MyHtmlHelper(new View());
    }
    
    public function testGetCarbonFootprintAsStringSun()
    {
        $this->assertGetCarbonFootprintAsString(910000000, '910.000 t CO2 bzw. 4.252.336.449 km mit dem Flugzeug, entspricht ca. 28,5x der mittleren Entfernung zur Sonne');
    }

    public function testGetCarbonFootprintAsStringSun2()
    {
        $this->assertGetCarbonFootprintAsString(349532710.3, '349.533 t CO2 bzw. 1.633.330.422 km mit dem Flugzeug, entspricht ca. 11x der mittleren Entfernung zur Sonne');
    }
    
    public function testGetCarbonFootprintAsStringSun3()
    {
        $this->assertGetCarbonFootprintAsString(8800000, '8.800 t CO2 bzw. 41.121.495 km mit dem Flugzeug, entspricht ca. 108x der Entfernung zum Mond');
    }
    
    public function testGetCarbonFootprintAsStringMars2()
    {
        $this->assertGetCarbonFootprintAsString(4400000, '4.400 t CO2 bzw. 20.560.748 km mit dem Flugzeug, entspricht ca. 54x der Entfernung zum Mond');
    }
    
    public function testGetCarbonFootprintAsStringMars3()
    {
        $this->assertGetCarbonFootprintAsString(1580000, '1.580 t CO2 bzw. 7.383.178 km mit dem Flugzeug, entspricht ca. 19,5x der Entfernung zum Mond');
    }
    
    public function testGetCarbonFootprintAsStringMoon()
    {
        $this->assertGetCarbonFootprintAsString(200000, '200 t CO2 bzw. 934.579 km mit dem Flugzeug, entspricht ca. 23,5x um den Äquator');
    }
    
    public function testGetCarbonFootprintAsStringEarth()
    {
        $this->assertGetCarbonFootprintAsString(52000, '52 t CO2 bzw. 242.991 km mit dem Flugzeug, entspricht ca. 6x um den Äquator');
    }
    
    public function testGetCarbonFootprintAsStringEarthSmall()
    {
        $this->assertGetCarbonFootprintAsString(200, '200 kg CO2 bzw. 935 km mit dem Flugzeug');
    }
    
    public function testGetCarbonFootprintAsStringEarthSmall2()
    {
        $this->assertGetCarbonFootprintAsString(4, '4 kg CO2 bzw. 19 km mit dem Flugzeug');
    }
    
    private function assertGetCarbonFootprintAsString($carbonFootprintSum, $expected)
    {
        $result = $this->MyHtmlHelper->getCarbonFootprintAsString($carbonFootprintSum);
        $this->assertEquals($expected, $result);
    }
}
