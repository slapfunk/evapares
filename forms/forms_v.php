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

		$num = $instance['num'];
		$cmid =$instance['cmid'];
		$preg =$instance['preg'];
		$resp =$instance['resp'];

		$mform->addElement('header', 'Detalle_Entregas', get_string('DeliverableDetails','mod_evapares'));

		for($i = 0; $i <= $num + 1; $i++){
		
			if($i == 0){
				
		$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Inicial');
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('personalEvalInitial','mod_evapares'));
		$mform->setDefault('available', 0);

			} elseif($i > 0 && $i < $num + 1){
			
		$mform->addElement('text', 'NE'.$i,get_string('DeliverableName', 'mod_evapares').$i);
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('dueDate','mod_evapares').$i);
		$mform->setDefault('available', 0);

			} elseif($i == $num +1){
				
		$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Final');
		$mform->setType('NE'.$i, PARAM_TEXT);

		$mform->addElement('date_time_selector', 'FE'.$i,get_string('finalDate', 'mod_evapares'));
		$mform->setDefault('available', 0);
		
			}
		}
		
		$mform->addElement('header', 'Detalle_Preguntas', get_string('AddMultipleOptionQuestion','mod_evapares'));
		
		for($j = 1; $j <= $preg; $j++){
		
			$mform->addElement('textarea', "P$j",get_string('question','mod_evapares').$j, 'wrap="virtual" rows="5" cols="60"');
			$mform->setType("P$j", PARAM_TEXT);
		
				
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
	function validation($data, $files){
		global $DB;
		
		$instance = $this->_customdata;
		
		$num = $instance['num'];
		$preg =$instance['preg'];
		$resp =$instance['resp'];
		
		$date = getdate();
		
		$errors = array();
		 
		for($i = 0; $i <= $num + 1; $i++){
			$name = $data['NE'.$i];
			$date = $data['FE'.$i];
			
			if( empty($data['NE'.$i])){
				$errors['NE'.$i] = get_string('addName','mod_evapares');
			}
			if( $data['FE'.$i] <= $date[0]) {
				$errors['FE'.$i] = get_string('ChooseDate','mod_evapares');
			}
		for($j = 1; $j <= $preg; $j++){
			$question = $data['P'.$j];
			
			if( empty($data['P'.$j])){
				$errors['P'.$j] = get_string('AddQuestion','mod_evapares');
			}
				for($h = 1; $h <= $resp; $h++){
					$answer = $data['R'.$j.$h];
					
					if( empty($data['R'.$j.$h])){
						$errors['R'.$j.$h] = get_string('addAnswer','mod_evapares');
					}
			}
		}
		}

		return $errors;
	}
}
