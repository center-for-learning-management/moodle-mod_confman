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
require_once($CFG->dirroot . "/mod/confman/lib.php");

class item_form extends moodleform {
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
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'isitemform', 1);
        $mform->setType('isitemform', PARAM_INT);
        $mform->addElement('hidden', 'event', 0);
        $mform->setType('event', PARAM_INT);
        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_TEXT);
        $mform->addElement('hidden', 'created', 0);
        $mform->setType('created', PARAM_INT);
        $mform->addElement('hidden', 'modified', 0);
        $mform->setType('modified', PARAM_INT);

        $_event = new mod_confman_event($event->id);
        $mform->addElement('header', 'eventname', $event->name);
        $mform->addElement('html', $event->html('_public'));

        $mform->addElement('header', 'personaldata', get_string('item:section:personaldata', 'confman'));
        $mform->addElement('text', 'title_pre', get_string('item:title_pre', 'confman'));
        $mform->setType('title_pre', PARAM_TEXT);
        $mform->addElement('text', 'firstname', get_string('item:firstname', 'confman'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', NULL, 'required');
        $mform->addElement('text', 'lastname', get_string('item:lastname', 'confman'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', NULL, 'required');
        $mform->addElement('text', 'title_post', get_string('item:title_post', 'confman'));
        $mform->setType('title_post', PARAM_TEXT);
        $mform->addElement('text', 'organization', get_string('item:organization', 'confman'));
        $mform->setType('organization', PARAM_TEXT);
        $mform->addRule('organization', NULL, 'required');

        if (empty($item->id) || $item->can_manage) {
            $mform->addElement('text', 'email', get_string('item:email', 'confman'));
            $mform->setType('email', PARAM_TEXT);
            $mform->addRule('email', NULL, 'email', null, 'server');
            $mform->addRule('email', NULL, 'required');

            if (empty($item->id)) {
                $mform->addElement('text', 'email2', get_string('item:email2', 'confman'));
                $mform->setType('email2', PARAM_TEXT);
                $mform->addRule('email2', NULL, 'required');
                $mform->addRule(array('email2', 'email'), get_string('item:invalidvalue', 'confman'), 'compare', 'eq', 'server');
            }
        } else {
            $mform->addElement('html', $OUTPUT->render_from_template('mod_confman/form_row', array('label' => get_string('item:email', 'confman'), 'content' => $item->data->email)));
        }

        $mform->addElement('header', 'yoursubmission', get_string('item:section:yoursubmission', 'confman'));
        $mform->addElement('text', 'title', get_string('item:title', 'confman'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', NULL, 'required');

        $cnt = 0;
        $boxes = array();
        foreach($event->types AS $type) {
            $boxes[] = $mform->createElement('checkbox', 'type_' . $cnt, $type, null, array('value' => $type));
            $mform->setType('type_' . $cnt, PARAM_BOOL);
            if (isset($item) && in_array($type, $item->data->types)) {
                $mform->setDefault('type_' . $cnt, 1);
            }
            $cnt++;
        }
        $mform->addGroup($boxes, 'type', get_string('item:type', 'confman'), null, false);
        $boxes = array();
        $cnt = 0;
        foreach($event->targetgroups AS $target) {
            $boxes[] = $mform->createElement('checkbox', 'targetgroup_' . $cnt, $target["targetgroup"] . (!empty($target["description"]) ? ' (<i>' . $target["description"] . '</i>)' : ''), null, array('value' => $target["targetgroup"]));
            $mform->setType('targetgroup_' . $cnt, PARAM_BOOL);
            if (isset($item) && in_array($target["targetgroup"], $item->data->targetgroups)) {
                $mform->setDefault('targetgroup_' . $cnt, 1);
            }
            $cnt++;
        }
        $mform->addGroup($boxes, 'targetgroup', get_string('item:targetgroup', 'confman'), null, false);

        $mform->addElement('editor', 'description', get_string('item:description', 'confman'), $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', NULL, 'required');

        $mform->addElement('editor', 'memo', get_string('item:memo', 'confman'), $editoroptions);
        $mform->setType('memo', PARAM_RAW);

        // We only need recaptcha or alternative when this is a new item.
        if (empty($item->id)) {
            if (get_config('auth_email', 'recaptcha') && !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey)) {
                //recaptcha is enabled
                $mform->addElement('recaptcha', 'recaptcha');
                $mform->closeHeaderBefore('recaptcha');
            } else {
                $itemcheck = cache::make('mod_confman', 'itemcheck');
                $calc = $itemcheck->get('itemcheck');
                if (empty($calc)) {
                    $calcs = array("+" , "-");
                    $z1 = 0; $z2 = 0; $calc = "+";
                    while (empty(eval("return $z1$calc$z2;"))) {
                        $z1 = rand(10, 20);
                        $z2 = rand(1, 10);
                        $calc = rand(0, count($calcs) - 1);
                    }
                    $calc = $z1 . " " . $calcs[$calc] . " " . $z2;
                    $itemcheck->set('itemcheck', $calc);
                }
                $checkresult = eval("return " . $calc . ";");
                $mform->addElement('hidden', 'checkhuman2', $checkresult);
                $mform->setType('checkhuman2', PARAM_INT);

                $mform->addElement('text', 'checkhuman', get_string('item:check', 'confman') . '<br />' . $calc . ' =');
                $mform->setType('checkhuman', PARAM_INT);
                $mform->addRule('checkhuman', NULL, 'required', '', 'server');
                $mform->addRule('checkhuman', NULL, 'nonzero', '', 'server');
                $mform->addRule(array('checkhuman', 'checkhuman2'), get_string('item:invalidvalue', 'confman'), 'compare', 'eq', 'server');
                $mform->closeHeaderBefore('checkhuman');
            }
        }

        $this->add_action_buttons();

        if ($item->id > 0) {
            $mform->addElement('header', 'yourfiles', get_string('item:files', 'confman'));
            $uniqid = md5(time());
            $mform->addElement('html',
                $OUTPUT->render_from_template('mod_confman/form_row',
                    array(
                        'label' => get_string('item:files', 'confman'),
                        'content' => implode('', array(
                            '<input type="file" value="' . get_string('item:file:upload') . '" multiple="multiple"',
                            '    onchange="var inp = this; require([\'mod_confman/main\'], function(MAIN) { MAIN.upload_file(inp, \'' . $CFG->wwwroot . '\', \'' . $uniqid . '\', ' . $item->id . ', \'' . optional_param('token', '', PARAM_TEXT) . '\'); });">',
                            '<div id="mod_confman_form-' . $uniqid . '" style="width: 100%;"></div>',
                        ))
                    )
                )
            );
            global $PAGE;
            $PAGE->requires->js_call_amd('mod_confman/main', 'upload_file_prepare', array($uniqid, $CFG->wwwroot, json_encode($item->get_files('array'), JSON_NUMERIC_CHECK)));
        }
    }
}
