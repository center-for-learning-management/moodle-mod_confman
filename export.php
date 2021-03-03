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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/confman/lib.php');

$format = optional_param('format', 'xlsx', PARAM_TEXT);

$event = required_param('event', PARAM_INT);
$event = new mod_confman_event($event);
require_login($event->course);
$PAGE->set_url(new moodle_url('/mod/confman/export.php', array('event' => $event->id)));
$PAGE->set_context(context_course::instance($event->course));
$PAGE->set_pagelayout('popup');
if ($event->can_manage) {
    $lines = array();
    $items = array();
    $fields = array('id', 'approved', 'title_pre', 'firstname', 'lastname', 'title_post', 'organization', 'email', 'title', 'targetgroups', 'types', 'description', 'memo');

    $ids = optional_param_array('ids', null, PARAM_INT);
    foreach ($ids AS $z => $id) {
        $item = new mod_confman_item($id);
        $items[$z] = $item->data;
        $lines[$z] = array(
            $item->id, $item->data->approved, $item->data->title_pre, $item->data->firstname,
            $item->data->lastname, $item->data->title_post, $item->data->organization,
            $item->data->email, $item->data->title, implode(', ', $item->data->targetgroups),
            implode(', ', $item->data->types), strip_tags($item->data->description),
            strip_tags($item->data->memo));
    }

    switch ($format) {
        case 'xlsx':
            // Do some transformations for use of core\dataformat.
            $columns = array();
            foreach ($fields as $field) {
                $columns[$field] = $field;
            }
            for ($i = 0; $i < count($lines); $i++) {
                $line = array();
                for ($z = 0; $z < count($fields); $z++) {
                    $line[$fields[$z]] = $lines[$i][$z];
                }
                $lines[$i] = $line;
            }
            // Make an iterator from our array.
            $o = new ArrayObject($lines);
            $it = $o->getIterator();
            \core\dataformat::download_data($event->name, 'excel', $columns, $it);
        break;
        case 'html':
            echo $OUTPUT->header();
            echo $OUTPUT->render_from_template('mod_confman/export_html', array('eventtitle' => $event->name, 'items' => $items));
            echo $OUTPUT->footer();
        break;
        default:
        echo $OUTPUT->header();
        echo "UNKNOWN FORMAT";
        echo $OUTPUT->footer();
    }

}
