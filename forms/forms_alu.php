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
		$preg =$instance['preg'];
		$resp =$instance['resp'];
		
		$table = new html_table();
		$table->head = array('buscalang', 'buscalang', 'buscalang', 'buscalang');
		$supa_data_sama=array();
		$data_chan=array();
		array_push($data_chan,'cambiarlang');
		//revisar si se ha respondido o no la entrega inicial
		array_push($data_chan,'<img src="visto o no.jpg" style="width:15px;height:15px;">');
		//revisar si esta activa la entrega inicial
		array_push($data_chan,'<img src="activo o no.jpg" View" style="width:15px;height:15px;">');
		array_push($data_chan,'boton');//visualizador
		array_push($supa_data_sama,$data_chan);
		//ver cuantas iteraciones intermedias son
		
		//array_push($hg,meter);
		
		
		
		$table->data = array(
    		array('Harry Potter', '76%', 'Getting better'),
    		array('Rincewind', '89%', 'Lucky as usual'),
    		array('Elminster Aumar', '100%', 'Easy when you know everything!')
		);
		echo html_writer::table($table);
		
		
		
		
		
		
		
		//for idem iterations
		$html=$html.'<tr><td>'.'cambiar esto en el lang'.'</td>';
		//revisar si se ha respondido o no la entrega final
		$html=$html.'<td><img src="visto o no.jpg"style="width:15px;height:15px;"></td>';
		//revisar si esta activa la entrega final
		$html=$html.'<td><img src="activo o no.jpg" alt="Mountain View" style="width:15px;height:15px;"></td>';
		$html=$html.'<td></td></tr></table>';//boton magio maderfacker
		
		$mform->addElement('html',$html);
		
		for($i = 0; $i <= $num + 1; $i++){
			if($i == 0){
				$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Inicial');
			 	$mform->setType('NE'.$i, PARAM_TEXT);
			 	$mform->addElement('date_time_selector', 'FE'.$i,get_string('personalEvalInitial','mod_evapares'), array('optional'=>true));
			 	$mform->setDefault('available', 0);
			} 
			elseif($i > 0 && $i < $num + 1){
				$mform->addElement('text', 'NE'.$i,get_string('DeliverableName', 'mod_evapares').$i);
			 	$mform->setType('NE'.$i, PARAM_TEXT);
			 	$mform->addElement('date_time_selector', 'FE'.$i,get_string('dueDate','mod_evapares').$i, array('optional'=>true));
			 	$mform->setDefault('available', 0);
			 } 
			 elseif($i == $num +1){
			 	$mform->addElement('hidden', 'NE'.$i, 'Evaluacion Final');
			 	$mform->setType('NE'.$i, PARAM_TEXT);
			 	$mform->addElement('date_time_selector', 'FE'.$i,get_string('finalDate', 'mod_evapares'), array('optional'=>true));
			 	$mform->setDefault('available', 0);
			 }
		}
		$mform->addElement('header', 'Detalle_Preguntas', get_string('DeliverableDetails','mod_evapares'));
		for($j = 1; $j <= $preg; $j++){
			$mform->addElement('textarea', 'P'.$j,get_string('question','mod_evapares').$j, 'wrap="virtual" rows="5" cols="60"');
			$mform->setType('P'.$j, PARAM_TEXT);
			for($h = 1; $h <= $resp; $h++){
				$mform->addElement('text', $j.'.'.$h,get_string('option','mod_evapares').$j.'.'.$h);
			 	$mform->setType($j.'.'.$h, PARAM_TEXT);
			}
		}
		$mform->addElement('hidden', 'id',$cmid);
		$mform->setType('id', PARAM_INT);
		$this->add_action_buttons();
	}
			 		
}
			 		