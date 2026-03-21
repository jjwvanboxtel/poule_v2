<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/users/user.class.php');
require_once('../modules/users/participant.class.php');
require_once('../modules/usergroups/usergroup.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfParticipants extends UnitTestCase {
    
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
    
    function testAddParticipant() {
        $enabled = 1;
        $email = 'poule@test.nl';
        $password = 'poule01';
        $firstName = 'testPerson';
        $lastName = 'lastName';
        $phoneNr = '8888888888';
        $userGroup = 3;
        $postalCode = '1111AA';
        $street = 'testStraat';
        $town = 'testPlaats';
        $houseNr = '1';
        $addition = 'a';
        $bankAccount = '1111111111';        

        $participantId = Participant::addp($enabled, $email, $password, $firstName, $lastName, $phoneNr, $userGroup,
                                                    $postalCode, $street, $town, $houseNr, $addition, $bankAccount);
        
        $this->assertTrue(Participant::exists($participantId));
        
        $participant = new Participant($participantId);
        $this->assertEqual($participant->getEnabled(), $enabled);
        $this->assertEqual($participant->getEmail(), $email);
        $this->assertEqual($participant->getPassword(), $password);
        $this->assertEqual($participant->getFirstName(), $firstName);
        $this->assertEqual($participant->getLastName(), $lastName);
        $this->assertEqual($participant->getPhoneNr(), $phoneNr);
        $this->assertEqual($participant->getUserGroup()->getId(), $userGroup);
        $this->assertEqual($participant->getPostalCode(), $postalCode);
        $this->assertEqual($participant->getStreet(), $street);
        $this->assertEqual($participant->getHouseNr(), $houseNr);
        $this->assertEqual($participant->getAddition(), $addition);
        $this->assertEqual($participant->getBankAccount(), $bankAccount);
        
        $participant->delete();
    }

    function testDeleteParticipant() {
        $enabled = 1;
        $email = 'poule@test.nl';
        $password = 'poule01';
        $firstName = 'testPerson';
        $lastName = 'lastName';
        $phoneNr = '8888888888';
        $userGroup = 3;
        $postalCode = '1111AA';
        $street = 'testStraat';
        $town = 'testPlaats';
        $houseNr = '1';
        $addition = 'a';
        $bankAccount = '1111111111';        

        $participantId = Participant::addp($enabled, $email, $password, $firstName, $lastName, $phoneNr, $userGroup,
                                                    $postalCode, $street, $town, $houseNr, $addition, $bankAccount);
        
        $participant = new Participant($participantId);
        $participant->delete();
        
        $this->assertFalse(Participant::exists($participantId));        
    }
    
    function testUpdateParticipant() {
        $enabled_before = 0;
        $email_before = 'poule@test.nl';
        $password_before = 'poule01';
        $firstName_before = 'testPerson1';
        $lastName_before = 'lastName1';
        $phoneNr_before = '8888888888';
        $userGroup_before = 3;
        $postalCode_before = '1111AA';
        $street_before = 'testStraat2';
        $town_before = 'testPlaats1';
        $houseNr_before = '1';
        $addition_before = 'a';
        $bankAccount_before = '1111111111';        
        $payed_before = 0;
        $subscribed_before = 0;
        $enabled_after = 1;
        $email_after = 'poule1@test.nl';
        $password_after = 'poule02';
        $firstName_after = 'testPerson2';
        $lastName_after = 'lastName2';
        $phoneNr_after = '7777777777';
        $userGroup_after = 3;
        $postalCode_after = '2222BB';
        $street_after = 'testStraat2';
        $town_after = 'testPlaats2';
        $houseNr_after = '2';
        $addition_after = 'b';
        $bankAccount_after = '2222222222'; 
        $payed_after = 1;
        $subscribed_after = 1;
        
        $participantId = Participant::addp($enabled_before, $email_before, $password_before, $firstName_before, $lastName_before, $phoneNr_before, $userGroup_before,
                                                    $postalCode_before, $street_before, $town_before, $houseNr_before, $addition_before, $bankAccount_before);
       
        
        $participant = new Participant($participantId);
        $this->assertEqual($participant->getEnabled(), $enabled_before);
        $this->assertEqual($participant->getEmail(), $email_before);
        $this->assertEqual($participant->getPassword(), $password_before);
        $this->assertEqual($participant->getFirstName(), $firstName_before);
        $this->assertEqual($participant->getLastName(), $lastName_before);
        $this->assertEqual($participant->getPhoneNr(), $phoneNr_before);
        $this->assertEqual($participant->getUserGroup()->getId(), $userGroup_before);
        $this->assertEqual($participant->getPostalCode(), $postalCode_before);
        $this->assertEqual($participant->getStreet(), $street_before);
        $this->assertEqual($participant->getTown(), $town_before);
        $this->assertEqual($participant->getHouseNr(), $houseNr_before);
        $this->assertEqual($participant->getAddition(), $addition_before);
        $this->assertEqual($participant->getBankAccount(), $bankAccount_before);
        $this->assertEqual($participant->getPayed($this->competitionId), $payed_before);
        $this->assertEqual($participant->getSubscribed($this->competitionId), $subscribed_before);

        $participant = new Participant($participantId);
        $participant->enable();
        $participant->setEmail($email_after);
        $participant->setPassword($password_after);
        $participant->setFirstName($firstName_after);
        $participant->setLastName($lastName_after);
        $participant->setPhoneNr($phoneNr_after);
        $participant->setUserGroup($userGroup_after);
        $participant->setPostalCode($postalCode_after);
        $participant->setStreet($street_after);
        $participant->setTown($town_after);
        $participant->setHouseNr($houseNr_after);
        $participant->setAddition($addition_after);
        $participant->setBankAccount($bankAccount_after);
        $participant->setPayed($this->competitionId, $payed_after);
        $participant->setSubscribed($this->competitionId, $subscribed_after);
        $participant->save();
        
        $participant = new Participant($participantId);
        $this->assertEqual($participant->getEnabled(), $enabled_after);
        $this->assertEqual($participant->getEmail(), $email_after);
        $this->assertEqual($participant->getPassword(), $password_after);
        $this->assertEqual($participant->getFirstName(), $firstName_after);
        $this->assertEqual($participant->getLastName(), $lastName_after);
        $this->assertEqual($participant->getPhoneNr(), $phoneNr_after);
        $this->assertEqual($participant->getUserGroup()->getId(), $userGroup_after);
        $this->assertEqual($participant->getPostalCode(), $postalCode_after);
        $this->assertEqual($participant->getStreet(), $street_after);
        $this->assertEqual($participant->getTown(), $town_after);
        $this->assertEqual($participant->getHouseNr(), $houseNr_after);
        $this->assertEqual($participant->getAddition(), $addition_after);
        $this->assertEqual($participant->getBankAccount(), $bankAccount_after);
        $this->assertEqual($participant->getPayed($this->competitionId), $payed_after);
        $this->assertEqual($participant->getSubscribed($this->competitionId), $subscribed_after);

        $participant->delete();        
    }
    
    function testGetAllParticipants()
    {
        $enabled = 1;
        $email = 'poule@test.nl';
        $password = 'poule01';
        $firstName = 'testPerson';
        $lastName = 'lastName';
        $phoneNr = '8888888888';
        $userGroup = 3;
        $postalCode = '1111AA';
        $street = 'testStraat';
        $town = 'testPlaats';
        $houseNr = '1';
        $addition = 'a';
        $bankAccount = '1111111111';        

        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            Participant::add($enabled, $i.$email, $password, $firstName, $lastName, $phoneNr, $userGroup,
                                                    $postalCode, $street, $town, $houseNr, $addition, $bankAccount);
        }
        
        $c = 0;
        User::getAllUsers(3);
        while (User::nextUser() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>