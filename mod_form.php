<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_evapares_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE;
        $mform = $this->_form;

// Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

// Adding the standard "name" field.
        $mform->addElement('text', 'name',get_string('formName','mod_evapares'), array('size' => '64'));
        
            $mform->setType('name', PARAM_TEXT);
        
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'evaparesname', 'evapares');//lang

 
// Defines the amount of Evaluations, Questions, Answers and time availability options respectively
        $AmountEval= 10; 
        $AmountQuest = 20;
        $AmountAns = 7;
        $AmountTime = 14;
        
// Fills different arrays with all the different options
	   $evaluations = array();
	   for ($i=0; $i <= $AmountEval ; $i++){ $evaluations[$i] = $i;}
	   $questions = array();
	   for ($i=1; $i <= $AmountQuest ; $i++){ $questions[$i] = $i;}
	   $answers = array();
	   for ($i=2; $i <= $AmountAns ; $i++){ $answers[$i] = $i;}
	   $time = array();
	   for ($i=1; $i <= $AmountTime ; $i++){ $time[$i] = $i;}
	   
//Add all the fields to be completed
        $mform->addElement('checkbox', 'ssc', get_string('addSSC', 'mod_evapares'));
        $mform->addHelpButton('ssc', 'ssc', 'mod_evapares');
        
        $mform->addElement('checkbox', 'default', 'Default evaluation');
        
        $mform->addElement('select', 'total_iterations',get_string('amountOfEvaluations','mod_evapares'), $evaluations);
        
        $mform->addElement('select', 'n_preguntas',get_string('amountOfQuestions','mod_evapares'), $questions);
        
        $mform->addElement('select', 'n_respuestas',get_string('amountOfAnswers','mod_evapares'), $answers);
        
        $mform->addElement('select', 'n_days',get_string('disponibilityTime','mod_evapares'), $time);
       
        $mform->addElement('hidden', 'course_id',$COURSE->id);
        $mform->setType('course_id', PARAM_INT);
        

                

// Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();


// Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

}

