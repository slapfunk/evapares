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
 * Prints a particular instance of evapares
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_evapares
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once('locallib.php');
require_once('forms/evaluations_form.php');

global $DB, $OUTPUT, $PAGE, $COURSE; 

require_login();

$action = required_param("action", PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);
$evaparesid = required_param("instance", PARAM_INT);
$sesskey = required_param("sesskey", PARAM_ALPHANUM);
$iterationid = optional_param("ei","0", PARAM_INT);
$evaluationid = optional_param("ee", "0", PARAM_INT);

if(! $cm = get_coursemodule_from_id('evapares', $cmid)){
	print_error('cm'." id: $cmid");
}

if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance))){
	print_error('evapares'." id: $cmid");
}

$context = context_module::instance($cm->id);

if( !has_capability('mod/evapares:myevaluations', $context) ){
	print_error(get_string('permission','mod_evapares'));
}

$PAGE->set_url('/mod/evapares/evaluations.php', array(
		"action" => $action,
		"cmid" => $cmid,
		"instance" => $evaparesid,
		"sesskey" => $sesskey,
		"ei" => $iterationid,
		"ee" => $evaluationid
));
$PAGE->set_context($context);
$PAGE->set_course($COURSE);
$PAGE->set_pagelayout("incourse");
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($evapares->name));
$PAGE->set_heading(format_string($COURSE->fullname));

$evaluationname = $DB->get_record("evapares_iterations", array("id" => $iterationid));

$backtoevapares = new moodle_url("/mod/evapares/view.php", array('id'=>$cmid));

if($action == "initial"){
	
	$initialform = new evapares_initialevaluation(null, array(
			"cmid" => $cmid,
			"action" => $action,
			"cmid" => $cmid,
			"instance" => $evaparesid,
			"sesskey" => $sesskey,
			"ei" => $iterationid,
			"ee" => $evaluationid
	));
	
	if ($initialform->is_cancelled()) {

		redirect($backtoevapares);
		
    } else if ($data = $initialform->get_data()) {
    	$records = array();
    	
    	$counter = 1;
    	foreach($data as $field => $value){
    		if($field == "a$counter"){
    			$record = new stdClass();
    			$record->evaluations_id = $evaluationid;
    			$record->answers_id = $value;
    			$record->iterationid = $iterationid;
    			$records[] = $record;
    		}
    		$counter++;
    	}

        $DB->insert_records("evapares_eval_has_answ", $records);
       
        $evaluation = $DB->get_record("evapares_evaluations", array("id" => $evaluationid));
        $evaluation->answers = 1;
        
        $DB->update_record("evapares_evaluations",$evaluation);
        
		redirect($backtoevapares);
    }
}

if($action == "iteration" || $action == "last"){
	$iterationform = new evapares_iterationform(null, array(
			"cmid" => $cmid,
			"action" => $action,
			"cmid" => $cmid,
			"instance" => $evaparesid,
			"sesskey" => $sesskey,
			"ei" => $iterationid
	));
	
	if ($iterationform->is_cancelled()) {

		redirect($backtoevapares);
	
	} else if ($data = $iterationform->get_data()){
		$counter = 1;
		//var_dump($data);
		$records = array();
		
		foreach($data as $field => $value){

			//echo "field ".$field." value ".$value."<br>";
			if($field == "start$counter"){
				$aux = 1;
				
				$eva = new stdClass();
				$eva->scc_start = $value;
			}
			
			if($field == "stop$counter"){
				$eva->scc_stop = $value;
			}
			
			if($field == "continue$counter"){
				$eva->scc_continue = $value;
			}
			
			if($field == "n$counter"){
				$eva->nota = (float)$value;
			}
			
			if($field == "a*$counter*$aux"){
				$record = new stdClass();
				$record->answers_id = $value;
				$record->iterationid = $iterationid;
				
			}
			
			if($field == "ee*$counter*$aux"){
				$record->evaluations_id = $value;
				$record->iterationid = $iterationid;
				$records[] = $record;
				
				if($aux == $evapares->n_preguntas){
 				
	 				$evaluation = $DB->get_record_sql("SELECT * FROM {evapares_evaluations} WHERE id = ?", array($value));
	 				
	 				//var_dump($eva);
	 				
	 				$evaluation->ssc_start = $eva->scc_start;
	 				$evaluation->ssc_stop = $eva->scc_stop = $value;
	 				$evaluation->ssc_continue = $eva->scc_continue = $value;
	 				$evaluation->nota = $eva->nota;
	 				$evaluation->enddate = time();
					$evaluation->answers = 1;
	 				
					//echo "<br><br>";
					//var_dump($evaluation);
					$DB->update_record("evapares_evaluations", $evaluation);
					$counter++;
				}
				
				$aux++;				
			}
			
			
		}
		//echo "<br><br>";
		//var_dump($records);
		
		$DB->insert_records("evapares_eval_has_answ", $records);
		
		redirect($backtoevapares);
	}
}


echo $OUTPUT->header();
echo $OUTPUT->heading($evaluationname->evaluation_name);

if($action == "initial"){
	$initialform->display();
}

if($action == "iteration" || $action == "last"){
	$iterationform->display();
}

echo $OUTPUT->footer();