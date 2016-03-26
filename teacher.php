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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$table_data_query = "SELECT g.id AS group_id, u.lastname AS lastname, u.firstname AS firstname, u.id AS userid,
			   SUM(length(ee.ssc_stop)) AS sumastop, SUM(length(ee.ssc_start)) AS sumastart,
			   SUM(length(ee.ssc_continue)) AS sumacontinue, ee.answers AS rdy, AVG(ee.nota) AS avg_nota,
			   ee.iterations_id AS it_id, ei.n_iteration AS inumb, ei.start_date AS stdate
			   FROM mdl_user u
			   INNER JOIN {groups_members} AS gm ON u.id = gm.userid
			   INNER JOIN {groups} AS g ON gm.groupid = g.id
		       INNER JOIN {course} AS c ON g.courseid = c.id
		       INNER JOIN {course_modules} AS cm ON c.id = cm.course
		       INNER JOIN {evapares_iterations} AS ei ON ei.evapares_id = cm.id
		       INNER JOIN {evapares_evaluations} AS ee ON ee.iterations_id = ei.id
		       WHERE cm.id = ?
			   AND ee.alu_evaluado_id = u.id
			   GROUP BY userid, it_id
			   ORDER BY group_id, lastname, it_id";

$get_table_data = $DB-> get_recordset_sql($table_data_query ,array($cmid));

$iterations = $DB->get_records_sql('SELECT n_iteration
									FROM {evapares_iterations}
									WHERE evapares_id='.$cm->id );

$evaluation_names = $DB->get_records_sql('SELECT ei.evaluation_name
										  FROM {evapares_iterations} as ei
										  WHERE ei.evapares_id ='.$cm->id);

//icons
$check = $OUTPUT->pix_icon("i/grade_correct", get_string('realized','mod_evapares'));
$cross = $OUTPUT->pix_icon("i/grade_incorrect", get_string('unrealized','mod_evapares'));
$improve = $OUTPUT->pix_icon("s/yes", get_string('improved','mod_evapares'));
$worse = $OUTPUT->pix_icon("s/no", get_string('worse','mod_evapares'));
$studenticondetail = new pix_icon("i/preview", get_string("view_details", "mod_evapares"));

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
		
		$current_student_data = $info[$j]->userid;
		$table_row = array();
		
		$studenturldetail = new moodle_url("/mod/evapares/student_details.php",
							array("action" => "view",
								  "studentid" => $info[$j]->userid,
								  "cmid" => $cmid));		
		$studentactiondetail = $OUTPUT->action_icon($studenturldetail, $studenticondetail);
	
	array_push($table_row, $info[$j]->group_id);
	array_push($table_row, $info[$j]->lastname.' '.$info[$j]->firstname);
	array_push($table_row, $studentactiondetail);

	}
	if($date < $info[$j]->stdate && $info[$j]->inumb == 0){
		//checks if the evaluation has not started and is the number zero
		
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));

		
	}elseif($date > $info[$j]->stdate && $info[$j]->inumb == 0 && $info[$j + 1]->stdate > $date){
		// checks if the evaluation is already made​, is the number zero and the next does not start yet
		
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));
		array_push($table_row, get_string('not_available','mod_evapares'));

	}elseif($date > $info[$j]->stdate && $info[$j]->inumb > 0 && $info[$j]->inumb <= $evapares->total_iterations && $info[$j + 1]->stdate > $date){
		// checks if the evaluation is already made, is not the number zero , is not the last and the next does not start yet
		
		array_push($table_row, $info[$j]->sumastop);
		array_push($table_row, $info[$j]->sumastart);
		array_push($table_row, $info[$j]->sumacontinue);
		
		// check progress according grades
		if($info[$j]->avg_nota == $info[$j - 1]->avg_nota){
			array_push($table_row, 'I');
		}elseif($info[$j]->avg_nota > $info[$j - 1]->avg_nota){
			array_push($table_row, $improve);
		}elseif($info[$j]->avg_nota < $info[$j - 1]->avg_nota){
			array_push($table_row, $worse);
		}
		
	}elseif($date > $info[$j]->stdate && $info[$j]->inumb == $evapares->total_iterations + 1){
		// checks if the evaluation is already made and is the last one
		
		array_push($table_row, $info[$j]->sumastop);
		array_push($table_row, $info[$j]->sumastart);
		array_push($table_row, $info[$j]->sumacontinue);
		
		// check progress according grades
		if($info[$j]->avg_nota == $info[$j - 1]->avg_nota){
			array_push($table_row, 'I');
		}elseif($info[$j]->avg_nota > $info[$j - 1]->avg_nota){
			array_push($table_row, $improve);
		}elseif($info[$j]->avg_nota < $info[$j - 1]->avg_nota){
			array_push($table_row, $worse);
		}
	}
	
	// checks the status of evaluations and displays the corresponding icon
	if($info[$j]->inumb == $evapares->total_iterations + 1){
		
		for($h = 1; $h <= count($info); $h ++){
			if($info[$j]->userid == $info[$h]->userid){
				
				if($info[$h]->stdate > $date){
					array_push($table_row, 'N.R.');
				}elseif($info[$h]->rdy == 1){
					array_push($table_row, $check);
				}elseif($info[$h]->rdy == 0){
					array_push($table_row, $cross);
				}
			}
		}
		
		array_push($table_data, $table_row);
	}
}

$headings = array(get_string('group','mod_evapares'), get_string('name','mod_evapares'),
				 get_string('detail','mod_evapares'), 'Stop', 'Start', 'Continue', get_string('progress','mod_evapares'));

$size = array('3%','20%','5%','5%','5%','5%','5%');

//Add a column for every extra evaluation besides Initial and Final Ones
	
	foreach($evaluation_names as $key => $names){
		
		array_push($headings, $names->evaluation_name);
		array_push($size, '%5');

	}

	
$url =  new moodle_url("/course/view.php",array('id' => $COURSE->id));
echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));


$table = new html_table();
$table->size = $size;
$table->head = $headings;
$table->data = $table_data;
echo html_writer::table($table);

echo $OUTPUT->single_button($url, get_string('back_to_course','mod_evapares'));
