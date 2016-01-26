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
 *
 * @package mod
 * @subpackage emarking
 * @copyright Hans Jeria (hansjeria@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config.php");
require_once ($CFG->libdir . "/formslib.php");
class evapares_evalu_usua extends moodleform {
	
	function definition() {
	
		global $DB,$COURSE;
		//$iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid));

		$mform = $this->_form;
		$instance = $this->_customdata;
		$num=$instance	['num'];
		$iduser =$instance['iduser'];
		$iter_id=$instance['iter_id'];
		$n_pregs=$instance['n_pregs'];//$evapares->n_preguntas
		$n_resps=$instance['n_resps'];//$evapares->n-respuestas
		$ssc_bool=$instance['ssc'];//$evapares->ssc
		$cm_id=$instance['cm_id'];//$cm->id
		$fin=$num+1;
		$notas= array();
		for($opc_notas=0;$opc_notas<=12;$opc_notas++){
			$notas[]=1+($opc_notas*0.5);
		}
		$n_itera_qry= $DB->get_records_sql('SELECT n_iteration FROM {evapares_iterations} WHERE id = ?', 
				array($iter_id));
		foreach($n_itera_qry as $llave => $resultado){
			$n_itera=$resultado->n_iteration;
		}
		if($n_itera==0||$n_itera==$fin){
			$mform->addElement('header', 'eva_perso', 'Evaluación personal');//tipo, id, string
			for($preg_n=1;$preg_n<=$n_pregs;$preg_n++){
				$pregtext_qry= $DB->get_records_sql('SELECT id, text FROM {evapares_questions} WHERE evapares_id = ? AND n_of_question=?',
						array($cm_id,$preg_n));
				foreach($pregtext_qry as $llave => $resultado){
					$pregid=$resultado->id;
					$pregtext=$resultado->text;
				}
				$mform->addElement('static','pregunta'.$preg_n ,$preg_n.':','<h5>'.$pregtext.'</h5>');
				$radioarray=array();
				for($resp_x=1;$resp_x<=$n_resps;$resp_x++){
					$resptext_qry= $DB->get_records_sql('SELECT text FROM {evapares_answers} WHERE question_id = ? AND number=?',
							array($pregid,$resp_x));
					foreach($resptext_qry as $llave => $resultado){
						$resptext=$resultado->text;
					}
					$radioarray[] =& $mform->createElement('radio', 'r'.$preg_n,'' , $resptext, $resp_x, '');
				}
				$mform->addGroup($radioarray, 'radioar'.$preg_n, '', array(' '), false);
				$mform->setDefault('pr'.$preg_n, 1);
			}
		}
		if($n_itera>0){
			$mform->addElement('header', 'eva_pares', 'Evaluación de pares');
			$evaluado_qry= $DB->get_records_sql('SELECT alu_evaluado_id FROM {evapares_evaluations}
					WHERE alu_evalua_id = ? AND iterations_id=?',
							array($iduser,$iter_id));
			foreach($evaluado_qry as $llave => $resultado){
				$evaluado=$resultado->alu_evaluado_id;
				$evaluado_name_qry= $DB->get_records_sql('SELECT firstname, lastname FROM {user} WHERE id = ?',
						array($evaluado));
				foreach($evaluado_name_qry as $llave => $resultado){
					$evaluado_name=$resultado->firstname." ".$resultado->lastname;
				}								//$evaluado=id, $evaluado_name=firstname lastname
				$mform->addElement('html', '<hr>');
				$mform->addElement('html', '<hr>');
				$mform->addElement('static',$evaluado ,'<h3>'.'Alumno a evaluar:'.'<h3>','<h3>'.$evaluado_name.'</h3>');
				if($ssc_bool){
					$mform->addElement('textarea', "ssc_stop".$evaluado,'STOP', 'wrap="virtual" rows="2" cols="60"');
					$mform->setType("ssc_stop".$evaluado, PARAM_TEXT);
					$mform->setDefault("ssc_stop".$evaluado,'...');
					$mform->addElement('textarea', "ssc_start".$evaluado,'START', 'wrap="virtual" rows="2" cols="60"');
					$mform->setType("ssc_start".$evaluado, PARAM_TEXT);
					$mform->setDefault("ssc_start".$evaluado,'...');
					$mform->addElement('textarea', "ssc_continue".$evaluado,'CONTINUE', 'wrap="virtual" rows="2" cols="60"');
					$mform->setType("ssc_continue".$evaluado, PARAM_TEXT);
					$mform->setDefault("ssc_continue".$evaluado,'...');
				}
				for($preg_n=1;$preg_n<=$n_pregs;$preg_n++){
					$pregtext_qry= $DB->get_records_sql('SELECT id, text FROM {evapares_questions} WHERE evapares_id = ? AND n_of_question=?',
							array($cm_id,$preg_n));
					foreach($pregtext_qry as $llave => $resultado){
						$pregid=$resultado->id;
						$pregtext=$resultado->text;
					}
					$mform->addElement('static','pregunta'.$preg_n ,$preg_n.':','<h5>'.$pregtext.'</h5>');
					$radioarray=array();
					for($resp_x=1;$resp_x<=$n_resps;$resp_x++){
						$resptext_qry= $DB->get_records_sql('SELECT text FROM {evapares_answers} WHERE question_id = ? AND number=?',
								array($pregid,$resp_x));
						foreach($resptext_qry as $llave => $resultado){
							$resptext=$resultado->text;
						}
						$radioarray[] =& $mform->createElement('radio', 'r'.$preg_n.'a'.$evaluado,'' , $resptext, $resp_x, '');
					}
					$mform->setDefault('r'.$preg_n.'a'.$evaluado,1);
					$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
					$mform->setDefault('radioar'.$preg_n.'a'.$evaluado, 1);
				}

				$mform->addElement('select', 'na'.$evaluado,'NOTA:', $notas);
				$mform->setDefault('na'.$evaluado, 1);
			}
		}
		$mform->addElement('hidden', 'id',$cm_id);
		$mform->setType('id', PARAM_INT);
				
		$this->add_action_buttons();
	}
			 		
}
		 		