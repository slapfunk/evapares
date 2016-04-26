<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_evapares_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
       global $COURSE;
       $mform = $this->_form;

       $mform->addElement('header', 'general', get_string('general', 'form'));

       $mform->addElement('text', 'name',get_string('formName','mod_evapares'), array('size' => '64'));
       $mform->setType('name', PARAM_TEXT);       
       $mform->addRule('name', null, 'required', null, 'client');
       $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
       $mform->addHelpButton('name', 'evaparesname', 'evapares');//lang

 
	   // Defines the amount of Evaluations and time availability options respectively
       $AmountEval= 10; 
       $AmountTime = 14;

	   // Fills different arrays with all the different options
	   $evaluations = array('-1' => 'Seleccione');
	   for ($i=0; $i <= $AmountEval ; $i++){
			$evaluations[$i] = $i;
	   }

	   $time = array();
	   for ($i=1; $i <= $AmountTime ; $i++){
	   		$time[$i] = $i;
	   }
	  
		//Add all the fields to be completed		
	   $mform->addElement('select', 'total_iterations',get_string('amountOfEvaluations','mod_evapares'), $evaluations);
	   $mform->addElement('select', 'n_days',get_string('disponibilityTime','mod_evapares'), $time);
	   
       $mform->addElement('hidden', 'ssc', '1');
       $mform->setType('ssc', PARAM_INT);

       $mform->addElement('hidden', 'n_preguntas', '-1');
       $mform->setType('n_preguntas', PARAM_INT);
        
       $mform->addElement('hidden', 'n_respuestas', '-1');
       $mform->setType('n_respuestas', PARAM_INT);
        
       $mform->addElement('hidden', 'course_id', $COURSE->id);
       $mform->setType('course_id', PARAM_INT);

       $this->standard_coursemodule_elements();
       $this->add_action_buttons();
    }

    function validation($data, $files){   	
    	
    	$errors = array();
    	
    	$iterations = $data['total_iterations'];

    	if($iterations == -1){
    		$errors['total_iterations'] = 'Debe escoger la cantidad de evaluaciones que se realizaran';
    	}
    	    	
    	return $errors;
    	   	
    }    
}

