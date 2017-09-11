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
 * @package    mod_confman
 * @copyright  2017 Digital Education Society (http://www.dibig.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
 
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/confman/lib.php');
 
class mod_confman_mod_form extends moodleform_mod {
 
    function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;
        
        $mform->addElement('text', 'name', get_string('event:name', 'confman'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        
        $mform->addElement('text', 'event_organizer', get_string('event:organizer', 'confman'), array('size'=>'64'));
        $mform->setType('event_organizer', PARAM_TEXT);
        $mform->addRule('event_organizer', null, 'required', null, 'client');
  
        $mform->addElement('text', 'event_contact', get_string('event:contact', 'confman'), array('size'=>'64'));
        $mform->setType('event_contact', PARAM_TEXT);
        $mform->addRule('event_contact', null, 'required', null, 'client');

        $utime = new DateTime("now", core_date::get_user_timezone_object());
        $utz = $utime->getTimezone();
        $startend_args = array(
               'startyear'=>date("Y"),
               'stopyear'=>date("Y")+5,
               'timezone'=>floor($utz->getOffset(new DateTime("now"))/60/60),
               'step'=>5
            );
        $mform->addElement('date_time_selector', 'submissionstart', get_string('event:submissionstart', 'confman'), $startend_args);
        //$mform->setType('title', PARAM_TEXT);
        $mform->addRule('submissionstart', null, 'required', null, 'client');       
        
        $mform->addElement('date_time_selector', 'submissionend', get_string('event:submissionend', 'confman'), $startend_args);
        //$mform->setType('title', PARAM_TEXT);
        $mform->addRule('submissionend', null, 'required', null, 'client');
 
        $description_args = array(
              'subdirs'=>0,
              'maxbytes'=>0,
              'maxfiles'=>0,
              'changeformat'=>0,
              'context'=>null,
              'noclean'=>0,
              'trusttext'=>0,
              'enable_filemanagement' => false,
            );
        $mform->addElement('editor', 'description', get_string('event:description', 'confman'),$description_args);
        $mform->setType('description', PARAM_RAW);
        
        $mform->addElement('textarea', 'targetgroups', get_string('event:targetgroups', 'confman'),array('style' => 'width: 100%'));
        $mform->setType('targetgroups', PARAM_RAW);
        $mform->setDefault('targetgroups',"digi.komp 4#Primarstufe\ndigi.komp 8#Sekundarstufe I\ndigi.komp 12#Sekundarstufe II\ndigi.komp P#Lehrer/innenfortbildung");
        
        $mform->addElement('textarea', 'types', get_string('event:types', 'confman'),array('style' => 'width: 100%'));
        $mform->setType('types', PARAM_RAW);
        $mform->setDefault('types',"Vortrag\nWorkshop");
 
        //$mform->addElement('filepicker', 'logo', get_string('event:logo','confman'), null, array('maxbytes' => 50*1024, 'accepted_types' => 'image'));
 
        $this->standard_coursemodule_elements();
 
        $this->add_action_buttons();
    }
}

