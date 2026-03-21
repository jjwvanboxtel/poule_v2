<?php
require_once('./simpletest/autorun.php');
require_once('./mock/appMock.php');
require_once('../modules/forms/form.class.php');
require_once('../modules/competitions/competition.class.php');

class TestOfForms extends UnitTestCase {
    
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
    
    function testAddForm() {
        $name = 'testForm1';
        $file['name'] = 'testForm1.doc';
        Form::add($name, $file, $this->competitionId);
        
        $formId = App::$_DB->getLastId();
        $this->assertTrue(Form::exists($formId));
        
        $form = new Form($formId);
        $this->assertEqual($form->getName(), $name);
        
        $form->delete();
    }

    function testDeleteForm() {
        $name = 'testForm1';
        $file['name'] = 'testForm1.doc';
        Form::add($name, $file, $this->competitionId);
        
        $formId = App::$_DB->getLastId();
        
        $form = new Form($formId);
        $form->delete();
        
        $this->assertFalse(Form::exists($formId));        
    }
    
    function testUpdateForm() {
        $nameBeforeUpdate = 'testForm1';
        $nameAfterUpdate = 'testForm2';
        $fileBeforeUpdate['name'] = 'testForm1.doc';
        $fileAfterUpdate['name'] = 'testForm2.doc';

        Form::add($nameBeforeUpdate, $fileBeforeUpdate, $this->competitionId);
        
        $formId = App::$_DB->getLastId();        
        
        $form = new Form($formId);
        $this->assertEqual($form->getName(), $nameBeforeUpdate);
        $this->assertEqual($form->getFile(), $fileBeforeUpdate['name']);

        $form = new Form($formId);
        $form->setName($nameAfterUpdate);
        $form->setFile($fileAfterUpdate);
        $form->save();
        
        $form = new Form($formId);
        $this->assertEqual($form->getName(), $nameAfterUpdate);
        $this->assertEqual($form->getFile(), $fileAfterUpdate['name']);
        $form->delete();        
    }
    
    function testGetAllForms()
    {
        $file['name'] = 'testForm1.doc';
        
        $count = 5;
        for ($i=0; $i<$count; $i++)
        {
            Form::add('testForm1', $file, $this->competitionId);
        }
        
        $c = 0;
        Form::getAllForms($this->competitionId);
        while (Form::nextForm() != null)
        {
            $c++;
        }
        $this->assertEqual($count, $c);        
    }
    
}
?>