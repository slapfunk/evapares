<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

global $CFG, $DB, $USER, $PAGE;

$cmid = required_param('cmid', PARAM_INT);
$sid = optional_param('studentid', '-1', PARAM_INT);

if(! $cm = get_coursemodule_from_id('evapares', $cmid))
{print_error('cm'." id: $cmid");}
if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance)))
{print_error('evapares'." id: $cmid");}
if(! $course = $DB->get_record('course', array('id' => $cm->course)))
{print_error('course'." id: $cmid");}

$context = context_module::instance($cm->id);

echo '<script src="../evapares/js/jquery.js"></script>
<script src="../evapares/js/controladorbotonbuscar.js"></script>';
// Print the page header.
if(!has_capability('mod/evapares:courseevaluations', $context) && !has_capability('mod/evapares:myevaluations', $context))
{
	print_error("no tiene la capacidad de estar en  esta pagina");
}
if(has_capability('mod/evapares:myevaluations', $context)){
	
	$studentid = $USER->id;
}elseif(has_capability('mod/evapares:courseevaluations', $context)){
	
	$studentid = $sid;
}

	$PAGE->set_url('/mod/evapares/view.php', array('id' => $cm->id));
	$PAGE->set_context($context);
	$PAGE->set_course($course);
	$PAGE->set_pagelayout("incourse");
	$PAGE->set_cm($cm);
	$PAGE->set_title(format_string($evapares->name));
	$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->js (new moodle_url('/mod/evapares/js/accordion.js') );

?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css">
<?php 

$iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid));

$resultquery =  'SELECT * FROM {evapares_evaluations} AS eval
 				 INNER JOIN {evapares_iterations} AS iter 
 				 ON eval.iterations_id = iter.id 
 				 WHERE iter.evapares_id = ? 
 				 AND eval.alu_evaluado_id = ?
 				 ORDER BY iterations_id ASC';

$resultados = $DB-> get_recordset_sql($resultquery, array($cm->id, $studentid));

$query = "SELECT Q.text AS preg, Q.id AS pregid, A.text AS resp, A.id AS ansid
		  FROM {evapares_questions} AS Q, {evapares_answers} AS A
		  WHERE Q.evapares_id = ? AND Q.id = A.question_id";

$percentages = "SELECT Answer.`answers_id`, Evaluation.`iterations_id`
				FROM {evapares_eval_has_answ} Answer
				JOIN {evapares_evaluations} Evaluation ON (Evaluation.id = Answer.evaluations_id )
				JOIN {evapares_iterations} Iteration ON (Evaluation.`iterations_id` = Iteration.`id`)
				WHERE Iteration.evapares_id = ?
				AND Evaluation.alu_evaluado_id = ?
				ORDER BY iterations_id";

$get_pers = $DB-> get_recordset_sql($percentages, array($cm->id, $studentid));

$n_group_members = "SELECT COUNT(gmn.groupid) AS n_members
					FROM mdl_groups_members AS gmn
					WHERE gmn.groupid =
					(SELECT gm.groupid AS gid
					 FROM mdl_groups_members AS gm
					 INNER JOIN mdl_groups AS g ON gm.groupid = g.id
					 INNER JOIN mdl_course_modules AS cm ON cm.course = g.courseid
					 WHERE gm.userid = ?
					 AND cm.id = ?)";

$n_memb = $DB->get_recordset_sql($n_group_members, array($studentid, $cmid));

foreach($n_memb as $quantity){
	$members = $quantity->n_members;
}
$n_memb->close();

foreach($get_pers as $data){
	$percent[] = $data;
}
$get_pers->close();

$count_plc = count($percent) - 1;
	

$headings = array('Stop','Start','Continue');

// number that verifies the table associated with each iteration
$n_table = 0;

echo $OUTPUT->header();

echo'<div class="accordion">';

