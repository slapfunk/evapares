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

		$mform->addElement('header', 'Detalle_Entregas', 'Detalle de Entregas');

		for($i = 0; $i <= $num + 1; $i++){
		
			if($i == 0){
				
		$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Inicial');
		$mform->setType('NE'.$i, PARAM_TEXT);
		$mform->addElement('date_time_selector', 'FE'.$i,'Evaluacion Personal Inicial', array('optional'=>true));
		$mform->setDefault('available', 0);
			} elseif($i > 0 && $i < $num + 1){
			
		$mform->addElement('text', 'NE'.$i,'Nombre Entrega '.$i);
		$mform->setType('NE'.$i, PARAM_TEXT);
		$mform->addElement('date_time_selector', 'FE'.$i,'Fecha Entrega '.$i, array('optional'=>true));
		$mform->setDefault('available', 0);
			} elseif($i == $num +1){
				
		$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Final');
		$mform->setType('NE'.$i, PARAM_TEXT);
		$mform->addElement('date_time_selector', 'FE'.$i,'Evaluacion Final', array('optional'=>true));
		$mform->setDefault('available', 0);
			}
		}
		
		$mform->addElement('header', 'Detalle_Preguntas', 'Detalle de Preguntas y Respuestas');
		
		for($j = 1; $j <= 3; $j++){
		
			$mform->addElement('textarea', 'P'.$j,'Pregunta '.$j, 'wrap="virtual" rows="5" cols="60"');
			$mform->setType('P'.$j, PARAM_TEXT);
		
				
			for($h = 1; $h <= 3; $h++){
				$mform->addElement('text', $j.'.'.$h,'Opcion '.$j.'.'.$h);
				$mform->setType($j.'.'.$h, PARAM_TEXT);
			}
		}
		
		$mform->addElement('hidden', 'id',$cmid);
		$mform->setType('id', PARAM_INT);
		
		$this->add_action_buttons();
	}
	
}
