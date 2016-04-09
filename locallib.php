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
	
	// GET if the user have group
	$groupid = groups_get_user_groups($COURSE->id, $USER->id);
	
	if(isset($groupid[0][0])){		
	
		$evaluationstable = new html_table();
		$evaluationstable->size = array(
				"28%",
				"9%",
				"9%",
				"16%",
				"16%",
				"10%"
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
			
			if( (TRUE ||$iteration->start_date <= time()) && ($daysinseconds + (int)$iteration->start_date >= time()) ){
				
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
							new confirm_action(get_string('confirmpopup','mod_evapares'))
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
					date("H:i - d-m-Y", $iteration->start_date),
					date("H:i - d-m-Y", ($daysinseconds + (int)$iteration->start_date)),
					$actionicon
			);
			
		}
		
		$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
		
		//echo "La hora de servidor es ".date("H:i - d-m-Y",time());
		echo html_writer::table($evaluationstable);
		echo "<hr>";
		echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));
	}else{
		
		$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
		
		echo "No perteneces a ningun grupo, por lo cual no puedes realizar las evaluaciones.";
		echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));
	}

}


function evapares_get_teacherview($cmid, $evapares){
	global $DB, $OUTPUT, $COURSE;
	
	$table_data_query = "SELECT g.name AS group_name, u.lastname, u.firstname, u.id AS userid,
			   SUM(length(ee.ssc_stop)) AS sumastop, SUM(length(ee.ssc_start)) AS sumastart,
			   SUM(length(ee.ssc_continue)) AS sumacontinue, ee.answers AS rdy, AVG(ee.nota) AS avg_nota,
			   ee.iterations_id AS it_id, ei.n_iteration AS inumb, ei.start_date AS stdate
			   FROM {user} AS u
			   INNER JOIN {groups_members} AS gm ON u.id = gm.userid
			   INNER JOIN {groups} AS g ON gm.groupid = g.id
		       INNER JOIN {course} AS c ON g.courseid = c.id
		       INNER JOIN {course_modules} AS cm ON c.id = cm.course
		       INNER JOIN {evapares_iterations} AS ei ON ei.evapares_id = cm.id
		       INNER JOIN {evapares_evaluations} AS ee ON ee.iterations_id = ei.id
		       WHERE cm.id = ?
			   AND ee.alu_evaluado_id = u.id
			   GROUP BY userid, it_id
			   ORDER BY group_name, lastname, it_id";

	$get_table_data = $DB-> get_recordset_sql($table_data_query ,array($cmid));
	
	$iterations = $DB->get_records_sql('SELECT n_iteration FROM {evapares_iterations} WHERE evapares_id = ?', 
			array($cmid)
	);
	
	$evaluation_names = $DB->get_records_sql('SELECT evaluation_name FROM {evapares_iterations} WHERE evapares_id = ?', 
			array($cmid)
	);
	
	//icons
	$check = $OUTPUT->pix_icon("i/grade_correct", get_string('realized','mod_evapares'));
	$cross = $OUTPUT->pix_icon("i/grade_incorrect", get_string('unrealized','mod_evapares'));
	$improve = $OUTPUT->pix_icon("t/up", get_string('improved','mod_evapares'));
	$worse = $OUTPUT->pix_icon("t/down", get_string('worse','mod_evapares'));
	$keeps = $OUTPUT->pix_icon("t/less", get_string('keeps','mod_evapares'));
	$studenticondetail = new pix_icon("i/preview", get_string("view_details", "mod_evapares"));
	$disabledicon = $OUTPUT->action_icon(new moodle_url("#"), new pix_icon("i/show", get_string("notavailable", "mod_evapares")));
	
	$date = time();
	$info = array();
	$key = 1;
	
	foreach($get_table_data as $data){
		
		$info[$key]=$data;
		$key = $key + 1;
	}
	$get_table_data->close();
	
	$table_data = array();
	$current_student_data = null;
	
	for($j = 1; $j <= count($info); $j ++){
		
		if(!$current_student_data || $current_student_data != $info[$j]->userid){
			
			$table_row = array();
			$current_student_data = $info[$j]->userid;

			$studenturldetail = new moodle_url("/mod/evapares/student_details.php",
								array("action" => "view",
									  "studentid" => $info[$j]->userid,
									  "cmid" => $cmid						
			));	
			
			$studentactiondetail = $OUTPUT->action_icon($studenturldetail, $studenticondetail);
			$disabledicon = $OUTPUT->action_icon(
					new moodle_url("#"),
					new pix_icon("i/show", "No disponible")
					);
			
			
			$table_row[] = $info[$j]->group_name;
			$table_row[] = $info[$j]->firstname.' '.$info[$j]->lastname;
			
	
		}
		
		if( $date < $info[$j]->stdate && $info[$j]->inumb == 0){
			//checks if the evaluation has not started and is the number zero
			
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			
			$table_row[] = $disabledicon;
			
		}elseif($date > $info[$j]->stdate && $info[$j]->inumb == 0 && $info[$j + 1]->stdate > $date){
			// checks if the evaluation is already made​, is the number zero and the next does not start yet
			
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			$table_row[] = get_string('not_available','mod_evapares');
			
			$table_row[] = $disabledicon;
	
		}else if($date > $info[$j]->stdate && $info[$j]->inumb >= 0 && $info[$j]->inumb <= $evapares->total_iterations && $info[$j + 1]->stdate > $date){
			// checks if the evaluation is already made, is not the number zero , is not the last and the next does not start yet
			
			$table_row[] = $info[$j]->sumastop;
			$table_row[] = $info[$j]->sumastart;
			$table_row[] = $info[$j]->sumacontinue;
			
			// check progress according grades
			if($info[$j]->avg_nota == $info[$j - 1]->avg_nota){
				$table_row[] = $keeps;
				
			}elseif($info[$j]->avg_nota > $info[$j - 1]->avg_nota){
				$table_row[] = $improve;
				
			}elseif($info[$j]->avg_nota < $info[$j - 1]->avg_nota){
				$table_row[] = $worse;
			}
			
			$table_row[] = $studentactiondetail;
					
			
		}elseif($date > $info[$j]->stdate && $info[$j]->inumb == $evapares->total_iterations + 1){
			// checks if the evaluation is already made and is the last one
			
			$table_row[] = $info[$j]->sumastop;
			$table_row[] = $info[$j]->sumastart;
			$table_row[] = $info[$j]->sumacontinue;
			
			// check progress according grades
			if($info[$j]->avg_nota == $info[$j - 1]->avg_nota){
				$table_row[] = $keeps;
				
			}elseif($info[$j]->avg_nota > $info[$j - 1]->avg_nota){
				$table_row[] = $improve;
				
			}elseif($info[$j]->avg_nota < $info[$j - 1]->avg_nota){
				$table_row[] = $worse;
			}

			$table_row[] = $studentactiondetail;
					
		}
		
		// checks the status of evaluations and displays the corresponding icon
		if($info[$j]->inumb == $evapares->total_iterations + 1){
			
			for($h = 1; $h <= count($info); $h ++){
				if($info[$j]->userid == $info[$h]->userid){
					
					
					if($info[$h]->stdate > $date){
						$table_row[] = $cross;
						
					}elseif($info[$h]->rdy == 1){
						$table_row[] = $check;
						
					}elseif($info[$h]->rdy == 0){
						$table_row[] = $cross;
					}
				}
				
			}

			$table_data[] =  $table_row;
		}
	}
	
	$headings = array(
			get_string('group','mod_evapares'), 
			get_string('name','mod_evapares'), 
			'Stop', 
			'Start', 
			'Continue', 
			get_string('progress','mod_evapares'),
			get_string('detail','mod_evapares')
			
	);
	
	$size = array(
			'3%',
			'20%',
			'5%',
			'5%',
			'5%',
			'5%',
			'5%'
	);
	
	$align = array(
			"center",
			"left",
			"center",
			"center",
			"center",
			"center",
			"center"
	);
	
	//Add a column for every extra evaluation besides Initial and Final Ones	
	foreach($evaluation_names as $key => $names){	
		$headings[] = $names->evaluation_name;
		$size[] ='5%';
		$align[] = "center";
	}
	
	$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
	$button = "<br>".$OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));
	
	$table = new html_table();
	$table->head = $headings;
	$table->size = $size;
	$table->align = $align;
	$table->data = $table_data;
	
	echo $button.html_writer::table($table).$button;
	
}

