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
 * @copyright  2019 Digital Education Society (http://www.dibig.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

class comment_form extends moodleform {
    static $accepted_types = '';
    static $areamaxbytes = 10485760;
    static $maxbytes = 1024*1024;
    static $maxfiles = 1;
    static $subdirs = 0;

    function definition() {
        global $CFG, $COURSE, $DB, $OUTPUT, $USER;
        // Item gets customized to particular event / item.
        global $event, $item;

        $editoroptions = array('subdirs'=>0, 'maxbytes'=>0, 'maxfiles'=>0,
                               'changeformat'=>0, 'context'=>null, 'noclean'=>0,
                               'trusttext'=>0, 'enable_filemanagement' => false);

        $mform = $this->_form;
        // Attention, this is the item-id, not comment-id!
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'event', 0);
        $mform->setType('event', PARAM_INT);
        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_TEXT);
        $mform->addElement('hidden', 'created', 0);
        $mform->setType('created', PARAM_INT);

        $mform->addElement('header', 'comments', get_string('comment:add', 'confman'));

        $mform->addElement('editor', 'comment', get_string('comment', 'confman'), $editoroptions);
        $mform->setType('comment', PARAM_RAW);

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();
        if (empty(strip_tags($data['comment']['text']))) {
            $errors['comment'] = get_string('comment:missing', 'confman');
        }
        return $errors;
    }
}
