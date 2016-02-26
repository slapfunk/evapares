<?php
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