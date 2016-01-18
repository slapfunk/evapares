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
require_once('/forms/forms_v.php');

global $CFG, $DB, $OUTPUT; 

$action = optional_param("action", "view", PARAM_TEXT);
$cmid = required_param('id', PARAM_INT);

if(! $cm = get_coursemodule_from_id('evapares', $cmid))
{print_error('cm'." id: $cmid");}

if(! $evapares = $DB->get_record('evapares', array('id' => $cm->instance)))
{print_error('evapares'." id: $cmid");}

if(! $course = $DB->get_record('course', array('id' => $cm->course)))
{print_error('course'." id: $cmid");}
$context = context_module::instance($cm->id);

require_login();

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
	
	echo $OUTPUT->header();
	
	if(!$grupos = $DB->get_records("groups", array('courseid'=>$course->id))){
		
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
	
	if( $addform->is_cancelled() ){
		$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
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
			
				$recr->number = $i.'.'.$j;
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
if(has_capability('mod/evapares:courseevaluations', $context) && $action == "add"){

	$addform->display();

}
elseif(has_capability('mod/evapares:courseevaluations', $context) && $action == "view"){
	
	echo"holi, usted es profe";
}

elseif(has_capability('mod/evapares:myevaluations', $context) && $action == "view"){
	if(!isset($currenttab)){
		$currenttab='tb1';
	}
	$tbz = array();
	$tabz=array();
	$inactive = array();
	$activated = array();
	$inactive = array('7');
	$activated = array('tb1');
	$tbz[] = new tabobject('tb1',new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'evaluation')), 'estocambiaenlang');
	$tbz[] = new tabobject('tb2',new moodle_url($CFG->wwwroot.'/mod/evapares/view.php',array('mode'=>'resultados')),'Restocambiaenlang');
	$tabz[]=$tbz;
	print_tabs($tabz,$currenttab,$inactive, $activated);
	if(!isset($_REQUEST['mode'])){
		$mode='evaluation';
	}
	else if(isset($_REQUEST['mode'])){
		$mode=$_REQUEST['mode'];
	}
	if($mode=='evaluation'){		
	
		$table = new html_table();
		$table->head = array('buscalang', 'buscalang', 'buscalang', 'buscalang');
		$supa_data_sama=array();
		$data_chan=array();
		array_push($data_chan,'cambiarlang');
		$insta_qry=$DB->get_records_sql('SELECT instance FROM {course_modules} WHERE id = ?', array($cmid));
		foreach($insta_qry as $llave => $resultado){
			$insta=$resultado;
		}
		$itera_qry = $DB->get_records_sql('SELECT id FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=?', array($insta,'0'));
		foreach($itera_qry as $llave => $resultado){
			$itera=$resultado;
		}
		$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua=?', array($itera,$iduser));
		$ans=false;
		foreach($answrs_qry as $llave=> $answers){
			if($answers==1)$ans=true;
		}
		$respondido='';
		if($ans)$respondio='pix/respondido.jpg';
		else $respondio='pix/norespondido.jpg';
		array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
		//revisar si esta activa la entrega inicial
		array_push($data_chan,'<img src="pix/respondible.jpg" View" style="width:15px;height:15px;">');
		array_push($data_chan,'<img src="pix/ver.jpg" View" style="width:15px;height:15px;">');//editar para que sea el boton de jquery
		array_push($supa_data_sama,$data_chan);
		for($i=1;$i<=$num;$i++){
			$data_chan=array();
				
			$itera_qry = $DB->get_records_sql('SELECT id,evaluation_name FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=?', array($insta,$i));
			foreach($itera_qry as $llave => $resultado){
				$itera=$resultado['id'];
				$nomitera=$resultado['evaluation_name'];
			}
			array_push($data_chan,$nomitera);
			$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua=? ', array($itera,$iduser));
			$ans=false;
			foreach($answrs_qry as $llave=> $answers){
				if($answers==1)$ans=true;
			}
			$respondido='';
			if($ans)$respondio='pix/respondido.jpg';
			else $respondio='pix/norespondido.jpg';
			array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
			//revisar si esta activa la entrega inicial
			array_push($data_chan,'<img src="pix/respondible.jpg" View" style="width:15px;height:15px;">');
			array_push($data_chan,'<img src="pix/ver.jpg" View" style="width:15px;height:15px;">');//editar para que sea el boton de jquery
			array_push($supa_data_sama,$data_chan);
		}
		$data_chan=array();
		array_push($data_chan,'cambiarlang');
		$fin=$num+1;
		$itera_qry = $DB->get_records_sql('SELECT id FROM {evapares_iterations} WHERE evapares_id = ? AND n_iteration=?', array($insta,$fin));
		$itera=$itera_qry['id'];
		$answrs_qry = $DB->get_records_sql('SELECT answers FROM {evapares_evaluations} WHERE iterations_id = ? And alu_evalua=?', array($itera,$iduser));
		$ans=false;
		var_dump($answrs_qry);
		foreach($answrs_qry as $answers){
			if($answers==1)$ans=true;
		}
		$respondido='';
		if($ans)$respondio='pix/respondido.jpg';
		else $respondio='pix/norespondido.jpg';
		array_push($data_chan,'<img src="'.$respondio.'" style="width:15px;height:15px;">');
		//revisar si esta activa la entrega final
		array_push($data_chan,'<img src="pix/respondible.jpg" View" style="width:15px;height:15px;">');
		array_push($data_chan,'<img src="pix/ver.jpg" View" style="width:15px;height:15px;">');//editar para que sea el boton de jquery
		array_push($supa_data_sama,$data_chan);
		echo html_writer::table($table);
// 		$iduser=$USER->id;
// 		$vars=array('num'=>$evapares->total_iterations,"cmid"=>$cmid,"iduser"=>$iduser);//iduser hay que saber de donde
// 		$addform = new evapares_evalu_usua(null,$vars);//editars
// 		echo $OUTPUT->header();
// 		$addform->display();
// 		echo $OUTPUT->footer();
		
		
	}
	else{
		include('results_tab.php');
	}

}
echo $OUTPUT->footer();
		
 	}