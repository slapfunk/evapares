<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
        $mform->addElement('text', 'name', 'Nombre', array('size' => '64'));
        
            $mform->setType('name', PARAM_TEXT);
        
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'evaparesname', 'evapares');//lang

        // Adding the standard "intro" and "introformat" fields.
        
//      if($CFG->version > 2014111008) {
//             $this->standard_intro_elements('hola');
//         } else {
//             $this->add_intro_editor();
//         }
      
  // We now define the amount of Evaluations, Questions, Answers and time availability options respectively
        $AmountEval= 10 ; 
        $AmountQuest = 5 ;
        $AmountAns = 7 ;
        $AmountTime = 5 ; 
        //Here we fill different arrays with all the different options
	   $evaluations = array();
	   for ($i=0; $i <= $AmountEval ; $i++){ array_push($evaluations,$i) ;}
	   $questions = array();
	   for ($i=0; $i <= $AmountQuest ; $i++){ array_push($questions,$i) ;}
	   $answers = array();
	   for ($i=0; $i <= $AmountAns ; $i++){ array_push($answers,$i) ;}
	   $time = array();
	   for ($i=0; $i <= $AmountTime ; $i++){ array_push($time,$i) ;}
	   
	   //Add all the fields to be completed

        $mform->addElement('checkbox', 'ssc',get_string('addSSC', 'mod_evapares'));
        
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

