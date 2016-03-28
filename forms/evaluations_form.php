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

class evapares_initialevaluation extends moodleform {
	
	public function definition() {
		global $DB;
		
		$mform = $this->_form;	
		$instance = $this->_customdata;
		
		$cmid = $instance["cmid"];
		$action = $instance["action"];
		$evaparesid = $instance["instance"];
		$iterationid = $instance["ei"];
		$evaluationid = $instance["ee"];
		
		$initialquestions = $DB->get_records("evapares_questions", array("evapares_id" => $cmid));
		
		$counter = 1;		
		foreach($initialquestions as $question){
			
			$answers = $DB->get_records("evapares_answers", array("question_id" => $question->id));
			
			$answersarray = array();
			$answersarray["0*0"] = "Seleccione una alternativa";
			foreach($answers as $answer){
				$answersarray[$answer->id] = $answer->text;
			}
			
			$mform->addElement("select", "a$counter" ,$question->text, $answersarray);
			$counter++;
		}
		
		$mform->addElement("hidden", "action", $action);
		$mform->setType( "action", PARAM_TEXT);
		
		$mform->addElement("hidden", "cmid", $cmid);
		$mform->setType( "cmid", PARAM_INT);
		
		$mform->addElement("hidden", "instance", $evaparesid);
		$mform->setType( "instance", PARAM_INT);
		
		$mform->addElement("hidden", "sesskey", sesskey());
		$mform->setType( "sesskey", PARAM_ALPHANUM);
		
		$mform->addElement("hidden", "ei", $iterationid);	
		$mform->setType( "ei", PARAM_INT);
		
		$mform->addElement("hidden", "ee", $evaluationid);
		$mform->setType( "ee", PARAM_INT);		
		
		$this->add_action_buttons(true);
	}
	
	public function validation($data, $files) {
		global $DB;
		
		$errors = array();
		
		$cmid = $data["cmid"];
		
		$initialquestions = $DB->get_records("evapares_questions", array("evapares_id" => $cmid));
		
		$counter = 1;
		foreach($initialquestions as $question){
			
			if($data["a$counter"] == "0*0"){
				$errors["a$counter"] = "Debe seleccionar una alternativa.";
			}

			$counter++;
		}	
		
		return $errors;
	}
}

class evapares_iterationform extends moodleform {

