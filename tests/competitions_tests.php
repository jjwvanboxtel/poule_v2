<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/competitions/competition.class.php');
require_once('../modules/users/user.class.php');
require_once('../modules/sections/section.class.php');
require_once('../modules/scorings/scoring.class.php');

class TestOfCompetitions extends UnitTestCase {
    
    function setUp() {
        App::clearAll();
    }

    function tearDown()
    {
        App::clearAll();
    }
    
    function testAddCompetition() {
        $name = 'nameCompetition1';
        $description = 'descriptionCompetition1';
        $header['name'] = 'headerCompetition1.png';
        $submission_date = time();
        $money = 5;
        $first_place = 50;
        $second_place = 25;
        $third_place = 20;
        
        $competitionId = Competition::add($name, $description, $header, $submission_date, $money, $first_place, $second_place, $third_place);
        
        $this->assertTrue(Competition::exists($competitionId));
        
        $competition = new Competition($competitionId);
        $this->assertEqual($competition->getName(), $name);
        $this->assertEqual($competition->getDescription(), $description);
        $this->assertEqual($competition->getImage(), $header['name']);        
        $this->assertEqual($competition->getFinalSubmissionDate(), $submission_date);
        $this->assertEqual($competition->getMoney(), $money);
        $this->assertEqual($competition->getFirstPlace(), $first_place);
        $this->assertEqual($competition->getSecondPlace(), $second_place);
        $this->assertEqual($competition->getThirdPlace(), $third_place);
        
        User::getAllUsers(3);
        while (($user = User::nextUser()) != null)
        {
            $record = App::$_DB->doSQL('SELECT count( * ) AS total
                            FROM `participant_competition`
                            WHERE `Participant_User_user_id` = ' . $user->user_id . '
                            AND `Competition_competition_id` = ' . $competitionId);
                            
            $this->assertTrue((boolean)App::$_DB->getRecord($record)->total);
        }
            
        Section::getAllSections();
        while (($section = Section::nextSection()) != null)
        {   
            $record = App::$_DB->doSQL('SELECT count( * ) AS total
                            FROM `section_competition`
                            WHERE `Section_section_id` = ' . $section->section_id . '
                            AND `Competition_competition_id` = ' . $competitionId);
                            
            $this->assertTrue((boolean)App::$_DB->getRecord($record)->total);
        }
            
        Scoring::getAllScorings();
        while (($scoring = Scoring::nextScoring()) != null)
        {  
            $record = App::$_DB->doSQL('SELECT count( * ) AS total
                            FROM `scoring_competition`
                            WHERE `Scoring_scoring_id` = ' . $scoring->scoring_id . '
                            AND `Competition_competition_id` = ' . $competitionId);
                            
            $this->assertTrue((boolean)App::$_DB->getRecord($record)->total);
        }
        
        $competition->delete();
    }

    function testUpdateCompetition() {
        $before_name = 'testCompetition1';
        $before_description = 'descriptionCompetition1';
        $before_header['name'] = 'headerCompetition1.png';
        $before_submission_date = time();
        $before_money = 5;
        $before_first_place = 50;
        $before_second_place = 25;
        $before_third_place = 20;

        $after_name = 'testCompetition2';
        $after_description = 'descriptionCompetition2';
        $after_header['name'] = 'headerCompetition2.png';
        $after_submission_date = $before_submission_date + (24*60*60);
        $after_money = 6;
        $after_first_place = 51;
        $after_second_place = 26;
        $after_third_place = 21;
        
        $competitionId = Competition::add($before_name, $before_description, $before_header, $before_submission_date, $before_money, $before_first_place, $before_second_place, $before_third_place);
        
        $competition = new Competition($competitionId);
        $this->assertEqual($competition->getName(), $before_name);
        $this->assertEqual($competition->getDescription(), $before_description);
        $this->assertEqual($competition->getImage(), $before_header['name']);
        $this->assertEqual($competition->getMoney(), $before_money);
        $this->assertEqual($competition->getFinalSubmissionDate(), $before_submission_date);
        $this->assertEqual($competition->getFirstPlace(), $before_first_place);
        $this->assertEqual($competition->getSecondPlace(), $before_second_place);
        $this->assertEqual($competition->getThirdPlace(), $before_third_place);

        $competition = new Competition($competitionId);
        $competition->setName($after_name);
        $competition->setDescription($after_description);
        $competition->setImage($after_header);        
        $competition->setFinalSubmissionDate($after_submission_date);
        $competition->setMoney($after_money);
        $competition->setFirstPlace($after_first_place);
        $competition->setSecondPlace($after_second_place);
        $competition->setThirdPlace($after_third_place);
        $competition->save();
        
        $competition = new Competition($competitionId);
        $this->assertEqual($competition->getName(), $after_name);
        $this->assertEqual($competition->getDescription(), $after_description);
        $this->assertEqual($competition->getImage(), $after_header['name']);
        $this->assertEqual($competition->getFinalSubmissionDate(), $after_submission_date);
        $this->assertEqual($competition->getMoney(), $after_money);
        $this->assertEqual($competition->getFirstPlace(), $after_first_place);
        $this->assertEqual($competition->getSecondPlace(), $after_second_place);
        $this->assertEqual($competition->getThirdPlace(), $after_third_place);

        $competition->delete();        
    }
    
    function testDeleteCompetition() {
        $name = 'testCompetition1';
        $description = 'descriptionCompetition1';
        $header['name'] = 'headerCompetition1.png';
        $money = 5;
        $first_place = 50;
        $second_place = 25;
        $third_place = 20;
        
        $competitionId = Competition::add($name, $description, $header, $money, $first_place, time(), $second_place, $third_place);
        
        $competition = new Competition($competitionId);
        $competition->delete();
        
        $this->assertFalse(Competition::exists($competitionId));        
    }
    
    function testGetAllCompetitions()
    {
        $header['name'] = 'headerCompetition1.png';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            Competition::add('testCompetition1', 'descriptionCompetition1', $header, time(), 5, 50, 25, 20);
        }
        
        $c = 0;
        Competition::getAllCompetitions();
        while (Competition::nextCompetition() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
    function testCheckSubmissionDateExpired()
    {
        $header['name'] = 'headerCompetition1.png';
        $competitionId = Competition::add('testCompetition1', 'descriptionCompetition1', $header, 
            DateTime::createFromFormat('d-m-Y', '18-11-1988')->getTimestamp(), 5, 50, 25, 20);

        $this->assertTrue(Competition::checkSubmissionDateExpired($competitionId, time()));
    }
}
?>