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
require_once('forms/editconfig_form.php');

global $DB, $OUTPUT, $PAGE, $COURSE;

require_login();

$action = optional_param("action", "view", PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);

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
if(!has_capability('mod/evapares:courseevaluations', $context) ){	
	print_error("No tiene la capacidad de estar en  esta pagina");
}

$PAGE->set_url('/mod/evapares/configuration.php', array('cmid' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout("incourse");
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($evapares->name));
$PAGE->set_heading(format_string($course->fullname));

if($action == "edit"){
	
	$formredirect = new moodle_url("configuration.php",array('action' => "view", "cmid" => $cmid));
	
	$editconfig = new evapares_editconfig(null, array("cmid" => $cmid, "evaparesid" => $cm->instance));
	
	$defaultdata = new stdClass();
	$defaultdata->name = $evapares->name;
	$defaultdata->duration = $evapares->n_days;
	
	$iterations = $DB->get_records("evapares_iterations", array("evapares_id" => $cmid));
	
	$counteriterations = 0;
	foreach ($iterations as $iteration){
		$fieldname = "i$counteriterations";
		$fielddate = "d$counteriterations";
		
		$defaultdata->$fieldname = $iteration->evaluation_name;
		$defaultdata->$fielddate = $iteration->start_date;
		
		$counteriterations++;
	}
	
	$editconfig->set_data($defaultdata);
	
	if ($editconfig->is_cancelled()) {
	
		redirect($formredirect);
	
	} else if ($data = $editconfig->get_data()) {
		
		$evapares->name = $data->name;
		$evapares->n_days = $data->duration;
		$DB->update_record("evapares", $evapares);
		
		for ($counteriterations = 0; $counteriterations < ($evapares->total_iterations + 2)  ; $counteriterations++){
			// inputs names
			$iditeration = "id$counteriterations";
			$name = "i$counteriterations";
			$date = "d$counteriterations";
			
			$iteration = $DB->get_record("evapares_iterations", array("id" => $data->$iditeration));

			$iteration->evaluation_name = $data->$name;
			$iteration->start_date = $data->$date;
			
			$DB->update_record("evapares_iterations", $iteration);

		}
		
		redirect($formredirect);
	}
}

if($action == "view"){
	$viewtable = new html_table();
	
	$viewtable->data [] = array(
			"Nombre",
			$evapares->name
	);
	
	$scc = "Deshabilitado";
	if($evapares->scc = 1){
		$scc = "Habilitado";
	}
	
	$viewtable->data [] = array(
			"Stop-Start-Continue",
			$scc
	);
	
	$viewtable->data [] = array(
			"Número de evaluaciones",
			($evapares->total_iterations + 2)
	);
	
	$viewtable->data [] = array(
			"Tiempo para evaluar",
			$evapares->n_days." días"
	);
	
	$iterations = $DB->get_records("evapares_iterations", array("evapares_id" => $cmid));
	
	$daysinseconds = 24 * 60 * 60 * (int)$evapares->n_days;
	
	foreach ($iterations as $iteration){
		$viewtable->data [] = array(
				$iteration->evaluation_name,
				"Inicio ".date("H:i - d-m-Y", $iteration->start_date)." / Fin ".date("H:i - d-m-Y", ($daysinseconds + (int)$iteration->start_date))
		);
	}
	
	$editbutton = new moodle_url("configuration.php",array('action' => "edit", "cmid" => $cmid));
	
	$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
	
}

echo $OUTPUT->header();

if($action == "edit"){
	
	echo $OUTPUT->heading("Edición de actividad evaluación de pares");
	$editconfig->display();
}

if($action == "view"){
	
	echo $OUTPUT->tabtree(evapares_edit_tabs($cmid), "Configuración");
	echo $OUTPUT->heading("Detalle actividad evaluación de pares");
	echo html_writer::table($viewtable);
	echo $OUTPUT->single_button($editbutton, "Editar");
	echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));
}

echo $OUTPUT->footer();