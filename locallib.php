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
 * Internal library of functions for module evapares
 *
 * All the evapares specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_evapares
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

function evapares_get_evaluations($cmid, $evaparesid){
	global $DB, $OUTPUT, $USER, $COURSE;
	
	$evaluationstable = new html_table();
	$evaluationstable->size = array(
			"28%",
			"12%",
			"12%",
			"12%",
			"12%",
			"12%"
	);
	$evaluationstable->align = array(
			"left",
			"center",
			"center",
			"center",
			"center",
			"center"
	);
	$evaluationstable->head = array(
			get_string('evals','mod_evapares'), 
			get_string('CompleteTable','mod_evapares'), 
			"Disponible", 
			"Fecha de inicio",
			"Fecha termino",
			get_string('evaluateTable','mod_evapares')
	);	

	$evapares = $DB->get_record("evapares", array("id" => $evaparesid));
	
	$daysinseconds = 24 * 60 * 60 * (int)$evapares->n_days;
	
	$iterations = array();
	for($count = 0;  $count <= (1 + $evapares->total_iterations); $count++){
		$iterations[] = $count;
	}
	
	list($sqlin, $param) = $DB->get_in_or_equal($iterations);
	
	$evaparesiterationssql = "SELECT ee.id, ee.alu_evalua_id, ee.alu_evaluado_id, ei.n_iteration, SUM(ee.answers) as answers, 
			ei.evapares_id, ei.start_date, ei.evaluation_name, ei.id as eiid
			FROM {evapares_evaluations} AS ee JOIN {evapares_iterations} AS ei 
			ON (ee.iterations_id = ei.id AND ei.n_iteration $sqlin AND ei.evapares_id = ? AND ee.alu_evalua_id = ?)
			GROUP BY ei.n_iteration";

	
	$params = array_merge($param, array($cmid, $USER->id));
	
	$evaparesiterations = $DB->get_records_sql($evaparesiterationssql, $params);
	
	foreach($evaparesiterations as $iteration){
		
		$actionicon = $OUTPUT->action_icon(
						new moodle_url("#"),
						new pix_icon("i/show", "No disponible")
				);
		
		if( ($iteration->start_date <= time()) && ($daysinseconds + (int)$iteration->start_date >= time()) ){
			
			$statusicon = new pix_icon("i/grade_correct", "si");
			
			if( $iteration->answers == 0){
				// No completada la evaluacion
				$drafticon = new pix_icon("i/grade_incorrect", "No entregado");
				
				
				if($iteration->n_iteration == 0){
					$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
							"action" => "initial",
							"cmid" => $cmid,
							"instance" => $evaparesid,
							"sesskey" => sesskey(),
							"ei" => $iteration->eiid,
							"ee" => $iteration->id
					));
					
				}else if( $iteration->n_iteration == ($count-1) ){
					$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
							"action" => "last",
							"cmid" => $cmid,
							"instance" => $evaparesid,
							"sesskey" => sesskey(),
							"ei" => $iteration->eiid,
							"ee" => $iteration->id
					));
					
				}else{
					$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
							"action" => "iteration",
							"cmid" => $cmid,
							"instance" => $evaparesid,
							"sesskey" => sesskey(),
							"ei" => $iteration->eiid,
							"ee" => $iteration->id
					));
					
				}
				
				$actionicon = $OUTPUT->action_icon(
						$actionurl,
						new pix_icon("i/manual_item", "confirmar"),
						new confirm_action("La evaluación solo se puede realizar una sola ves, una ves enviada no se puede modificar.")
				);
				
			}else{
				$drafticon = new pix_icon("i/grade_correct", "Entregado");
			}
			
		}else{
			
			$statusicon = new pix_icon("i/grade_incorrect", "");
			
			if( $iteration->answers == 0){
				// No completada la evaluacion
				$drafticon = new pix_icon("i/grade_incorrect", "No entregado");
			}else{
				$drafticon = new pix_icon("i/grade_correct", "Entregado");
			}
		}
		
		
		/*
		if($iteration->n_iteration == 0){


			// entrega inicial
			if( $iteration->answers == 0){
				$drafticon = new pix_icon("i/grade_incorrect", "No entregado");
				if( ($daysinseconds + $iteration->start_date) > time()){
					$statusicon = new pix_icon("i/grade_correct", "si");
					$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
							"action" => "initial",
							"cmid" => $cmid,
							"instance" => $evaparesid,
							"sesskey" => sesskey(),
							"ei" => $iteration->eiid,
							"ee" => $iteration->id
					));
				}
			}
			
		}else if( $iteration->n_iteration == ($count-1) ){
			// entrega final
			if( $iteration->answers == 0){
					$drafticon = new pix_icon("i/grade_incorrect", "No entregado");
					if( ($daysinseconds + $iteration->start_date) > time()){
						$statusicon = new pix_icon("i/grade_correct", "no entregado");
						$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
								"action" => "last",
								"cmid" => $cmid,
								"instance" => $evaparesid,
								"sesskey" => sesskey(),
								"ei" => $iteration->eiid,
								"ee" => $iteration->id
						));
					}
				}
			
		}else{
			//Iteraciones de entregables
			if( $iteration->answers == 0){
				$drafticon = new pix_icon("i/grade_incorrect", "No entregado");
				if( ($daysinseconds + $iteration->start_date) > time()){
					$statusicon = new pix_icon("i/grade_correct", "no entregado");
					$actionurl = new moodle_url("/mod/evapares/evaluations.php", array(
							"action" => "iteration",
							"cmid" => $cmid,
							"instance" => $evaparesid,
							"sesskey" => sesskey(),
							"ei" => $iteration->eiid,
							"ee" => $iteration->id
					));
				}
			}
		}
		*/
		$draft = $OUTPUT->action_icon(
				new moodle_url("#"),
				$drafticon
		);
		$status = $OUTPUT->action_icon(
				new moodle_url("#"),
				$statusicon
		);
		
		$evaluationstable->data [] = array(
				$iteration->evaluation_name,
				$draft,
				$status,
				date("d-m-Y", $iteration->start_date),
				date("d-m-Y", ($daysinseconds + (int)$iteration->start_date)),
				$actionicon
		);
		
	}
	
	$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
	
	echo html_writer::table($evaluationstable);
	echo "<hr>";
	echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));

}
