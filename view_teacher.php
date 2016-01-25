//Pruebas de query
<?php

echo "<br>".$pruebaNombres ; 
//$cantidadDePreguntas = $DB->get_record_sql('') ;
for($pregunta= 0 ; $pregunta <$cantidadDePreguntas; $pregunta++){
$check= $DB->get_record_sql('SELECT id,text FROM mdl_evapares_answers WHERE id='.$pregunta) ;


//Retrive where the group's course id
$groupid= $idUser->groupid ;
$idGroup = $DB->get_record_sql('SELECT courseid FROM mdl_groups
								INNER JOIN mdl_groups_members 
								ON mdl_groups_members.groupid = mdl_groups.id
								WHERE mdl_groups.id = mdl_groups_members.groupid 
								AND mdl_groups.courseid ='.$groupid) ;
$course = get_course($courseid);

//Retrieve Group Numbers for the students
$NumberForStudentGroup = $DB-> get_record_sql('SELECT `groupid`
											   FROM mdl_groups_members gm
											   JOIN mdl_groups g ON gm.groupid = g.id') ;

//Count the amount of students in the course cd.id=2
$NumberOfStudentsInCourse = $DB->get_record_sql('SELECT cr.SHORTNAME, cr.FULLNAME, 
      											 COUNT(ra.ID) AS enrolled 
												 FROM   `MDL_COURSE` cr 
				       							 JOIN `MDL_CONTEXT` ct 
						        				 ON ( ct.INSTANCEID = cr.ID ) 
						      					 LEFT JOIN `MDL_ROLE_ASSIGNMENTS` ra 
              									 ON ( ra.CONTEXTID = ct.ID ) 
												 WHERE  ct.CONTEXTLEVEL = 50 
												       AND ra.ROLEID = 5 
                                                       AND cr.id = 2
												 GROUP  BY cr.SHORTNAME, 
												          cr.FULLNAME 
												 ORDER  BY `ENROLLED` ASC ') ;
//Retrieve Name of the students
$NameOfTheStudentsInCourse = $DB->get_record_sql('SELECT u.username,u.id
												  FROM mdl_user u
												  INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
												  INNER JOIN mdl_context ct ON ct.id = ra.contextid
												  INNER JOIN mdl_course c ON c.id = ct.instanceid
												  INNER JOIN mdl_role r ON r.id = ra.roleid') ;
//Retrieve the total amount of characters written FOR a SINGLE student
$TotalLength = $DB->get_record_sql('SELECT u.username, sum(length("ssc_stop")) AS SumaStop, sum(length("ssc_stop")) AS SumaStart
									sum(length("ssc_stop")) AS SumaContinue
									FROM mdl_evapares_evaluations
									INNER JOIN mdl_user  u
									ON u.id= mdl_evapares_evaluations.alu_evaluado_id
									WHERE length(`ssc_stop`) 
									IN (SELECT LENGTH(`ssc_stop`)
									   FROM mdl_evapares_evaluations
                                       WHERE  mdl_evapares_evaluations.`alu_evaluado_id` = 4)
									GROUP BY alu_evaluado_id') ;
}

//WE MUST DO A NEW MASSIVE QUERY




foreach($NameOfTheStudentsInCourse->u.id AS $key=>$value)
{
$row = array() ;
array_push($row, $value) ;
$length = $TotalLength->SumaStop['$value'] ;
array_push($bidimensional[$key], $row) ;




}
//Start of Table coding
$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');

for($i=0; $i<($evapares->total_iterations) ; $i){
	$i++ ;
	array_push($headings,'Evaluacion numero '.$i) ;
}

array_push($headings, 'Evaluacion Final') ;

$bidimensional = array() ;

for($l=0 ; $l < $NumberOfStudentsInCourse; $l++){
	//Queries for the answers written in those files
	$alpha = $DB->get_record_sql('SELECT ssc_stop FROM mdl_evapares_evaluations WHERE id='.$l) ;
	$beta = $DB->get_record_sql('SELECT ssc_start FROM mdl_evapares_evaluations WHERE id='.$l) ;
	$gamma = $DB->get_record_sql('SELECT ssc_continue FROM mdl_evapares_evaluations WHERE id='.$l) ;
	$name = $DB->get_record_sql ('SELECT id FROM mdl_groups WHERE id =1') ;
	var_dump($name) ;
	//LENGTH OF THE ANSWER  (Trim function could delete white spaces, preventing cheats)
	$stop = strlen($alpha->text)  ;
	$start = strlen($beta->text) ;
	$continue = strlen($gamma->text) ;

	//Here we build the bidimensional array to be displayed in each row
	$bidimensional[$l]=array($stop,$start,$continue) ;

}

echo "<br> <br> <br>" ;
var_dump($evapares->total_iterations) ;
$table = new html_table();
$table->head = $headings ;
$table->data = 	//Codigo para la tabla
	$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');
	
	for($i=0; $i<($evapares->total_iterations) ; $i){
		$i++ ;
		array_push($headings,'Evaluacion numero '.$i) ;
	}
	
	array_push($headings, 'Evaluacion Final') ;
	
	$bidimensional = array() ;
	
	for($l=0 ; $l < $cantidadDeAlumnos; $l++){
		//Queries for the answers written in those files
		$stop = $DB->get_record_sql("SELECT length(`ssc_stop`) FROM mdl_evapares_evaluations WHERE id=".$l) ;
		$start = $DB->get_record_sql('SELECT ssc_start FROM mdl_evapares_evaluations WHERE id='.$l) ;
		$continue = $DB->get_record_sql('SELECT ssc_continue FROM mdl_evapares_evaluations WHERE id='.$l) ;
		$name = $DB->get_record_sql ('SELECT id FROM mdl_groups WHERE id =1') ; 
		//LENGTH OF THE ANSWER  (Trim function could delete white spaces, preventing cheats)
		$stopLength = strlen($stop->text)  ;
		$startLength = strlen($start->text) ;
		$continueLength = strlen($continue->text) ;
	
	
		//Here we build the bidimensional array to be displayed in each row
		$bidimensional[$l]=array($name,$stopLength,$startLength,$continueLength) ;
	
	}
	get_enrolled_students($context,$withcapability = 'view',$groupid=2) ;
	echo "<br> <br> <br>" ;
	$table = new html_table();
	$table->head = $headings ;
	$table->data = $bidimensional ;
	//Actual Table generating code below, do NOT tweak
	
	$SUPERQUERY = $DB->get_records_sql('SELECT g.id AS group_id, u.username AS USERname,u.id AS USERid, SUM(length("ssc_stop")) AS SumaStop,  SUM(length(`ssc_start`)) AS SumaStart, SUM(length(`ssc_continue`)) AS SumaContinue
FROM mdl_user u
INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
INNER JOIN mdl_context ct ON ct.id = ra.contextid
INNER JOIN mdl_course c ON c.id = ct.instanceid
INNER JOIN mdl_role r ON r.id = ra.roleid
INNER JOIN mdl_groups_members gm ON gm.userid = u.id
INNER JOIN mdl_groups g ON g.id = gm.groupid
INNER JOIN mdl_evapares_evaluations eval ON u.id= eval.alu_evaluado_id
WHERE length(`ssc_stop`)
IN (SELECT LENGTH(`ssc_stop`)
FROM mdl_evapares_evaluations eval
   WHERE `alu_evalua_id`!=`alu_evaluado_id`)
GROUP BY eval.alu_evaluado_id');
	//Table Headers
	$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');
	//Add a column for every extra evaluation besides Initial and Final Ones
	for($i=0; $i<($evapares->total_iterations) ; $i){
		$i++ ;
		array_push($headings,'Ev. '.$i) ;
	}
	
	array_push($headings, 'Ev.Final') ;
	//Table Data
	
	$bidimensional = array() ;
	
	foreach($SUPERQUERY AS $values) {
		$bidimensional[$values->userid][0] =$values->group_id;
		$bidimensional[$values->userid][1] =$values->username;
		$bidimensional[$values->userid][2] =$values->userid;
		$bidimensional[$values->userid][3] =$values->sumastop;
		$bidimensional[$values->userid][4] =$values->sumastart;
		$bidimensional[$values->userid][5] =$values->sumacontinue;
	}
	var_dump($bidimensional) ;
	
	
echo "hola" ;

echo html_writer::table($table);

var_dump($CFG) ;


