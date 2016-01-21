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
		$n_itera_qry= $DB->get_records_sql('SELECT n_iteration FROM {evapares_iterations} WHERE id = ?', 
				array($iter_id));
		foreach($n_itera_qry as $llave => $resultado){
			$n_itera=$resultado->n_iteration;
		}
		if($n_itera==0||$n_itera==$fin){
			$mform->addElement('header', 'eva_perso', 'cambiaellang');
			for($p=1;$p<=$n_pregs;$p++){
				$pregtext_qry= $DB->get_records_sql('SELECT id, text FROM {evapares_questions} WHERE evapares_id = ? AND n_of_question=?',
						array($cm_id,$p));
				foreach($pregtext_qry as $llave => $resultado){
					$pregid=$resultado->id;
					$pregtext=$resultado->text;
				}
				$mform->addElement('static','pregunta'.$p ,'cambiaenlang',$pregtext);
				$radioarray=array();
				for($r=1;$r<=$n_resps;$r++){
					$resptext_qry= $DB->get_records_sql('SELECT text FROM {evapares_answers} WHERE question_id = ? AND number=?',
							array($pregid,$r));
					foreach($resptext_qry as $llave => $resultado){
						$resptext=$resultado->text;
					}
					$radioarray[] =& $mform->createElement('radio', 'pr'.$p,'' , $resptext, $r, '');
				}
				$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
				$mform->setDefault('pr'.$p, 1);
			}
		}
		if($n_itera>0){
			$mform->addElement('header', 'eva_pares', 'cambiaellang');
			$evaluado_qry= $DB->get_records_sql('SELECT alu_evaluado_id FROM {evapares_evaluations}
					WHERE alu_evalua_id = ? AND iterations_id=?',
							array($iduser,$iter_id));
			foreach($evaluado_qry as $llave => $resultado){
				$evaluado=$resultado->alu_evaluado_id;
				$evaluado_name_qry= $DB->get_records_sql('SELECT firstname, lastname FROM {user} WHERE id = ?',
						array($evaluado));
				foreach($evaluado_name_qry as $llave => $resultado){
					$evaluado_name=$resultado->firstname." ".$resultado->lastname;
				}
				$mform->addElement('static',$evaluado ,'cambiaenlang',$evaluado_name);
				if($ssc_bool){
					$mform->addElement('textarea', "ssc_stop".$evaluado,'STOP', 'wrap="virtual" rows="5" cols="60"');
					$mform->setType("ssc_stop".$evaluado, PARAM_TEXT);
					$mform->addElement('textarea', "ssc_start".$evaluado,'START', 'wrap="virtual" rows="5" cols="60"');
					$mform->setType("ssc_start".$evaluado, PARAM_TEXT);
					$mform->addElement('textarea', "ssc_continue".$evaluado,'CONTINUE', 'wrap="virtual" rows="5" cols="60"');
					$mform->setType("ssc_start".$evaluado, PARAM_TEXT);
				}
				for($p=1;$p<=$n_pregs;$p++){
					$pregtext_qry= $DB->get_records_sql('SELECT id, text FROM {evapares_questions} WHERE evapares_id = ? AND n_of_question=?',
							array($cm_id,$p));
					foreach($pregtext_qry as $llave => $resultado){
						$pregid=$resultado->id;
						$pregtext=$resultado->text;
					}
					$mform->addElement('static','pregunta'.$p ,'cambiaenlang',$pregtext);
					$radioarray=array();
					for($r=1;$r<=$n_resps;$r++){
						$resptext_qry= $DB->get_records_sql('SELECT text FROM {evapares_answers} WHERE question_id = ? AND number=?',
								array($pregid,$r));
						foreach($resptext_qry as $llave => $resultado){
							$resptext=$resultado->text;
						}
						$radioarray[] =& $mform->createElement('radio', 'pr'.$p.'al'.$evaluado,'' , $resptext, $r, '');
					}
					$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
					$mform->setDefault('pr'.$p.'al'.$evaluado, 1);
				}
			}
		}
		$mform->addElement('hidden', 'id',$cm_id);
		$mform->setType('id', PARAM_INT);
				
		$this->add_action_buttons();
	}
			 		
}
		 		