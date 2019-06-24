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

require_once($CFG->dirroot . '/mod/confman/thirdparty/Spout/Autoloader/autoload.php');

$event = required_param('event', PARAM_INT);

$event = new mod_confman_event($event);
require_login($event->course);

$PAGE->set_context(context_course::instance($event->course));
if ($event->can_manage) {
    $writer = \Box\Spout\Writer\WriterFactory::create(\Box\Spout\Common\Type::XLSX); // for XLSX files
    //$writer->setShouldUseInlineStrings(true); // default (and recommended) value
    //$writer->setShouldUseInlineStrings(false); // will use shared strings
    $writer->openToBrowser($event->name . '.xlsx');

    $fields = array('id', 'approved', 'title_pre', 'firstname', 'lastname', 'title_post', 'organization', 'email', 'title', 'targetgroups', 'types', 'description', 'memo');
    $writer->addRow($fields);

    $ids = optional_param_array('ids', null, PARAM_INT);
    foreach ($ids AS $id) {
        $item = new mod_confman_item($id);
        $fields = array(
            $item->id, $item->data->approved, $item->data->title_pre, $item->data->firstname,
            $item->data->lastname, $item->data->title_post, $item->data->organization,
            $item->data->email, $item->data->title, implode(', ', $item->data->targetgroups),
            implode(', ', $item->data->types), $item->data->description, $item->data->memo);
        $writer->addRow($fields);
    }

    $writer->close();
}
