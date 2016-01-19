<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

global $CFG, $DB, $OUTPUT, $USER;

$iterations = $DB->get_records("evapares_iterations", array('evapares_id'=>$cmid));

$resultados = $DB->get_records("evapares_evaluations", array('alu_evaluado_id'=>$USER->id),'iterations_id ASC');

$headings = array('Stop','Start','Continue');

$n_table = 0;
 		
foreach($resultados as $param){
	
 	if($param->alu_evalua_id != $param->alu_evaluado_id){
 		
 		if($param->iterations_id != $n_table){
 			if($n_table != 0){
 				
 				$table->data = $supa_data_sama;
 				echo $iterations[$param->iterations_id - 1]->evaluation_name;
 				//COMPROBAR CON FECHA
 				echo html_writer::table($table);
 				
 			}
		
 			$table = new html_table();
 			$table->head = $headings ;
 			$supa_data_sama=array();
 			$data_chan=array();
 			
 			array_push($data_chan,$param->ssc_stop);
 			array_push($data_chan,$param->ssc_start);
 			array_push($data_chan,$param->ssc_continue);
 			array_push($supa_data_sama,$data_chan);
 			
 			$data_chan=array();
 			
 			$n_table = $param->iterations_id;
 			
 		}else{
 			
 			array_push($data_chan,$param->ssc_stop);
 			array_push($data_chan,$param->ssc_start);
 			array_push($data_chan,$param->ssc_continue);
 			array_push($supa_data_sama,$data_chan);
 			
 			$data_chan=array();
 			
 		}
 	
	}
	
}
$table->data = $supa_data_sama;
echo $iterations[$param->iterations_id]->evaluation_name;
echo html_writer::table($table);



