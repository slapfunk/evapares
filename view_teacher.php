//Pruebas de query
<?php
echo"<br><br><br>" ;
$user = $DB->get_record_sql('SELECT * FROM mdl_course WHERE id = 1');
echo $user->id ;
echo "<br>" ;
$alpha = $DB->get_record_sql('SELECT id,text FROM mdl_evapares_answers WHERE id= 1 ') ;
if($alpha->text != NULL){echo strlen($alpha->text) ; } else {echo  "No hay nada aca parece" ; }
$pruebaNombres = $DB->get_record_sql('SELECT alu_evalua_id FROM mdl_evapares_evaluations WHERE alu_evalua_id=1') ;
echo "<br>".$pruebaNombres ; 
//$cantidadDePreguntas = $DB->get_record_sql('') ;
for($pregunta= 0 ; $pregunta <$cantidadDePreguntas; $pregunta++){
$check= $DB->get_record_sql('SELECT id,text FROM mdl_evapares_answers WHERE id='.$pregunta) ;

//Retrieve Group Number
$userid = 1 ;
$idUser = $DB->get_record_sql('SELECT groupid FROM mdl_groups_members WHERE userid ='.$userid) ;
//Retrive where the group's course id
$groupid= $idUser->groupid ;
$idGroup = $DB->get_record_sql('SELECT courseid FROM mdl_groups
								INNER JOIN mdl_groups_members 
								ON mdl_groups_members.groupid = mdl_groups.id
								WHERE mdl_groups.id = mdl_groups_members.groupid 
								AND mdl_groups.courseid ='.$groupid) ;
//Count the amount of dudes in the course
$NumberOfStudentsInCourse = $DB->get_record_sql('SELECT cr.SHORTNAME, cr.FULLNAME, 
      											 COUNT(ra.ID) AS enrolled 
												 FROM   `MDL_COURSE` cr 
				       							 JOIN `MDL_CONTEXT` ct 
						        				 ON ( ct.INSTANCEID = cr.ID ) 
						      					 LEFT JOIN `MDL_ROLE_ASSIGNMENTS` ra 
              									 ON ( ra.CONTEXTID = ct.ID ) 
												 WHERE  ct.CONTEXTLEVEL = 50 
												       AND ra.ROLEID = 5 
												 GROUP  BY cr.SHORTNAME, 
												          cr.FULLNAME 
												 ORDER  BY `ENROLLED` ASC ') ;
//Retrieve Name of the students
$NameOfTheStudentsInCourse = $DB->get_record-sql('SELECT u.username, c.id
												  FROM mdl_user u
												  INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
												  INNER JOIN mdl_context ct ON ct.id = ra.contextid
												  INNER JOIN mdl_course c ON c.id = ct.instanceid
												  INNER JOIN mdl_role r ON r.id = ra.roleid') ;
//Retrieve Group Numbers for the students
$NumberForStudentGroup = $DB-> get_record_sql('SELECT `groupid` 
											   FROM mdl_groups_members gm
											   JOIN mdl_groups g ON gm.groupid = g.id') ;
}
//Codigo para la tabla
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
		$stop = $DB->get_record_sql('SELECT ssc_stop FROM mdl_evapares_evaluations WHERE id='.$l) ;
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
	var_dump($evapares->total_iterations) ;
	$table = new html_table();
	$table->head = $headings ;
	$table->data = $bidimensional ;
	
echo "hola" ;

echo html_writer::table($table);