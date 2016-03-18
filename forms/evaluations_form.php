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
		
		$initialquestions = $DB->get_recordset_select("evapares_questions", " evapares_id = ?", array($cmid));
		
		foreach($initialquestions as $question){
			
			$answers = $DB->get_recordset_select("evapares_answers", " question_id = ?", array($question->id));
			
			$answersarray = array();
			$answersarray["0*0"] = "Seleccione una alternativa";
			foreach($answers as $answer){
				$answersarray[$question->id."*".$answer->id] = $answer->text;
			}
			
			$mform->addElement("select", $question->id."*".$answer->id ,$question->text, $answersarray);
		}
		
		
		$this->add_action_buttons(true);
	}
	
	public function validation($data, $files) {
		global $DB;
		
		$errors = array();
		
		//comprobar que se selecciono algo en los select
		
		return $erros;
	}
}

class evapares_iterationevaluation extends moodleform {

	public function definition() {

	}
	
	public function validation($data, $files) {
		
	}
}

class evapares_finalevaluation extends moodleform {

	public function definition() {

	}
	
	public function validation($data, $files) {
	
	}
}

