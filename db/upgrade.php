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

        // Define field event_organizer to be added to confman.
        $table = new xmldb_table('confman');
        $field1 = new xmldb_field('event_organizer', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contents');
        $field2 = new xmldb_field('event_contact', XMLDB_TYPE_TEXT, null, null, null, null, null, 'event_organizer');
        $field3 = new xmldb_field('targetgroups', XMLDB_TYPE_TEXT, null, null, null, null, null, 'event_contact');
        $field4 = new xmldb_field('types', XMLDB_TYPE_TEXT, null, null, null, null, null, 'targetgroups');

        // Conditionally launch add field event_organizer.
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        // Confman savepoint reached.
        upgrade_mod_savepoint(true, 2017091104, 'confman');
    }

    return true;
}
