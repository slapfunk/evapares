<?php
//Beginning of Teacher's view
$bidimensional = array() ;	
//Count the amount of students in the course cd.id=2
// $NumberOfStudentsInCourse = $DB->get_record_sql('SELECT cr.SHORTNAME, cr.FULLNAME,
//       											 COUNT(ra.ID) AS enrolled
// 												 FROM   {course} cr
// 				       							 JOIN {context} ct
// 						        				 ON ( ct.INSTANCEID = cr.ID )
// 						      					 LEFT JOIN `MDL_ROLE_ASSIGNMENTS` ra
//               									 ON ( ra.CONTEXTID = ct.ID )
// 												 WHERE  ct.CONTEXTLEVEL = 50
// 												       AND ra.ROLEID = 5
//                                                        AND cr.id ='.$course->id.'
// 												 GROUP  BY cr.SHORTNAME,
// 												          cr.FULLNAME
// 												 ORDER  BY `ENROLLED` ASC ') ;
//get group_id, user_id and user_name, and the sums for stop, start, continue
$dataquery = $DB->get_records_sql('SELECT u.id AS userid, g.id AS group_id, u.username AS username, SUM(length(ssc_stop)) AS sumastop,
		SUM(length(ssc_start)) AS sumastart, SUM(length(ssc_continue)) AS sumacontinue
FROM {user} u
INNER JOIN {role_assignments} ra ON ra.userid = u.id
INNER JOIN {context} ct ON ct.id = ra.contextid
INNER JOIN {course} c ON c.id = ct.instanceid
INNER JOIN {role} r ON r.id = ra.roleid
INNER JOIN {groups_members} gm ON gm.userid = u.id
INNER JOIN {groups} g ON g.id = gm.groupid
INNER JOIN {course_modules} cm ON cm.course = c.id
LEFT JOIN {evapares_evaluations} eval ON u.id= eval.alu_evaluado_id
WHERE alu_evalua_id != alu_evaluado_id
AND cm.id = '.$cm->id.'		
group BY userid');

$resultados = $DB->get_records_sql('SELECT eval.id `iterations_id`,alu_evalua_id AS evaluador
		,alu_evaluado_id AS Evaluado,`answers`, iter.id AS iteration
FROM {evapares_evaluations} eval
INNER JOIN {evapares_iterations} iter ON iter.id = eval.iterations_id
WHERE iter.evapares_id ='.$cmid.'
ORDER BY `alu_evalua_id`, `iterations_id`') ;

$StartDate = $DB->get_records_sql('SELECT iter.n_iteration,iter.start_date AS start_date
FROM mdl_user u
INNER JOIN {role_assignments} ra ON ra.userid = u.id
INNER JOIN {context} ct ON ct.id = ra.contextid
INNER JOIN {course} c ON c.id = ct.instanceid
LEFT JOIN {evapares_evaluations} eval ON u.id= eval.alu_evaluado_id
LEFT JOIN {evapares_iterations} iter ON iter.id = eval.iterations_id
WHERE c.id = '.$course->id.'
GROUP BY n_iteration') ; 

// $resultadoInvividual = $DB->get_records_sql("SELECT eval.id `iterations_id`,alu_evalua_id AS evaluador
// 			,alu_evaluado_id AS Evaluado,`answers`, iter.id AS iteration
// 	FROM {evapares_evaluations} eval
// 	INNER JOIN {user} u ON u.id = eval.alu_evalua_id
// 	INNER JOIN {evapares_iterations} iter ON iter.id = eval.iterations_id
// 	WHERE iter.evapares_id = '.$cm->id.'");

$iterations = $DB->get_records_sql('SELECT n_iteration
		FROM {evapares_iterations}
		WHERE evapares_id='.$cm->id )  ;

$actualDate = time() ;
// Add the date with which to retrieve the data for the last evapares iteration
// $lastIteration = 0 ;
// $dates = array() ;
// foreach ($StartDate[1] as $dates)
// {
// if ($actualDate <= $date[1])
// 	{
// 	$lastIteration = $dates->n_iteration ;
// 	}	
// 	array_push($dates, $date->start_date) ; 
// }
// 	var_dump($lastIteration) ;

//Table Headers
$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');
//Add a column for every extra evaluation besides Initial and Final Ones
for($i=0; $i<($evapares->total_iterations) ; $i){
	$i++ ;
	array_push($headings,'Ev. '.$i) ;
}
array_push($headings, 'Ev.Final') ;

//Table Data

foreach($dataquery AS $values)
{
	$deleteurlprinter = new moodle_url("/mod/evapares/student_details.php",
			array(
					"action" => "view",
					"studentid" => $values->userid,
					"cmid" => $cmid));
			$deleteiconprinter = new pix_icon("i/preview", get_string("view_details", "mod_evapares"));
			$deleteactionprinter = $OUTPUT->action_icon($deleteurlprinter, $deleteiconprinter);
					
	$bidimensional[$values->userid][0] =$values->group_id;
	$bidimensional[$values->userid][1] =$values->username;
	$bidimensional[$values->userid][2] =$deleteactionprinter;
	//If values are NULL, write '0' in the table
	
	if ($values->sumastop)
			{
				$bidimensional[$values->userid][3] =$values->sumastop;
			}
			else
				{
					$bidimensional[$values->userid][3] = 0;
				}
	if ($values->sumastart)
			{
				$bidimensional[$values->userid][4] =$values->sumastart;
			}
			else
				{
					$bidimensional[$values->userid][4] = 0 ;
				}
				
	if($values->sumacontinue)
			{
				$bidimensional[$values->userid][5] =$values->sumacontinue;
			}
			else
				{ 
					$bidimensional[$values->userid][5] = 0 ; 
				}
	$partialKey = 1 ; 
	foreach ($resultados AS $partialEvaluationsValues)
	{
		//Here we had a problem after a db actualization, the problem wasn't found so a patch was implemented
		//This will do a series of queries, after each one of them checking for the 'answers' column
		//the same code that ran before, didn't anymore, if with time, try and change this
		

//	GROUP BY alu_evalua_id='.$bidimensional[$values->userid][2]) ;

//echo $bidimensional[$values->userid][2];
//var_dump($StartDate);
//var_dump($actualDate);
//var_dump($resultados);
			if($StartDate<= $actualDate && $resultados->answers == 1)
			{
				var_dump($resultadoInvividual->answers);
 				$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/respondido.jpg" style=width:15px;height:15px;>';
			}
			elseif($StartDate<= $actualDate && $resultados->answers == 0)
			{
				$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/norespondible.jpg" style=width:15px;height:15px;>';
			}
			elseif($StartDate>= $actualDate)
			{
				$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/nodisponible.jpg" style=width:15px;height:15px;>';
			}
		$partialKey++ ;
	if(count($bidimensional[$values->userid]) > count($headings)-1) break ;
	}
}
echo "<h3><u> <divc><span style='margin-left:120px ; width:45%;' >".//$get_string('lastEvaluation','mod_evapares')
	'Ultima Evaluacion'."</span></u>
		   <span style = 'float : right ; width: 55%;'><u>".//$get_string('periodSummary','mod_evapares')
		   'Resumen de Evaluaciones'."</u> </span></div></h3>" ;
// no reconoce estos dos ultimos langs

$sizePercentage = array('5%','10%','5%','5%','5%','5%','10%') ;
$table = new html_table();
$table->head = $headings ;
$table->data = $bidimensional ;
$table->size = $sizePercentage ; 
echo html_writer::table($table);


//End of Teacher's View