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
 * Internal library of functions for module evapares
*
* All the evapares specific functions, needed to implement the module
* logic, should go here. Never include this file from your lib.php!
*
* @package    mod_evapares
* @copyright  2016 Hans Jeria (hansjeria@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config.php");
require_once ($CFG->libdir . "/formslib.php");

class evapares_editconfig extends moodleform {

	public function definition() {
		global $DB;

		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$cmid = $instance["cmid"];
		
		
		$mform->addElement("text", "name", "Nombre de la actividad");
		$mform->setType( "name", PARAM_TEXT);
		
		$mform->addElement("text", "duration", "Duración de las iteraciones");
		$mform->setType( "duration", PARAM_INT);
		
		$iterations = $DB->get_records("evapares_iterations", array("evapares_id" => $cmid));
		
		$counteriterations = 0;
		foreach ($iterations as $iteration){
			$fieldname = "i$counteriterations";
			$fielddate = "d$counteriterations";
			
			$mform->addElement ( 'header', "header$fieldname", "Evaluación ".($counteriterations+1), null, false);
			
			$mform->addElement("hidden", "id$counteriterations", $iteration->id);
			$mform->setType( "id$counteriterations", PARAM_INT);
		
			$mform->addElement("text", $fieldname, "Nombre de la iteración ".$counteriterations);
			$mform->setType( $fieldname, PARAM_TEXT);
			
			$mform->addElement("date_time_selector", $fielddate, "Fecha inicio iteración ".$counteriterations);
		
			$counteriterations++;
			$mform->setExpanded("header$fieldname", true);
		}
		
		$mform->addElement("hidden", "action", "edit");
		$mform->setType( "action", PARAM_TEXT);
		
		$mform->addElement("hidden", "cmid", $cmid);
		$mform->setType( "cmid", PARAM_INT);
		
		$this->add_action_buttons(true);
		
	}
	public function validation($data, $files) {
		global $DB;
		
		$instance = $this->_customdata;
			
		$errors = array();

		$evaparesid = $instance["evaparesid"];
		
		$evapares = $DB->get_record('evapares', array('id' => $evaparesid));
		
		$durationinseconds =  24 * 60 * 60 * (int)$data["duration"];
		
		$counteriterations = 0;
		for ($counteriterations = 0; $counteriterations <= $evapares->total_iterations ;$counteriterations++){
			$fielddate = "d$counteriterations";
			$next = $counteriterations +1;
				
			if($data["d$next"]){
				if( ((int)$data[$fielddate] +$durationinseconds) > (int)$data["d$next"]){
					$errors[$fielddate] = "Las fechas deben ir en orden cronologico";
					$errors["d$next"] = "Las fechas deben ir en orden cronologico";
				}
			}
		}
		
		return $errors;
	}
}