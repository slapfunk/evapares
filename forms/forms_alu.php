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


		//aki
		$tabz = array();
		$tabz[]=new tabobject('tabs',$CFG->wwwroot.'/mod/evapares/evaluations_tab','tab1');
		$tabz[]=new tabobject('tabs',$CFG->wwwroot.'/mod/evapares/results_tab','tab2');
		//

		
	class evapares_evaluaciones_usuario extends moodleform {
	
		function definition() {
	
			 		global $DB;
			 		$instance = $this->_customdata;//ver bse
			 			
			 		$nei = $instance['evapares']->total_iterations;//$nei sera el numero de entregas intermedias
			$mform = $this->_form;
	
			$mform->addElement('header', 'entrega_inicial', 'Evaluaci�n Inicial');
		
			$mform->addElement('html', '<h5><center>Evaluacion Personal</center></h5>');
			$mform->addElement('html', '<hr>');//aqui va un texto traducible
			//ev personal
			for($j = 1; $j <= $nei; $j++){
				
				//$ev_j es el valor del nombre de la entrega intermedia j
				$mform->addElement('header', 'entrega_intermedia_'.$j, $ev_j);
	
				$mform->addElement('textarea', 'P'.$j,'Pregunta '.$j, 'wrap="virtual" rows="5" cols="60"');
				$mform->setType('P'.$j, PARAM_TEXT);
	
					
				for($h = 1; $h <= 3; $h++){
					$mform->addElement('text', $j.'.'.$h,'Opcion '.$j.'.'.$h);
					$mform->setType($j.'.'.$h, PARAM_TEXT);
				}
			}
		}
	
	}