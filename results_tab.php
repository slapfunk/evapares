<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

global $CFG, $DB, $OUTPUT, $USER;

$iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid));

$resultados = $DB->get_records("evapares_evaluations", array('alu_evaluado_id'=>$USER->id),'iterations_id ASC');

$query = "SELECT Q.text AS preg, Q.id AS pregid, A.text AS resp, A.id AS ansid
		  FROM mdl_evapares_questions AS Q, mdl_evapares_answers AS A
		  WHERE Q.evapares_id = ? AND Q.id = A.question_id";

$percentages = "SELECT Answer.`evaluations_id`, Answer.`answers_id`, COUNT(Answer.`answers_id`) AS num, Evaluation.`iterations_id`
				FROM mdl_evapares_evlalutons_has_answ Answer
				JOIN mdl_evapares_evaluations Evaluation ON (Evaluation.id = Answer.evaluations_id )
				JOIN mdl_evapares_iterations Iteration ON (Evaluation.`iterations_id` = Iteration.`id`)
				WHERE Iteration.evapares_id = 164
				GROUP BY answers_id
				ORDER BY answers_id";

$get_pers = $DB-> get_recordset_sql($percentages ,array($cm->id));

foreach($get_pers as $data){
var_dump($data);}

$headings = array('Stop','Start','Continue');

$n_table = 0;

foreach($resultados as $param){
	
 	if($param->alu_evalua_id != $param->alu_evaluado_id){
 		
 		if($param->iterations_id != $n_table){
 			if($n_table != 0){
 				
 				$table->data = $supa_data_sama;
 				//echo $param->iterations_id - 1;
 				echo '<strong>'.$iterations[$param->iterations_id - 1]->evaluation_name.'</strong><br>'; 				
 				//COMPROBAR CON FECHA
 				echo html_writer::table($table);

 				$cons = $DB-> get_recordset_sql($query ,array($cm->id));
 					
 				$tempid = 0;
 				foreach($cons as $p_a){

 					if($p_a->pregid != $tempid){
 						echo '<table>
 							  <tr><td><strong>'.$p_a->preg.'</strong></td></tr>';
 						$tempid = $p_a->pregid;
 					}
 						echo '<tr><td></td><td>'.$p_a->resp.'</td></tr>';
 				}
 						echo '</table><hr>';
			}
 				
 			$table = new html_table();
 			$table->head = $headings ;
 			$supa_data_sama=array();
 			$data_chan=array();
 			
 			array_push($data_chan,$param->ssc_stop);
 			array_push($data_chan,$param->ssc_start);
 			array_push($data_chan,$param->ssc_continue);
 			array_push($supa_data_sama,$data_chan);
 			
 			$data_chan=array();
 			
 			$n_table = $param->iterations_id;
 			
 		}else{
 			
 			array_push($data_chan,$param->ssc_stop);
 			array_push($data_chan,$param->ssc_start);
 			array_push($data_chan,$param->ssc_continue);
 			array_push($supa_data_sama,$data_chan);
 			
 			$data_chan=array();
 			
 		}
 	
	}
	
}
$table->data = $supa_data_sama;
echo '<strong>'.$iterations[$param->iterations_id]->evaluation_name.'</strong><br>';
echo html_writer::table($table);
$cons = $DB-> get_recordset_sql($query ,array($cm->id));

$tempid = 0;
foreach($cons as $p_a){

	if($p_a->pregid != $tempid){
		echo '<strong>'.$p_a->preg.'</strong><br>';
		$tempid = $p_a->pregid;
	}
	echo $p_a->resp.'<br>';
}