	public function definition() {
		global $DB, $USER;
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$cmid = $instance["cmid"];
		$action = $instance["action"];
		$evaparesid = $instance["instance"];
		$iterationid = $instance["ei"];
		
		$grades = array();
		$grades["0"] = "Seleccione una alternativa";
		for ($grade = 1; $grade <= 7; $grade += 0.5) {
			$grades["$grade"] = $grade;
		}
		
		$sql = "SELECT ee.id, ee.alu_evaluado_id, CONCAT(u.firstname, ' ', u.lastname) AS username
				FROM {evapares_evaluations} AS ee JOIN {user} AS u ON (ee.alu_evaluado_id = u.id)
				JOIN {evapares_iterations} AS ei ON (ei.id = ee.iterations_id and ei.id = ? and ei.evapares_id = ?)
				WHERE ee.alu_evalua_id = ?
				GROUP BY u.lastname, u.firstname";
		$evaluations = $DB->get_records_sql($sql, array($iterationid, $cmid, $USER->id));
		
		$counter = 1;
		foreach($evaluations as $evaluation){
			
			$mform->addElement ( 'header', "name$counter", $evaluation->username, null, false);
			
			if($evaluation->alu_evaluado_id == $USER->id){
				
				$mform->addElement("hidden", "start$counter", "START");
				$mform->setType( "start$counter", PARAM_TEXT);
				
				$mform->addElement("hidden", "stop$counter", "STOP");
				$mform->setType( "stop$counter", PARAM_TEXT);
				
				$mform->addElement("hidden", "continue$counter", "CONTINUE");
				$mform->setType( "continue$counter", PARAM_TEXT);
				
			}else{
				
				$mform->addElement("textarea", "start$counter", "START");
				$mform->setType( "star$counter", PARAM_TEXT);
				
				$mform->addElement("textarea", "stop$counter", "STOP");
				$mform->setType( "stop$counter", PARAM_TEXT);
				
				$mform->addElement("textarea", "continue$counter", "CONTINUE");
				$mform->setType( "continue$counter", PARAM_TEXT);
				
				$mform->addElement("select", "n$counter" , "Nota", $grades);
			}
				
			$questions = $DB->get_records("evapares_questions", array("evapares_id" => $cmid));
			$aux = 1;
			foreach($questions as $question){
				
				$answers = $DB->get_records("evapares_answers", array("question_id" => $question->id));
				
				$answersarray = array();
				$answersarray["0*0"] = "Seleccione una alternativa";
				foreach($answers as $answer){
					$answersarray[$answer->id] = $answer->text;
				}
				
				$mform->addElement("select", "a*$counter*$aux" ,$question->text, $answersarray);
				$mform->addElement("hidden", "ee*$counter*$aux", $evaluation->id);
				$mform->setType( "ee*$counter*$aux", PARAM_INT);
				$aux++;
			}
			
			if($counter == 1){
				$mform->setExpanded("name$counter", true);
			}else{
				$mform->setExpanded("name$counter", false);
			}
			
			$counter++;
		}
		
		$mform->addElement("hidden", "action", $action);
		$mform->setType( "action", PARAM_TEXT);
		
		$mform->addElement("hidden", "cmid", $cmid);
		$mform->setType( "cmid", PARAM_INT);
		
		$mform->addElement("hidden", "instance", $evaparesid);
		$mform->setType( "instance", PARAM_INT);
		
		$mform->addElement("hidden", "ei", $iterationid);
		$mform->setType( "ei", PARAM_INT);
		
		$mform->addElement("hidden", "sesskey", sesskey());
		$mform->setType( "sesskey", PARAM_ALPHANUM);
		
		$this->add_action_buttons(true);
	}
	
	public function validation($data, $files) {
		global $DB, $USER;
		
		$errors = array();

		$cmid = $data["cmid"];
		$iterationid = $data["ei"];
		
		$sql = "SELECT ee.id, ee.alu_evaluado_id, CONCAT(u.firstname, ' ', u.lastname) AS username
				FROM {evapares_evaluations} AS ee JOIN {user} AS u ON (ee.alu_evaluado_id = u.id)
				WHERE ee.iterations_id = ? AND ee.alu_evalua_id = ?
				GROUP BY u.lastname, u.firstname";
		$evaluations = $DB->get_records_sql($sql, array($iterationid, $USER->id));
		
		$counter = 1;
		foreach($evaluations as $evaluation){
				
			if($evaluation->alu_evaluado_id != $USER->id){
		
				if( empty($data["start$counter"]) || !isset($data["start$counter"]) || $data["start$counter"] == NULL ){
					$errors["start$counter"] = "Debe obligatoriamente escribir en este campo.";
				}
				
				if( empty($data["stop$counter"]) || !isset($data["stop$counter"]) || $data["stop$counter"] == NULL ){
					$errors["stop$counter"] = "Debe obligatoriamente escribir en este campo.";
				}
				
				if( empty($data["continue$counter"]) || !isset($data["continue$counter"]) || $data["continue$counter"] == NULL ){
					$errors["continue$counter"] = "Debe obligatoriamente escribir en este campo.";
				}
				
				if( $data["n$counter"] == 0 ){
					$errors["n$counter"] = "Debe seleccionar una nota.";
				}
			}
		
			$questions = $DB->get_records("evapares_questions", array("evapares_id" => $cmid));
			$aux = 1;
			foreach($questions as $question){

				if($data["a*$counter*$aux"] == "0*0"){
					$errors["a*$counter*$aux"] = "Debe seleccionar una alternativa.";
				}
				
				$aux++;
			}
				
			$counter++;
		}
		
		return $errors;
	}
}
