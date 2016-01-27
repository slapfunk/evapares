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
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('forms/forms_v.php');
require_once('forms/forms_alu.php');

global $CFG, $DB, $OUTPUT, $PAGE; 

$action = optional_param("action", "view", PARAM_TEXT);
$cmid = required_param('id', PARAM_INT);

if(! $cm = get_coursemodule_from_id('evapares', $cmid))
{print_error('cm'." id: $cmid");}

if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance)))
{print_error('evapares'." id: $cmid");}

if(! $course = $DB->get_record('course', array('id' => $cm->course)))
{print_error('course'." id: $cmid");}
$context = context_module::instance($cm->id);
$iduser=$USER->id;

require_login();
echo '<script src="../evapares/js/jquery.js"></script>
<script src="../evapares/js/controladorbotonbuscar.js"></script>';
// Print the page header.
if(!has_capability('mod/evapares:courseevaluations', $context) && !has_capability('mod/evapares:myevaluations', $context))
{	
	print_error("no tiene la capacidad de estar en  esta pagina");
}
else{
	$PAGE->set_url('/mod/evapares/view.php', array('id' => $cm->id));
	$PAGE->set_context($context);
	$PAGE->set_course($course);
	$PAGE->set_pagelayout("incourse");
	$PAGE->set_cm($cm);
	$PAGE->set_title(format_string($evapares->name));
	$PAGE->set_heading(format_string($course->fullname));
	

	
	if(!$grupos = $DB->get_records("groups", array('courseid'=>$course->id))){
		echo $OUTPUT->header();
		echo 'Debe crear los grupos para continuar con la actividad <br>
		(Administracion del curso > Usuarios > Grupos)';
		
		echo $OUTPUT->footer();
		die();
	}
	
	if(!$evapares_iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid))){
		$action = "add";
	}
		
	$vars = array('num'=>$evapares->total_iterations,
			"cmid"=>$cmid, 
			'preg'=>$evapares->n_preguntas, 
			'resp'=>$evapares->n_respuestas
	);
	
