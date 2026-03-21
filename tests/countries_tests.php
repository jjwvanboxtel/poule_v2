<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/countries/country.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfCountries extends UnitTestCase {
    
    private $competitionId = 0;
    
    function setUp() {
        App::clearAll();
        
        $header['name'] = 'headerCompetition1.png';
        $this->competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddCountry() {
        $name = 'testCountry1';
        $image['name'] = 'testCountry1.png';
        Country::add($name, $image, $this->competitionId);
        
        $countryId = App::$_DB->getLastId();
        $this->assertTrue(Country::exists($countryId));
        
        $country = new Country($countryId);
        $this->assertEqual($country->getName(), $name);
        
        $country->delete();
    }

    function testDeleteCountry() {
        $name = 'testCountry1';
        $image['name'] = 'testCountry1.png';
        Country::add($name, $image, $this->competitionId);
        
        $countryId = App::$_DB->getLastId();
        
        $country = new Country($countryId);
        $country->delete();
        
        $this->assertFalse(Country::exists($countryId));        
    }
    
    function testUpdateCountry() {
        $nameBeforeUpdate = 'testCountry1';
        $nameAfterUpdate = 'testCountry2';
        $imageBeforeUpdate['name'] = 'testCountry1.png';
        $imageAfterUpdate['name'] = 'testCountry2.png';

        Country::add($nameBeforeUpdate, $imageBeforeUpdate, $this->competitionId);
        
        $countryId = App::$_DB->getLastId();        
        
        $country = new Country($countryId);
        $this->assertEqual($country->getName(), $nameBeforeUpdate);
        $this->assertEqual($country->getImage(), $imageBeforeUpdate['name']);

        $country = new Country($countryId);
        $country->setName($nameAfterUpdate);
        $country->setImage($imageAfterUpdate);
        $country->save();
        
        $country = new Country($countryId);
        $this->assertEqual($country->getName(), $nameAfterUpdate);
        $this->assertEqual($country->getImage(), $imageAfterUpdate['name']);
        $country->delete();        
    }
    
    function testGetAllCountries()
    {
        $image['name'] = 'testCountry1.png';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            Country::add('testCountry1', $image, $this->competitionId);
        }
        
        $c = 0;
        Country::getAllCountries($this->competitionId);
        while (Country::nextCountry() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>