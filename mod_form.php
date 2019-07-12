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
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/confman/lib.php');

class mod_confman_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG, $COURSE, $DB, $OUTPUT, $USER;
        global $embedded, $eventid, $id, $token, $preview;

        $mform =& $this->_form;

        $mform->addElement('embedded', 'hidden', $embedded);
        $mform->addElement('eventid', 'hidden', $eventid);
        $mform->addElement('id', 'hidden', $id);
        $mform->addElement('token', 'hidden', $token);
        $mform->addElement('preview', 'hidden', $preview);

        $mform->addElement('text', 'name', get_string('event:name', 'confman'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'event_organizer', get_string('event:organizer', 'confman'), array('size' => '64'));
        $mform->setDefault('event_organizer', fullname($USER));
        $mform->setType('event_organizer', PARAM_TEXT);
        $mform->addRule('event_organizer', null, 'required', null, 'client');

        $mform->addElement('text', 'event_contact', get_string('event:contact', 'confman'), array('size' => '64'));
        $mform->setDefault('event_contact', $USER->email);
        $mform->setType('event_contact', PARAM_TEXT);
        $mform->addRule('event_contact', null, 'required', null, 'client');

        $mform->addElement('html', '<div class="form-group row fitem" style="text-align: center;"><div class="col-md-10">' . get_string('mail:contributor:notice', 'confman') . '</div></div>');
        $boxes = array();
        $boxes[] = $mform->createElement('advcheckbox', 'mail_organizer_creation', get_string('mail:organizer:creation', 'confman'));
        $mform->setType('mail_organizer_creation', PARAM_BOOL);
        $mform->setDefault('mail_organizer_creation' , 1);
        $boxes[] = $mform->createElement('advcheckbox', 'mail_organizer_update', get_string('mail:organizer:update', 'confman'));
        $mform->setType('mail_organizer_update', PARAM_BOOL);
        $mform->setDefault('mail_organizer_update' , 1);
        $boxes[] = $mform->createElement('advcheckbox', 'mail_organizer_files', get_string('mail:organizer:files', 'confman'));
        $mform->setType('mail_organizer_files', PARAM_BOOL);
        $mform->setDefault('mail_organizer_files' , 1);
        $mform->addGroup($boxes, 'mail_organizer', get_string('mail:organizer', 'confman'), null, false);

        $boxes = array();
        $boxes[] = $mform->createElement('advcheckbox', 'mail_contributor_creation', get_string('mail:contributor:creation', 'confman'));
        $mform->setType('mail_contributor_creation', PARAM_BOOL);
        $mform->setDefault('mail_contributor_creation' , 1);
        $boxes[] = $mform->createElement('advcheckbox', 'mail_contributor_update', get_string('mail:contributor:update', 'confman'));
        $mform->setType('mail_contributor_update', PARAM_BOOL);
        $mform->setDefault('mail_contributor_update' , 0);
        $boxes[] = $mform->createElement('advcheckbox', 'mail_contributor_files', get_string('mail:contributor:files', 'confman'));
        $mform->setType('mail_contributor_files', PARAM_BOOL);
        $mform->setDefault('mail_contributor_files' , 0);
        $mform->addGroup($boxes, 'mail_contributor', get_string('mail:contributor', 'confman'), null, false);

        $utime = new DateTime("now", core_date::get_user_timezone_object());
        $utz = $utime->getTimezone();
        $startendargs = array(
               'startyear' => date("Y"),
               'stopyear' => date("Y") + 5,
               'timezone' => floor($utz->getOffset(new DateTime("now")) / 60 / 60),
               'step' => 5,
               'optional' => 0,
            );
        $mform->addElement('date_time_selector', 'submissionstart', get_string('event:submissionstart', 'confman'), $startendargs);
        $mform->addRule('submissionstart', null, 'required', null, 'client');

        $mform->addElement('date_time_selector', 'submissionend', get_string('event:submissionend', 'confman'), $startendargs);
        $mform->addRule('submissionend', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('textarea', 'targetgroups', get_string('event:targetgroups', 'confman'),
                array('style' => 'width: 100%'));
        $mform->setType('targetgroups', PARAM_TEXT);
        $mform->setDefault('targetgroups', str_replace('*', "\n", get_string('defaults:targetgroups', 'confman')));

        $mform->addElement('textarea', 'types', get_string('event:types', 'confman'), array('style' => 'width: 100%'));
        $mform->setType('types', PARAM_TEXT);
        $mform->setDefault('types', str_replace('*', "\n", get_string('defaults:types', 'confman')));

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
