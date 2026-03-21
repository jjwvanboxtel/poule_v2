<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/cities/city.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfCities extends UnitTestCase {
    
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
    
    function testAddCity() {
        $name = 'testCity1';
        $cityId = City::add($name, $this->competitionId);

        $this->assertTrue(City::exists($cityId));
        
        $city = new City($cityId);
        $this->assertEqual($city->getName(), $name);
        
        $city->delete();
    }

    function testDeleteCity() {
        $name = 'testCity1';
        $cityId = City::add($name, $this->competitionId);
                
        $city = new City($cityId);
        $city->delete();
        
        $this->assertFalse(City::exists($cityId));        
    }
    
    function testUpdateCity() {
        $nameBeforeUpdate = 'testCity1';
        $nameAfterUpdate = 'testCity2';
        $cityId = City::add($nameBeforeUpdate, $this->competitionId);
                
        $city = new City($cityId);
        $this->assertEqual($city->getName(), $nameBeforeUpdate);

        $city = new City($cityId);
        $city->setName($nameAfterUpdate);
        $city->save();
        
        $city = new City($cityId);
        $this->assertEqual($city->getName(), $nameAfterUpdate);
        $city->delete();        
    }
    
    function testGetAllCities()
    {
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            City::add('testCity1', $this->competitionId);
        }
        
        $c = 0;
        City::getAllCities($this->competitionId);
        while (City::nextCity() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>