function evapares_edit_tabs($cmid) {
	$edittab = array();
	
	$edittab[] = new tabobject("Resumen",
			new moodle_url("/mod/evapares/view.php", array(
					"id" => $cmid)), "Resumen");
	
	$edittab[] = new tabobject("Configuración",
			new moodle_url("/mod/evapares/configuration.php", array(
					"cmid" => $cmid)), "Configuración");
	
	return $edittab;
}

// Copy of emarking/marking/locallib.php
// emarking_save_data_to_excel($headers, $tabledata, $excelfilename, $colnumber = 5)
function evapares_save_data_to_excel($headers, $tabledata, $excelfilename, $colnumber = 5) {
	// Creating a workbook.
	$workbook = new MoodleExcelWorkbook("-");
	// Sending HTTP headers.
	$workbook->send($excelfilename);
	// Adding the worksheet.
	$myxls = $workbook->add_worksheet(get_string('evapares', 'mod_evapares'));
	// Writing the headers in the first row.
	$row = 0;
	$col = 0;
	foreach (array_values($headers) as $d) {
		$myxls->write_string($row, $col, $d);
		$col ++;
	}
	// Writing the data.
	$row = 1;
	foreach ($tabledata as $data) {
		$col = 0;
		foreach (array_values($data) as $d) {
			if ($row > 0 && $col >= $colnumber) {
				$myxls->write_number($row, $col, $d);
			} else {
				$myxls->write_string($row, $col, $d);
			}
			$col ++;
		}
		$row ++;
	}
	$workbook->close();
}

