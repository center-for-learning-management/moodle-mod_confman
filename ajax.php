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
require_once('../../config.php');
/*
 * This page can be accessed by users that are not logged in!
 *
 * Permission to modifiy an item or manage files depends
 * on the capability of a user (if logged in) or if the user
 * knew the secret token that is specific to a certain item in
 * the database.
 *
 * All permission-checks are done in the constructor of mod_confman_item
 */

require_once($CFG->dirroot . '/mod/confman/lib.php');


$action = required_param("act", PARAM_ALPHANUMEXT);
$itemid = required_param("id", PARAM_INT);
$token = optional_param("token", "", PARAM_ALPHANUMEXT);

$item = new mod_confman_item($itemid, $token);

$PAGE->set_context(context_course::instance($item->event->course));

// Now that we have created our item we check if we are allowed to access.
if ($item->id == 0 || (!$item->can_edit && !$item->can_view)) {
    $OUTPUT->header();
    echo "<p>Permission denied</p>";
    echo $OUTPUT->footer();
    exit;
}

$result = array();

switch($action){
    case "file_append":
        if ($item->can_edit) {
            $filename = required_param("filename", PARAM_TEXT);
            $filecontent = required_param("file", PARAM_RAW);
            $result["url"] = "".$item->file_append($filename, $filecontent);
            if ($result["url"] != "") {
                $result["status"] = "ok";
            } else {
                $result["status"] = "error";
            }
        }
    break;
    case "file_delete":
        if ($item->can_edit) {
            $filename = required_param("filename", PARAM_TEXT);
            $chk = $item->file_delete($filename);
            $result["delete_file"] = $filename;
            if ($chk) {
                $result["status"] = "ok";
            } else {
                $result["status"] = "error";
            }
        }
    break;
    case "file_mail":
        if ($item->can_edit) {
            $type = required_param("type", PARAM_TEXT);
            if (!in_array($type, array("file_append", "file_delete"))) {
                $result["status"] = "error";
            } else {
                $result["status"] = "ok";
                $item->mail($type, 'files');
            }
        }
    break;
    case "set_approved":
        if ($item->can_manage) {
            $setto = optional_param('setto', 0, PARAM_INT);
            $item->data->approved = $setto;
            $item->store($item->data, 0);
            $result["setto"] = $setto;
            $result["status"] = "ok";
        }
    break;
}

die(mod_confman_item::asutf8(json_encode($result, JSON_NUMERIC_CHECK)));
