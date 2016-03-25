<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright Hans Jeria (hansjeria@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config.php");
require_once ($CFG->libdir . "/formslib.php");


class evapares_num_eval_form extends moodleform {
	
	function definition() {
		
		global $DB;
		
	$mform = $this->_form;
	$instance = $this->_customdata;		
	
// amount of iterations
		$num = $instance['num'];
		$cmid =$instance['cmid'];
		
//amount of questions		
		$preg =$instance['preg'];
		
//amount of answers
		$resp =$instance['resp'];

		$mform->addElement('header', 'Detalle_Entregas', get_string('DeliverableDetails','mod_evapares'));

// creates the respective form for each iteration
		for($i = 0; $i <= $num + 1; $i++){

// initial evaluation
			if($i == 0){

		$mform->addElement('hidden', 'NE'.$i, get_string('initialEval','mod_evapares'));
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('personalEvalInitial','mod_evapares'));
		$mform->setDefault('available', 0);

// intermediates evaluations
			} elseif($i > 0 && $i < $num + 1){

		$mform->addElement('text', 'NE'.$i,get_string('DeliverableName', 'mod_evapares').$i);
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('dueDate','mod_evapares').$i);
		$mform->setDefault('available', 0);

// final evaluation
			} elseif($i == $num +1){

		$mform->addElement('hidden', 'NE'.$i, get_string('finalEval','mod_evapares'));
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('finalDate', 'mod_evapares'));
		$mform->setDefault('available', 0);
		
			}
		}
		
		$mform->addElement('header', 'Detalle_Preguntas', get_string('AddMultipleOptionQuestion','mod_evapares'));

// creates the questions with the respective answers
		for($j = 1; $j <= $preg; $j++){

// questions
			$mform->addElement('textarea', "P$j",get_string('question','mod_evapares').$j, 'wrap="virtual" rows="5" cols="60"');
			$mform->setType("P$j", PARAM_TEXT);
		
// answers	
			for($h = 1; $h <= $resp; $h++){
				$idm = "R$j$h";
				
				$mform->addElement('text',$idm, get_string('option','mod_evapares').$j.'.'.$h);
				$mform->setType($idm, PARAM_TEXT);
			}
		}
		
		$mform->addElement('hidden', 'id',$cmid);
		$mform->setType('id', PARAM_INT);
		
		$this->add_action_buttons();
	}
	
// validates thet the fields are properly filled
	function validation($data, $files){
		global $DB;
		
		$instance = $this->_customdata;
		
		$num = $instance['num'];
		$preg =$instance['preg'];
		$resp =$instance['resp'];
		
		$actualdate = time();
		
		$errors = array();
		 
		for($i = 0; $i <= $num + 1; $i++){
			$name = $data['NE'.$i];
			$date = $data['FE'.$i];
			
			if( empty($name)){
				$errors['NE'.$i] = get_string('addName','mod_evapares');
			}
// 			if( $date < $actualdate) {
// 				$errors['FE'.$i] = get_string('ChooseDate','mod_evapares');
// 			}
			if( $i >= 1){
				$j = $i - 1;
				if( $data['FE'.$i] <= $data['FE'.$j]){					
				$errors['FE'.$i] = 'las fechas de entregas deben ir en orden cronologico';
				}
			}
		for($j = 1; $j <= $preg; $j++){
			$question = $data['P'.$j];
			
			if( empty($question)){
				$errors['P'.$j] = get_string('AddQuestion','mod_evapares');
			}
			if( strlen($question) > 200){
				$errors['P'.$j] = 'la cantidad de caracteres no debe exeder los 200';
			}
				for($h = 1; $h <= $resp; $h++){
					$answer = $data['R'.$j.$h];
					
					if( empty($answer)){
						$errors['R'.$j.$h] = get_string('addAnswer','mod_evapares');
					}
					if( strlen($answer) > 200){
						$errors['R'.$j.$h] = 'la cantidad de caracteres no debe exeder los 200';
					}
			}
		}
		}

		return $errors;
	}
}
