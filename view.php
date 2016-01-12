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
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('/forms/forms_v.php');

global $CFG, $DB, $OUTPUT; 

$action = optional_param("action", "view", PARAM_TEXT);
$cmid = required_param('id', PARAM_INT); 

if(! $cm = get_coursemodule_from_id('evapares', $cmid))
{print_error('cm'." id: $cmid");}

if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance)))
{print_error('evapares'." id: $cmid");}

if(! $course = $DB->get_record('course', array('id' => $cm->course)))
{print_error('course'." id: $cmid");}
$context = context_module::instance($cm->id);

require_login();

// Print the page header.
if(!has_capability('mod/evapares:courseevaluations', $context) && !has_capability('mod/evapares:myevaluations', $context))
{	
	print_error("no tiene la capacidad de estar en  esta pagina");
}

	$PAGE->set_url('/mod/evapares/view.php', array('id' => $cm->id));
	$PAGE->set_context($context);
	$PAGE->set_course($course);
	$PAGE->set_pagelayout("incourse");
	$PAGE->set_cm($cm);
	$PAGE->set_title(format_string($evapares->name));
	$PAGE->set_heading(format_string($course->fullname));
	
	if(!$DB->get_records("evapares_iterations", array('evapares_id'=>$cmid)))
	{$action = "add";}
	
	if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){
	$addform = new evapares_num_eval_form(null,array('num'=>$evapares->total_iterations,"cmid"=>$cmid));

	$allquestions = array();
	
	if( $addform->is_cancelled() ){
		$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
		redirect($backtocourse);
		
	}else if($datas = $addform->get_data()){
		
		for($i = 0; $i <= $evapares->total_iterations + 1; $i++ ){
			$idfe = "FE$i";
			$idne = "NE$i";
			
			$record = new stdClass();
			
			$record->n_iteration = $i;
			$record->start_date = $datas->$idfe;
			$record->evapares_id = (int)$cm->id;
			$record->evaluation_name = $datas->$idne;
			
			$allquestions[]=$record;
		}
		
		 $DB->insert_records("evapares_iterations", $allquestions);
			$action = "view";

	}
}

if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){
	echo $OUTPUT->header();

	$addform->display();

}
elseif(has_capability('mod/evapares:courseevaluations', $context) && $action == "view"){
	echo $OUTPUT->header();
	echo"holi, usted es profe";
}

elseif(has_capability('mod/evapares:myevaluations', $context) && $action == "view"){
	echo $OUTPUT->header();
	echo"holi, usted es alumno";
}
echo $OUTPUT->footer();
	
// 	else if(has_capability('mod/evapares:myevaluations', $context)){		
		
// 	}
	

