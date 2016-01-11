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

$cmid = required_param('id', PARAM_INT); 

$cm         = get_coursemodule_from_id('evapares', $cmid);
$course     = $DB->get_record('course', array('id' => $cm->course));
$evapares  = $DB->get_record('evapares', array('id' => $cm->instance));
//comprobar con if

$context = context_module::instance($cm->id);

require_login();

// Print the page header.
if(!has_capability('mod/evapares:courseevaluations', $context)&&!has_capability('mod/evapares:myevaluations', $context))
{	
	print_error("no tiene la capacidad de estar en  esta página");
}
else{
	$PAGE->set_url('/mod/evapares/view.php', array('id' => $cm->id));
	$PAGE->set_context($context);
	$PAGE->set_course($course);
	$PAGE->set_pagelayout("incourse");
	$PAGE->set_cm($cm);
	$PAGE->set_title(format_string($evapares->name));
	$PAGE->set_heading(format_string($course->fullname));
	
	
	echo $OUTPUT->header();
	if(has_capability('mod/evapares:courseevaluations', $context)){
		$mform=new evapares_num_eval_form(null, array("evapares"=>$evapares));
		$mform->display();
		
		$mform=new evapares_detalle_preguntas;
		$mform->display();
	}
	else if(has_capability('mod/evapares:myevaluations', $context)){
		
	}
	
	
	
	
	echo $OUTPUT->footer();
	
}
