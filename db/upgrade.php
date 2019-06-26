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

defined('MOODLE_INTERNAL') || die;

function xmldb_confman_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017091104) {
        $table = new xmldb_table('confman');
        $fields = array(
            new xmldb_field('event_organizer', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contents'),
            new xmldb_field('event_contact', XMLDB_TYPE_TEXT, null, null, null, null, null, 'event_organizer'),
            new xmldb_field('targetgroups', XMLDB_TYPE_TEXT, null, null, null, null, null, 'event_contact'),
            new xmldb_field('types', XMLDB_TYPE_TEXT, null, null, null, null, null, 'targetgroups')
        );
        foreach($fields AS $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2017091104, 'confman');
    }
    if ($oldversion < 2019041000) {
        $table = new xmldb_table('confman');
        $fields = array(
            new xmldb_field('mail_contributor_creation', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'types'),
            new xmldb_field('mail_contributor_update', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'mail_contributor_creation'),
            new xmldb_field('mail_contributor_files', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'mail_contributor_update'),
            new xmldb_field('mail_organizer_creation', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'mail_contributor_files'),
            new xmldb_field('mail_organizer_update', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'mail_organizer_creation'),
            new xmldb_field('mail_organizer_files', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'mail_organizer_update'),
        );
        foreach($fields AS $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        $dbman->drop_field($table, new xmldb_field('contents', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'description'));
        upgrade_mod_savepoint(true, 2019041000, 'confman');
    }
    if ($oldversion < 2019041001) {
        $table = new xmldb_table('confman');
        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'description');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'name');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'intro');
        }

        $field = new xmldb_field('contents', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'intro');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        // Try to convert old file uploads as we now use the course module context.
        $module = $DB->get_record('modules', array('name' => 'confman'));
        $instances = $DB->get_records('course_modules', array('module' => $module->id));
        foreach ($instances AS $instance) {
            $coursecontext = context_course::instance($instance->course);
            $modulecontext = context_module::instance($instance->id);
            $confman = $DB->get_record('confman', array('id' => $instance->instance));
            $confman->cmid = $instance->id;
            $DB->update_record('confman', $confman);
            $files = $DB->get_records('files', array('contextid' => $coursecontext->id, 'component' => 'mod_confman', 'filearea' => 'content'));
            foreach ($files AS $file) {
                $file->contextid = $modulecontext->id;
                $DB->update_record('files', $file);
            }
        }
        upgrade_mod_savepoint(true, 2019041001, 'confman');
    }

    return true;
}
