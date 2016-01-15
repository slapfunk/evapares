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
	
		global $DB;

		$mform = $this->_form;
		$instance = $this->_customdata;
			 		
		$num = $instance['num'];
		$cmid =$instance['cmid'];
		$iduser =$instance['iduser'];
		
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
		
		$this->add_action_buttons();
	}
			 		
}
			 		