function evapares_get_summary_data($cmid, $evaparesname){
	global $DB, $COURSE;
	
	$headers = array(
			"01id" => "ID",
			"02Fistname" => "Fistname",
			"03Lastname" => "Lastname",
			"04Group" => "Group",
			"05Email" => "Email",
			"06Grade" => "Grade"
	);
	
	$tabledata = array();
	
	$excelfilename = "evapares_summary_".$evaparesname."_".date("d-m-Y",time());
	
	$sqlgetdata = "SELECT u.id AS userid, u.firstname, u.lastname, g.name AS groupname, AVG(ee.nota) AS grade, u.email
			   FROM {user} AS u
			   JOIN {groups_members} AS gm ON (u.id = gm.userid)
			   JOIN {groups} AS g ON (gm.groupid = g.id)
		       JOIN {course} AS c ON (g.courseid = c.id)
		       JOIN {course_modules} AS cm ON (c.id = cm.course AND cm.id = ?)
		       JOIN {evapares_iterations} AS ei ON (ei.evapares_id = cm.id)
		       JOIN {evapares_evaluations} AS ee ON (ee.iterations_id = ei.id AND ee.alu_evaluado_id = u.id)
			   GROUP BY userid
			   ORDER BY userid, lastname, firstname";
	
	if($data = $DB->get_records_sql($sqlgetdata, array($cmid))){
		foreach ($data as $row){
			$grade = "0.000";
			if($row->grade != NULL){
				$grade = $row->grade;
			}
			$tabledata[] = array(
					(int)$row->userid,
					$row->firstname,
					$row->lastname,
					$row->email,
					$row->groupname,
					$grade
			);
		}
	}
	
	evapares_save_data_to_excel($headers, $tabledata, $excelfilename);
}

function evapares_get_all_data($cmid, $evaparesname){
	$excelfilename = "evapares_alldata_".$evaparesname."_".data("d-m-Y",time());
	
	$headers = array();
	evapares_save_data_to_excel($headers, $tabledata, $excelfilename);
}

function evapares_result_tabs($cmid) {
	$resultstab = array();

	$resultstab[] = new tabobject("Evaluaciones",
			new moodle_url("/mod/evapares/view.php", array(
					"id" => $cmid)), "Evaluaciones");

	$resultstab[] = new tabobject("Resultados",
			new moodle_url("/mod/evapares/student_details.php", array(
					"cmid" => $cmid)), "Resultados");

	return $resultstab;
}