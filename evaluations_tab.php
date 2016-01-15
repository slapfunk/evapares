<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('/forms/forms_alu.php');

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
else{
	$vars=array('num'=>$evapares->total_iterations,"cmid"=>$cmid,"iduser"=>$iduser);//iduser hay que saber de donde
	$addform = new evapares_evalu_usua(null,$vars);//editars
	echo $OUTPUT->header();
	$addform->display();
	echo $OUTPUT->footer();

}