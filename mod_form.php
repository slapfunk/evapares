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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_evapares_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $COURSE;
		$PAGE->requires->jquery();
        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'ev_name', 'Nombre', array('size' => '64'));
        
            $mform->setType('ev_name', PARAM_TEXT);
        
        $mform->addRule('ev_name', null, 'required', null, 'client');
        $mform->addRule('ev_name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('ev_name', 'evaparesname', 'evapares');//lang

        // Adding the standard "intro" and "introformat" fields.
        
//      if($CFG->version > 2014111008) {
//             $this->standard_intro_elements('hola');
//         } else {
//             $this->add_intro_editor();
//         }
      
   $opciones = array(0,1,2,3,4,5,6,7,8,9,10);

        $mform->addElement('checkbox', 'ssc','Agregar SSC');
        
        $mform->addElement('select', 'total_iterations','Cantidad de Entregas Parciales (Sin incluir entrega inicial y final)', $opciones);
       
        $mform->addElement('hidden', 'course_id',$COURSE->id);
        $mform->setType('course_id', PARAM_INT);
        

                

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();


        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}

