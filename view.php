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
 * @copyright  2016 Benjamin Espinosa (beespinosa@gmail.com)
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('locallib.php');
require_once('forms/view_form.php');

global $CFG, $DB, $OUTPUT, $PAGE;

require_login();

$action = optional_param("action", "view", PARAM_TEXT);
$cmid = required_param('id', PARAM_INT);
$mode = optional_param("mode", "evaluation", PARAM_TEXT);

if(! $cm = get_coursemodule_from_id('evapares', $cmid)){
	print_error('cm'." id: $cmid");
}

if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance))){
	print_error('evapares'." id: $cmid");
}

if(! $course = $DB->get_record('course', array('id' => $cm->course))){
	print_error('course'." id: $cmid");
}

$context = context_module::instance($cm->id);

// Print the page header.
if(!has_capability('mod/evapares:courseevaluations', $context) && !has_capability('mod/evapares:myevaluations', $context))
{	
	print_error("No tiene la capacidad de estar en  esta pagina");
}

$PAGE->set_url('/mod/evapares/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout("incourse");
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($evapares->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

if(!$grupos = $DB->get_records("groups", array('courseid'=>$course->id))){
	
	echo get_string('no_groups','mod_evapares');
	
	$creategroupurl =  new moodle_url("/group/index.php",array('id' => $COURSE->id));
	echo $OUTPUT->single_button($creategroupurl, get_string('create_groups','mod_evapares'));

	echo $OUTPUT->footer();
	die();
}

if(!$evapares_iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid))){
	$action = "add";
}
	
$vars = array('num'=>$evapares->total_iterations,
		"cmid"=>$cmid, 
		'preg'=>$evapares->n_preguntas, 
		'resp'=>$evapares->n_respuestas
);
	
if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){
	
	$addform = new evapares_num_eval_form(null, $vars);

	$alliterations = array();
	$allquestions = array();
	$allanswers = array();
	$allcombs = array();
	
	if( $addform->is_cancelled() ){
		$backtocourse = new moodle_url("/course/view.php",array('id'=>$course->id));
		redirect($backtocourse);
		
	}
	else if($datas = $addform->get_data()){
		
		for($i = 0; $i <= $evapares->total_iterations + 1; $i++ ){
			
			//delivery date id
			$idfe = "FE$i";
			//delivery name id
			$idne = "NE$i";
			
			$record = new stdClass();
			
			$record->n_iteration = $i;
			$record->start_date = $datas->$idfe;
			$record->evapares_id = (int)$cm->id;
			$record->evaluation_name = $datas->$idne;
			
			$alliterations[]=$record;
		}
		
		$DB->insert_records("evapares_iterations", $alliterations);
		
		$sql = 'SELECT GM1.userid AS a_evalua, GM2.userid AS a_evaluado, EI.id As id_iteration, EI.n_iteration AS n_iteration
			    FROM mdl_groups_members AS GM1, mdl_groups_members AS GM2, mdl_evapares_iterations AS EI
				WHERE GM1.groupid = GM2.groupid AND EI.evapares_id = ?';
		
		$consulta = $DB-> get_recordset_sql($sql ,array($cm->id));
		
		foreach($consulta as $insert){
			if($insert->a_evalua == $insert->a_evaluado && $insert->n_iteration == 0 || $insert->n_iteration == $evapares->total_iterations +1){
		
				$rec = new stdClass();
		
				$rec->ssc_stop = null;
				$rec->ssc_start = null;
				$rec->ssc_continue = null;
				$rec->answers = '0';
				$rec->alu_evalua_id = $insert->a_evalua;
				$rec->alu_evaluado_id = $insert->a_evaluado;
				$rec->iterations_id = $insert->id_iteration;
		
				$allcombs[] = $rec;
		
			}elseif($insert->a_evalua != $insert->a_evaluado && $insert->n_iteration > 0){
		
				$rec = new stdClass();
		
				$rec->ssc_stop = null;
				$rec->ssc_start = null;
				$rec->ssc_continue = null;
				$rec->answers = '0';
				$rec->alu_evalua_id = $insert->a_evalua;
				$rec->alu_evaluado_id = $insert->a_evaluado;
				$rec->iterations_id = $insert->id_iteration;
		
				$allcombs[] = $rec;
		
			}
		}
		
		$DB->insert_records("evapares_evaluations", $allcombs);
		
		
		for($i = 1; $i <= $evapares->n_preguntas; $i++ ){
			
			//questions id
			$idp = "P$i";
							
			$recp = new stdClass();	
			
			$recp->n_of_question = $i;
			$recp->evapares_id = (int)$cm->id;
			$recp->text = $datas->$idp;
			
			$DB->insert_record("evapares_questions", $recp);
			
			$questionid = $DB->get_records("evapares_questions", array('evapares_id'=>$cmid));
 			
 			foreach($questionid as $key => $value){
 			$llaves[$i] = $key;
 			}
			
			for($j = 1; $j <= $evapares->n_respuestas; $j++ ){
				$idr = "R$i$j";

				$recr = new stdClass();
			
				$recr->number = $j;
				$recr->question_id = $questionid[$llaves[$i]]->id;
				$recr->text = $datas->$idr;
			
				$allanswers[]=$recr;		
			}

			$DB->insert_records("evapares_answers", $allanswers);
			unset($allanswers);
			
		}	
			$action = "view";
	}
}

if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){

	$addform->display();

}elseif(has_capability('mod/evapares:courseevaluations', $context) && $action == "view"){

	//include('teacher.php');
	evapares_get_teacherview($cm->id, $evapares);

}elseif(has_capability('mod/evapares:myevaluations', $context) && $action == "view"){
	//Vista alumnos

	$tabs[] = array(
			new tabobject(
				'tb1',
				new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'evaluation','id' => $cm->id)), 
				get_string('eval','mod_evapares')
			),
			new tabobject(
				'tb2',
				new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'results','id' => $cm->id)),
				get_string('results','mod_evapares')
			)
	);
	
	if($mode == 'evaluation'){
		// TABS
		$currenttab='tb1';
		$activated = array('tb1');
		print_tabs($tabs, $currenttab, $activated);
		
		evapares_get_evaluations($cm->id, $cm->instance);
		
	}else if($mode == "results"){
		// TABS
		$currenttab='tb2';
		$activated = array('tb2');
		print_tabs($tabs, $currenttab, $activated);
		
		include('results_tab.php');
	}
	
}

echo $OUTPUT->footer();		
		
