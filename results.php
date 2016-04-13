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

 * @package    mod_evapares
 * @copyright  2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once('locallib.php');

global $DB, $USER, $PAGE, $COURSE;

$cmid = required_param('cmid', PARAM_INT);
$studentid = optional_param('studentid', '-1', PARAM_INT);
$iterationid = optional_param("iterationid", "-1", PARAM_INT);

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

$PAGE->set_url('/mod/evapares/results.php', array('cmid' => $cmid, "studentid" => $studentid));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout("incourse");
$PAGE->set_cm($cm);

if(!has_capability('mod/evapares:courseevaluations', $context) && !has_capability('mod/evapares:myevaluations', $context)){
	print_error("no tiene la capacidad de estar en  esta pagina");
}

if($iterationid == "-1"){
	$sqlgetiteration = "SELECT *
			FROM {evapares_iterations}
			WHERE start_date <= ? AND start_date > ? AND evapares_id = ?";
	
	$duration = 24 * 60 * 60 * (int)$evapares->n_days;
	
	$params = array(time(), time() - $duration, $cmid);
}else{
	$sqlgetiteration = "SELECT  * 
			FROM {evapares_iterations}
			WHERE id = ?";
	
	$params = array($iterationid);
}

if( !$iteration = $DB->get_record_sql($sqlgetiteration, $params) ){
	$sqlgetiteration = "SELECT *
			FROM {evapares_iterations}
			WHERE n_iteration = ? AND evapares_id = ?";
		
	$params = array(1, $cmid);
	$iteration = $DB->get_record_sql($sqlgetiteration, $params);
}

if(has_capability('mod/evapares:myevaluations', $context)){	
	
	$PAGE->set_title(format_string($iteration->evaluation_name));
	$PAGE->set_heading(format_string($iteration->evaluation_name));
	echo $OUTPUT->header();
	
	echo $OUTPUT->tabtree(evapares_result_tabs($cmid), "Resultados");
	echo $OUTPUT->tabtree(evapares_evaluations_tabs($cmid, $studentid), $iteration->evaluation_name);
	
	$studentid = $USER->id;
}else{
	$PAGE->set_title(format_string($evapares->name));
	$PAGE->set_heading(format_string($course->fullname));
	echo $OUTPUT->header();
}

//cantidad de personas en el grupo
$groupid = groups_get_user_groups($COURSE->id, $USER->id);

$membersgroup = groups_get_members($groupid[0][0], $fields = "u.id");

$quantitymembers = count($membersgroup) -1;

$sqlevaluations = "SELECT e.id, e.ssc_stop, e.ssc_start, e.ssc_continue, e.nota
		FROM {evapares_evaluations} AS e
		WHERE e.iterations_id = ?
		AND e.alu_evaluado_id = ?
		AND e.alu_evalua_id != ?
		AND e.answers = 1";

if( !$evaluations = $DB->get_records_sql($sqlevaluations, array($iteration->id, $studentid, $studentid)) ){
	
	$url =  new moodle_url("/mod/evapares/view.php",array('id' =>$cmid));
	
	echo 'Aún no hay datos que desplegar';
	echo $OUTPUT->single_button($url, "Volver a las evaluaciones");
	echo $OUTPUT->footer();
	die();
}

$ssctable = new html_table();

$ssctable->head = array(
		"Integrantes",
		"Stop",
		"Start",
		"Continue"
);

$ssctable->size = array(
		"7%",
		"31%",
		"31%",
		"31%"
);

$ssctable->align = array(
		"left",
		"center",
		"center",
		"center"
);

$grade = 0;

$countevaluations = 1;
foreach ($evaluations as $evaluation){
	
	if($evaluation->ssc_stop == NULL || empty($evaluation->ssc_stop)){
		$stop = "-";
	}else{
		$stop = $evaluation->ssc_stop;
	}
	
	if($evaluation->ssc_start == NULL || empty($evaluation->ssc_start)){
		$start = "-";
	}else{
		$start = $evaluation->ssc_start;
	}
	
	if($evaluation->ssc_continue == NULL || empty($evaluation->ssc_continue)){
		$continue = "-";
	}else{
		$continue = $evaluation->ssc_continue;
	}
	
	
	$ssctable->data [] = array(
			$countevaluations,
			$stop,
			$start,
			$continue
	);
	
	$grade += $evaluation->nota;
	
	$countevaluations++;
}

if($countevaluations <= $quantitymembers ){
	for($count = 0; $count + $countevaluations <= $quantitymembers; $count++){
		$ssctable->data [] = array(
				($count + $countevaluations),
				"-",
				"-",
				"-"
		);
	}
}

// stop-start-continue table
echo html_writer::table($ssctable);


$sqlquestionandanswers = "SELECT q.id AS questionid, q.text AS question, a.text AS answers, a.id AS answersid
		FROM {evapares_questions} AS q
		INNER JOIN {evapares_answers} AS a ON (q.evapares_id = ? AND a.question_id = q.id)
		GROUP BY q.id";

$questions = $DB->get_records_sql($sqlquestionandanswers , array($cmid));

$sqlanswers = "SELECT a.answers_id, e.iterations_id
				FROM {evapares_eval_has_answ} AS a
				JOIN {evapares_evaluations} AS e ON (e.id = a.evaluations_id AND e.iterations_id = ?)
				WHERE e.alu_evaluado_id = ?
				ORDER BY iterations_id";

$answers = $DB->get_records_sql($sqlanswers, array($iteration->id, $studentid));




 
echo $OUTPUT->footer();