if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){
	
	
	
	$addform = new evapares_num_eval_form(null, $vars);

	$alliterations = array();
	$allquestions = array();
	$allanswers = array();
	$allcombs = array();
	
	if( $addform->is_cancelled() ){
		$backtocourse = new moodle_url("/course/view.php",array('id'=>$course->id));
		redirect($backtocourse);
		
	}
	else if($datas = $addform->get_data()){
		
		for($i = 0; $i <= $evapares->total_iterations + 1; $i++ ){
			$idfe = "FE$i";
			$idne = "NE$i";
			
			$record = new stdClass();
			
			$record->n_iteration = $i;
			$record->start_date = $datas->$idfe;
			$record->evapares_id = (int)$cm->id;
			$record->evaluation_name = $datas->$idne;
			
			$alliterations[]=$record;
		}
		
		$DB->insert_records("evapares_iterations", $alliterations);
		
		$sql = 'SELECT GM1.userid AS a_evalua, GM2.userid AS a_evaluado, EI.id As id_iteration, EI.n_iteration AS n_iteration
			    FROM mdl_groups_members AS GM1, mdl_groups_members AS GM2, mdl_evapares_iterations AS EI
				WHERE GM1.groupid = Gm2.groupid AND EI.evapares_id = ?';
		
		$consulta = $DB-> get_recordset_sql($sql ,array($cm->id));
		
		foreach($consulta as $insert){
			if($insert->a_evalua == $insert->a_evaluado && $insert->n_iteration == 0 || $insert->n_iteration == $evapares->total_iterations +1){
		
				$rec = new stdClass();
		
				$rec->ssc_stop = null;
				$rec->ssc_start = null;
				$rec->ssc_continue = null;
				$rec->answers = '0';
				$rec->alu_evalua_id = $insert->a_evalua;
				$rec->alu_evaluado_id = $insert->a_evaluado;
				$rec->iterations_id = $insert->id_iteration;
		
				$allcombs[] = $rec;
		
			}elseif($insert->a_evalua != $insert->a_evaluado && $insert->n_iteration > 0){
		
				$rec = new stdClass();
		
				$rec->ssc_stop = null;
				$rec->ssc_start = null;
				$rec->ssc_continue = null;
				$rec->answers = '0';
				$rec->alu_evalua_id = $insert->a_evalua;
				$rec->alu_evaluado_id = $insert->a_evaluado;
				$rec->iterations_id = $insert->id_iteration;
		
				$allcombs[] = $rec;
		
			}
		}
		
		$DB->insert_records("evapares_evaluations", $allcombs);
		
		
		for($i = 1; $i <= $evapares->n_preguntas; $i++ ){
			$idp = "P$i";
							
			$recp = new stdClass();	
			
			$recp->n_of_question = $i;
			$recp->evapares_id = (int)$cm->id;
			$recp->text = $datas->$idp;
			
			$DB->insert_record("evapares_questions", $recp);
			
			$questionid = $DB->get_records("evapares_questions", array('evapares_id'=>$cmid));
 			
 			foreach($questionid as $key => $value){
 			$llaves[$i] = $key;
 			}
			
			for($j = 1; $j <= $evapares->n_respuestas; $j++ ){
				$idr = "R$i$j";

				$recr = new stdClass();
			
				$recr->number = $j;
				$recr->question_id = $questionid[$llaves[$i]]->id;
				$recr->text = $datas->$idr;
			
				$allanswers[]=$recr;		
			}

			$DB->insert_records("evapares_answers", $allanswers);
			unset($allanswers);
			
		}
		
			$action = "view";

	}
}
echo $OUTPUT->header();
if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){

	$addform->display();

}
elseif(has_capability('mod/evapares:courseevaluations', $context) && $action == "view"){
	
	
$bidimensional = array() ;
$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');

for($i=0; $i<($evapares->total_iterations) ; $i)
	{
	$i++ ;
	array_push($headings,'Evaluacion numero '.$i) ;
	}
	array_push($headings, 'Evaluacion Final') ;
	
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
//get group_id, user_id and user_name, and the sums for stop, start, continue
$SUPERQUERY = $DB->get_records_sql('SELECT u.id AS userid, g.id AS group_id, u.username AS USERname, SUM(length("ssc_stop")) AS SumaStop,  SUM(length(`ssc_start`)) AS SumaStart, SUM(length(`ssc_continue`)) AS SumaContinue
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
GROUP BY userid');

$resultados = $DB->get_records_sql('SELECT eval.id `iterations_id`,alu_evalua_id AS evaluador
		,alu_evaluado_id AS Evaluado,`answers`, iter.id AS iteration
FROM {evapares_evaluations} eval
INNER JOIN {evapares_iterations} iter ON iter.id = eval.iterations_id
WHERE iter.evapares_id ='.$cmid.'
ORDER BY `alu_evalua_id`, `iterations_id`') ; 
$StartDate = $DB->get_records_sql('SELECT eval.id, iter.`start_date` FROM mdl_evapares_evaluations eval 
								INNER JOIN mdl_evapares_iterations iter ON iter.id = eval.iterations_id 
								GROUP BY iter.id') ; 
$actualDate = time() ;

//Table Headers
$headings= array('Grupo', 'Integrante', 'Res','S','S','C','Ev. Parcial','Ev. Inicial');
//Add a column for every extra evaluation besides Initial and Final Ones
for($i=0; $i<($evapares->total_iterations) ; $i){
	$i++ ;
	array_push($headings,'Ev. '.$i) ;
}
array_push($headings, 'Ev.Final') ;
//Table Data
foreach($SUPERQUERY AS $values)
{
	$bidimensional[$values->userid][0] =$values->group_id;
	$bidimensional[$values->userid][1] =$values->username;
	$bidimensional[$values->userid][2] =$values->userid;
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
		if($StartDate<= $actualDate)
		{
		if($partialEvaluationsValues->answers != 0)
			{
			$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/respondible.jpg" style=width:15px;height:15px;>';
			}
			else
				{
				$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/norespondible.jpg" style=width:15px;height:15px;>';
				}
		}
		else
		{
		$bidimensional[$values->userid][5+$partialKey] ='<img src="pix/norespondido.jpg" style=width:15px;height:15px;>';
		}
		$partialKey++ ;
	}
}


echo "SUPERDUPER"; 
$table = new html_table();
$table->head = $headings ;
$table->data = $bidimensional ;

echo html_writer::table($table);
}

elseif(has_capability('mod/evapares:myevaluations', $context) && $action == "view"){
	if(!isset($_REQUEST['mode'])){
		$currenttab='tb1';
	}
	else {
		if($_REQUEST['mode']=='evaluation'){
			$currenttab='tb1';
		}
		else if ($_REQUEST['mode']=='resultados'){
			$currenttab='tb2';
		}
	}
	$tbz = array();
	$tabz=array();
	$inactive = array();
	$activated = array();
	$inactive = array('7');
	$activated = array('tb1');
	$tbz[] = new tabobject('tb1',new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'evaluation','id' => $cm->id)), get_string('eval','mod_evapares'));
	$tbz[] = new tabobject('tb2',new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'resultados','id' => $cm->id)), get_string('results','mod_evapares'));
	$tabz[]=$tbz;
	print_tabs($tabz,$currenttab,$inactive, $activated);
	if(!isset($_REQUEST['mode'])){
		$mode='evaluation';
	}
	else if(isset($_REQUEST['mode'])){
		$mode=$_REQUEST['mode'];
	}
	if($mode=='evaluation'){
		if(!isset($_SESSION['itra'])){
			$_SESSION['itra']=999;
		}
		$forms= array();
		$varrs=array();
		$table = new html_table();
		$table->head = array(get_string('evals','mod_evapares'), get_string('CompleteTable','mod_evapares'), get_string('activeTable','mod_evapares'), get_string('resultsTable','mod_evapares'));
		$supa_data_sama=array();
		$data_chan=array();
		array_push($data_chan, get_string('initialEval','mod_evapares'));//inicial
		$itera_qry = $DB->get_records_sql('SELECT id FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=?', 
				array($cm->id,'0'));
		foreach($itera_qry as $llave => $resultado){$itera=$resultado->id;}
		if($evapares->ssc==0)$sscb=false;
		else if($evapares->ssc==1)$sscb=true;
		$vars = array('num'=>$evapares->total_iterations,
				"iduser"=>$iduser,
				'iter_id'=>$itera,
				'n_pregs'=>$evapares->n_preguntas,
				'n_resps'=>$evapares->n_respuestas,
				'ssc'=>$sscb,
				'cm_id'=>$cm->id	
		);
		array_push($varrs,$vars);
		$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua_id=?', 
				array($itera,$iduser));
		$ans=false;
		foreach($answrs_qry as $llave=> $answers){
			if($answers->answers==1)$ans=true;
		}
		$respondido='';
		if($ans)$respondio='pix/respondido.jpg';
		else $respondio='pix/norespondido.jpg';
		array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
		//revisar si esta activa la entrega inicial $respondible = bool
		$respondible=true;
		//
		$string_to_strong='<img src="pix/';
		if($respondible){
			$string_to_strong=$string_to_strong.'respondible.jpg" View" style="width:15px;height:15px;">';
		}
		else {
			$string_to_strong=$string_to_strong.'norespondible.jpg" View" style="width:15px;height:15px;">';
		}
		array_push($data_chan,$string_to_strong);
		//if $respondible -> poner boton, else -> not
		array_push($data_chan,'<button id="f0" ><img src="pix/ver.jpg"  View" style="width:15px;height:15px;"></button>');//editar para que sea el boton de jquery
		array_push($supa_data_sama,$data_chan);
		$addform = new evapares_evalu_usua(null, $varrs['0']);
		if( $addform->is_cancelled() ){
			$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
			redirect($backtocourse);
		}
		array_push($forms,$addform);
		var_dump($_SESSION['itra']);
		if( $forms['0']->is_cancelled() ){
			$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
			redirect($backtocourse);
		}
		else if($datas = $forms['0']->get_data()&&isset($_SESSION['itra'])){
			if($_SESSION['itra']==0){
				$eva_perso = $DB->get_records('evapares_evaluations',array('iterations_id'=>$itera, 'alu_evalua_id'=>$iduser,'alu_evaluado_id'=>$iduser));
				foreach($eva_perso as $llave => $ep){
					$ep->answers=1;
					$epid=$ep->id;
				}
				$DB->update_record('evapares_evaluations', $eva_perso[$epid], $bulk=false);
				for($preg_n=1;$preg_n<=$evapares->n_preguntas;$preg_n++){
					$ev_pr=new stdClass();
					$respuesta_perso=new stdClass();
					$respuesta_perso->evaluations_id=$epid;
					$rbtn='r'.$preg_n;
					$qstn_qry = $DB->get_records_sql('SELECT id FROM {evapares_questions} WHERE n_of_question=? AND evapares_id=?',
							array($preg_n,$cm->id));
					foreach($qstn_qry as $llave => $resultado){
						$qstn=$resultado->id;
					}
					var_dump($datas->$rbtn);
					$responde_qry = $DB->get_records_sql('SELECT id FROM {evapares_answers} WHERE number=? AND question_id=?',
							array($datas->$rbtn,$qstn));
					foreach($responde_qry as $llave => $resultado){
						$responde=$resultado->id;
					}
					$respuesta_perso->answers_id=$responde;
					$DB->insert_record('evapares_eval_has_answ', $respuesta_perso, $returnid=false, $bulk=false);
					echo "guardo la inicial";
				}
			
			}

		}
		//revisar si esta activa la entrega inicial
		$num=$evapares->total_iterations;
		for($i=1;$i<=$num;$i++){
			$data_chan=array();
				
			$itera_qry = $DB->get_records_sql('SELECT id,evaluation_name FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=?',
					array($cm->id,$i));
			foreach($itera_qry as $llave => $resultado){
				$itera=$resultado->id;
				$nomitera=$resultado->evaluation_name;
			}
			$vars = array('num'=>$evapares->total_iterations,//
					"iduser"=>$iduser,
					'iter_id'=>$itera,
					'n_pregs'=>$evapares->n_preguntas,//
					'n_resps'=>$evapares->n_respuestas,//
					'ssc'=>$sscb,//
					'cm_id'=>$cm->id
			);
			array_push($varrs,$vars);
			array_push($data_chan,$nomitera);
			$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua_id=? limit 1 ', 
					array($itera,$iduser));
			$ans=false;
			foreach($answrs_qry as $llave=> $answers){
				if($answers->answers==1)$ans=true;
			}
			$respondido='';
			if($ans)$respondio='pix/respondido.jpg';
			else $respondio='pix/norespondido.jpg';
			array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
			//revisar si esta activa la entrega inicial
			array_push($data_chan,'<img src="pix/respondible.jpg" View" style="width:15px;height:15px;">');

			array_push($data_chan,'<button id="f'.$i.'" ><img src="pix/ver.jpg" View" style="width:15px;height:15px;"></button>');//editar para que sea el boton de jquery
			$addform = new evapares_evalu_usua(null, $varrs[$i]);
			array_push($forms,$addform);
			var_dump($_SESSION['itra']);
			if( $forms[$i]->is_cancelled() ){
				$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
				redirect($backtocourse);
			}
			/////////////////////////////////////////////////////////////AqUI
			else if($datas = $forms[$i]->get_data()&&isset($_SESSION['itra'])){
				if($_SESSION['itra']==$i){
					$evaluado_qry= $DB->get_records_sql('SELECT id, alu_evaluado_id FROM {evapares_evaluations}
					WHERE alu_evalua_id = ? AND iterations_id=?',
							array($iduser,$itera));
					foreach($evaluado_qry as $llave => $evaluadox){
						$evaluado_id=$evaluadox->alu_evaluado_id;
						$evaluazion_id=$evaluadox->id;
						$eva_pares = $DB->get_records('evapares_evaluations',array('iterations_id'=>$itera, 'alu_evalua_id'=>$iduser,'alu_evaluado_id'=>$evaluado_id));
						foreach($eva_pares as $llave => $ep){
					
							$s1='ssc_stop'.$evaluado_id;
							$s2='ssc_start'.$evaluado_id;
							$s3='ssc_continue'.$evaluado_id;
							$ep->ssc_stop=$datas->$s1;
							$ep->ssc_start=$datas->$s2;
							$ep->ssc_continue=$datas->$s3;
							if(!($ep->ssc_stop='...'&&$ep->ssc_start='...'&&$ep->ssc_continue='...'))$ep->answers=1;
							$nt='na'.$evaluado_id;
							$ep->nota=$datas->$nt;
							$epid=$ep->id;
							$DB->update_record('evapares_evaluations', $ep, $bulk=false);
							for($preg_n=1;$preg_n<=$evapares->n_preguntas;$preg_n++){
								$ev_pr=new stdClass();
								$respuesta_pares=new stdClass();
								$respuesta_pares->evaluations_id=$epid;
								$rbtn='r'.$preg_n.'a'.$evaluado_id;
								$qstn_qry = $DB->get_records_sql('SELECT id FROM {evapares_questions} WHERE n_of_question=? AND evapares_id=?',
										array($preg_n,$cm->id));
								foreach($qstn_qry as $llave => $resultado){
									$qstn=$resultado->id;
								}
								$responde_qry = $DB->get_records_sql('SELECT id FROM {evapares_answers} WHERE number=? AND question_id=?',
										array($datas->$rbtn,$qstn));
								foreach($responde_qry as $llave => $resultado){
									$responde=$resultado->id;
								}
								$respuesta_pares->answers_id=$responde;
								$DB->insert_record('evapares_eval_has_answ', $respuesta_pares, $returnid=false, $bulk=false);
								echo "guardo la iteracion ".$i;
							}
						}
					}
				}
				
			}
			array_push($supa_data_sama,$data_chan);
		}
		$data_chan=array();
		array_push($data_chan,get_string('finalDate','mod_evapares'));
		$fin=$num+1;
		$itera_qry = $DB->get_records_sql('SELECT id FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=? limit 1', array($cm->id,$fin));
		foreach($itera_qry as $llave => $resultado){
			$itera=$resultado->id;
		}
		$vars = array('num'=>$evapares->total_iterations,
				"iduser"=>$iduser,
				'iter_id'=>$itera,
				'n_pregs'=>$evapares->n_preguntas,
				'n_resps'=>$evapares->n_respuestas,
				'ssc'=>$sscb,
				'cm_id'=>$cm->id
		);
		array_push($varrs,$vars);
		$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua_id=? limit 1', 
				array($itera,$iduser));
		$ans=false;
		foreach($answrs_qry as $answers){
			if($answers->answers==1)$ans=true;
		}
		$respondido='';
		if($ans)$respondio='pix/respondido.jpg';
		else $respondio='pix/norespondido.jpg';
		array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
		//revisar si esta activa la entrega final
		array_push($data_chan,'<img src="pix/respondible.jpg" View" style="width:15px;height:15px;">');
		array_push($data_chan,'<button id="f'.$fin.'" ><img src="pix/ver.jpg" id="'.$fin.'" View" style="width:15px;height:15px;"></button>');//editar para que sea el boton de jquery
		$addform = new evapares_evalu_usua(null, $varrs[$fin]);
		array_push($forms,$addform);
		var_dump($_SESSION['itra']);
		if( $forms[$fin]->is_cancelled() ){
			$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
			redirect($backtocourse);
		}
		else if($datas = $forms[$fin]->get_data()&&isset($_SESSION['itra'])){
			if($_SESSION['itra']==$fin){
				///////////////////////////////////////////////////////
				$eva_perso = $DB->get_records('evapares_evaluations',array('iterations_id'=>$itera, 'alu_evalua_id'=>$iduser,'alu_evaluado_id'=>$iduser));
				foreach($eva_perso as $llave => $ep){
					$ep->answers=1;
					$epid=$ep->id;
				}
				$DB->update_record('evapares_evaluations', $eva_perso[$epid], $bulk=false);
				for($preg_n=1;$preg_n<=$evapares->n_preguntas;$preg_n++){
					$ev_pr=new stdClass();
					$respuesta_perso=new stdClass();
					$respuesta_perso->evaluations_id=$epid;
					$rbtn='r'.$preg_n;
					$qstn_qry = $DB->get_records_sql('SELECT id FROM {evapares_questions} WHERE n_of_question=? AND evapares_id=?',
							array($preg_n,$cm->id));
					foreach($qstn_qry as $llave => $resultado){
						$qstn=$resultado->id;
					}
					$responde_qry = $DB->get_records_sql('SELECT id FROM {evapares_answers} WHERE number=? AND question_id=?',
							array($datas->$rbtn,$qstn));
					foreach($responde_qry as $llave => $resultado){
						$responde=$resultado->id;
					}
					$respuesta_perso->answers_id=$responde;
					$DB->insert_record('evapares_eval_has_answ', $respuesta_perso, $returnid=false, $bulk=false);
				}
				//////////////////////////////////////////////////////////
				$evaluado_qry= $DB->get_records_sql('SELECT id, alu_evaluado_id FROM {evapares_evaluations}
					WHERE alu_evalua_id = ? AND iterations_id=?',
						array($iduser,$itera));
				foreach($evaluado_qry as $llave => $evaluadox){
					$evaluado_id=$evaluadox->alu_evaluado_id;
					$evaluazion_id=$evaluadox->id;
					$eva_pares = $DB->get_records('evapares_evaluations',array('iterations_id'=>$itera, 'alu_evalua_id'=>$iduser,'alu_evaluado_id'=>$evaluado_id));
					foreach($eva_pares as $llave => $ep2){
							
						$s1='ssc_stop'.$evaluado_id;
						$s2='ssc_start'.$evaluado_id;
						$s3='ssc_continue'.$evaluado_id;
						$ep2->ssc_stop=$datas->$s1;
						$ep2->ssc_start=$datas->$s2;
						$ep2->ssc_continue=$datas->$s3;
						if(!($ep2->ssc_stop='...'&&$ep2->ssc_start='...'&&$ep2->ssc_continue='...'))$ep2->answers=1;
						$nt='na'.$evaluado_id;
						$ep2->nota=$datas->$nt;
						$epid=$ep2->id;
						$DB->update_record('evapares_evaluations', $ep2, $bulk=false);
						for($preg_n=1;$preg_n<=$evapares->n_preguntas;$preg_n++){
							$ev_pr=new stdClass();
							$respuesta_pares=new stdClass();
							$respuesta_pares->evaluations_id=$epid;
							$rbtn='r'.$preg_n.'a'.$evaluado_id;
							$qstn_qry = $DB->get_records_sql('SELECT id FROM {evapares_questions} WHERE n_of_question=? AND evapares_id=?',
									array($preg_n,$cm->id));
							foreach($qstn_qry as $llave => $resultado){
								$qstn=$resultado->id;
							}
							$responde_qry = $DB->get_records_sql('SELECT id FROM {evapares_answers} WHERE number=? AND question_id=?',
									array($datas->$rbtn,$qstn));
							foreach($responde_qry as $llave => $resultado){
								$responde=$resultado->id;
							}
							$respuesta_pares->answers_id=$responde;
							$DB->insert_record('evapares_eval_has_answ', $respuesta_pares, $returnid=false, $bulk=false);
							echo "guardo la iteracion final";
						}
					}
				}
			}
		}
		array_push($supa_data_sama,$data_chan);
		$table->data = $supa_data_sama;
		echo html_writer::table($table);
		for($t=0;$t<=$fin;$t++){
			echo html_writer::start_tag('div',array( 'id'=>'t'.$t, 'class'=>'hide formulario'));
			$forms[$t]->display();
			echo html_writer::end_tag('div');
		}
// 		$iduser=$USER->id;
// 		$vars=array('num'=>$evapares->total_iterations,"cmid"=>$cmid,"iduser"=>$iduser);//iduser hay que saber de donde
// 		$addform = new evapares_evalu_usua(null,$vars);//editars
// 		echo $OUTPUT->header();
// 		$addform->display();
// 		echo $OUTPUT->footer();
		
		
	}
		elseif($mode = 'resultados'){
	include('results_tab.php');
}
	
}
echo $OUTPUT->footer();
		
 	}
		