foreach($resultados as $param){

// verifies that the state is not self-assessment	
 	if($param->alu_evalua_id != $param->alu_evaluado_id){
 		
// check table associated with the iteration		
 		if($param->iterations_id != $n_table){
 			
// verified that this isn't the first iteration 			
 			if($n_table != 0){

 				$table->data = $tabledata;
 				
 				echo '<h3>'.$iterations[$param->iterations_id - 1]->evaluation_name.'</h3>';
 				echo'<div>';
 				//COMPROBAR CON FECHA
// displays the table created in a previous loop
 				echo html_writer::table($table);

 				$cons = $DB-> get_recordset_sql($query ,array($cm->id));
 				
// number used to verify the ID of the answers 					
 				$tempid = 0;
 				echo'<table>';
 				foreach($cons as $p_a){

// verify the ID of the question with the actual state
 					if($p_a->pregid != $tempid){
 						echo '<tr><td><strong>'.$p_a->preg.'</strong></td></tr>';
 						$tempid = $p_a->pregid;
 					}
 						echo '<tr><td></td><td>'.$p_a->resp.'</td>
 								  <td>';
 						
// count how many times an alternative was chosen
 						$temp = 0;
 						for($cont = 0; $cont <= $count_plc; $cont++){							
 							if($param->iterations_id -1 == $percent[$cont]->iterations_id &&
 							   $percent[$cont]->answers_id == $p_a->ansid){
 							   	$temp = $temp + 1;
 							} 													
 						}
// calculates in percentages how many times an alternative was chosen
 						$perc_display = $temp * 100 / ($members -1);
 						echo '<strong>'.$perc_display.'%</strong>';
 						echo'</td></tr>';
 				}
 						echo '</table>';
 						echo '</div>';
			}
	
// creates the SSC table that will be displayed in the next loop
 			$table = new html_table();
 			$table->head = $headings ;
 			$tabledata=array();
 			$tablerow=array();
 			
 			array_push($tablerow,$param->ssc_stop);
 			array_push($tablerow,$param->ssc_start);
 			array_push($tablerow,$param->ssc_continue);
 			array_push($tabledata,$tablerow);
 			
 			$tablerow=array();
 			
 			$n_table = $param->iterations_id;
 			
 		}else{
 			
// refills the SSC table that will be displayed in the next loop
 			array_push($tablerow,$param->ssc_stop);
 			array_push($tablerow,$param->ssc_start);
 			array_push($tablerow,$param->ssc_continue);
 			array_push($tabledata,$tablerow);
 			
 			$tablerow=array();
 			
 		}
 	
	}
	
}
$resultados->close();

$table->data = $tabledata;
echo '<h3>'.$iterations[$param->iterations_id]->evaluation_name.'</h3>';
echo '<div>';

// displays the table created in the last loop
echo html_writer::table($table);
$cons = $DB-> get_recordset_sql($query ,array($cm->id));

// number used to verify the ID of the answers
$tempid = 0;
foreach($cons as $p_a){

// verify the ID of the question with the actual state
	if($p_a->pregid != $tempid){
 		echo '<table>
 			  <tr><td><strong>'.$p_a->preg.'</strong></td></tr>';
 		$tempid = $p_a->pregid;
	}
 		echo '<tr><td></td><td>'.$p_a->resp.'</td><td>';
 		
// count how many times an alternative was chosen
 		$temp = 0;
 		for($cont = 0; $cont <= $count_plc; $cont++){
 			if($param->iterations_id == $percent[$cont]->iterations_id &&
 					$percent[$cont]->answers_id == $p_a->ansid){

 						$temp = $temp + 1;
 			}
 		}
 		
// calculates in percentages how many times an alternative was chosen
 		$perc_display = $temp * 100 / ($members -1);
 		echo '<strong>'.$perc_display.'%</strong>';
 		echo'</td></tr>';
 }
 		echo '</table>
			  </div>
  		      </div>';
 		
 		echo $OUTPUT->footer();
