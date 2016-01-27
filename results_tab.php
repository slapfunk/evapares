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

$PAGE->requires->jquery ();
$PAGE->requires->jquery_plugin ( 'ui' );
$PAGE->requires->jquery_plugin ( 'ui-css' );

$iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid));

$resultados = $DB->get_records("evapares_evaluations", array('alu_evaluado_id'=>$USER->id),'iterations_id ASC');

$query = "SELECT Q.text AS preg, Q.id AS pregid, A.text AS resp, A.id AS ansid
		  FROM mdl_evapares_questions AS Q, mdl_evapares_answers AS A
		  WHERE Q.evapares_id = ? AND Q.id = A.question_id";

$percentages = "SELECT Answer.`answers_id`, Evaluation.`iterations_id`
				FROM mdl_evapares_eval_has_answ Answer
				JOIN mdl_evapares_evaluations Evaluation ON (Evaluation.id = Answer.evaluations_id )
				JOIN mdl_evapares_iterations Iteration ON (Evaluation.`iterations_id` = Iteration.`id`)
				WHERE Iteration.evapares_id = ?
				ORDER BY iterations_id";

$get_pers = $DB-> get_recordset_sql($percentages ,array($cm->id));

$n_group_members = "SELECT COUNT(groups.groupid) AS n_members
					FROM mdl_groups_members AS groups
					WHERE groups.groupid = 
				   (SELECT groups.groupid
					FROM mdl_groups_members AS groups
					WHERE groups.userid = ?)";

$n_memb = $DB->get_recordset_sql($n_group_members, array($USER->id));

foreach($n_memb as $quant){
	$efective_members = $quant->n_members;
}


foreach($get_pers as $data){
	$percent[] = $data;
}

$count_plc = count($percent) - 1;


$headings = array('Stop','Start','Continue');

$n_table = 0;

foreach($resultados as $param){
	
 	if($param->alu_evalua_id != $param->alu_evaluado_id){
 		
 		if($param->iterations_id != $n_table){
 			if($n_table != 0){
 				
 				$table->data = $supa_data_sama;
 				
 				echo '<div class="panel-group" id="accordion" role="tablist"
 						aria-multiselectable="true">
 						<div class="panel panel-default">
 						<div role="tab" id="'.$iterations[$param->iterations_id - 1]->evaluation_name.'">
 						<h3 class="panel-title">
 						<a class="list-group-item " role="button" data-toggle="collapse"
 								data-parent="#accordion" href="#'.$iterations[$param->iterations_id - 1]->evaluation_name.'"
 										aria-controls="'.$iterations[$param->iterations_id - 1]->evaluation_name.'">
 												'.$iterations[$param->iterations_id - 1]->evaluation_name.'
 										</a>
 										</h3>
 										</div>';
 											
 				//echo '<strong>'.$iterations[$param->iterations_id - 1]->evaluation_name.'</strong><br>'; 				
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
 						echo '<tr><td></td><td>'.$p_a->resp.'</td>
 								  <td>';
 						$temp = 0;
 						for($cont = 0; $cont <= $count_plc; $cont++){

 							if($param->iterations_id -1 == $percent[$cont]->iterations_id &&
 							   $percent[$cont]->answers_id == $p_a->ansid){
 							   	$temp = $temp + 1;
 							} 													
 						}
 						$perc_display = $temp * 100 / ($efective_members -1);
 						echo '<strong>'.$perc_display.'%</strong>';
 						echo'</td></tr>';
 				}
 						echo '</table><hr>';
 						echo'</ul>
					</div>
				</div>
			</div>
 						';
			}
			echo '<div id="'.$iterations[$param->iterations_id - 1]->evaluation_name.'" class="panel-collapse collapse in"
					role="tabpanel" aria-labelledby="'.$iterations[$param->iterations_id - 1]->evaluation_name.'">
					<div>
					<ul class="list-group">';
						
			
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
 		echo '<table>
 			  <tr><td><strong>'.$p_a->preg.'</strong></td></tr>';
 		$tempid = $p_a->pregid;
	}
 		echo '<tr><td></td><td>'.$p_a->resp.'</td><td>';
 		$temp = 0;
 		for($cont = 0; $cont <= $count_plc; $cont++){
 			if($param->iterations_id == $percent[$cont]->iterations_id &&
 					$percent[$cont]->answers_id == $p_a->ansid){

 						$temp = $temp + 1;
 			}
 		}
 		$perc_display = $temp * 100 / ($efective_members -1);
 		echo '<strong>'.$perc_display.'%</strong>';
 		echo'</td></tr>';
 }
 		echo '</table><hr>';


