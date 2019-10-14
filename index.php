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
require_once($CFG->libdir.'/adminlib.php');
sesskey();
/*
 * This page can be accessed by users that are not logged in
 * Permission to modifiy an item or manage files depends
 * on the capability of a user (if logged in) or if the user
 * new a secret token that is specific to a certain item in
 * the database.
 *
 * All permission-checks are done in the constructor of mod_confman_item
 */

require_once($CFG->dirroot . '/mod/confman/lib.php');

// If the user is not logged in we try to login as guest.
if (!isloggedin()) {
    $guestuser = guest_user();
    complete_user_login($guestuser);
}

/*
 *  We just check if this parameter is given, it is needed in the constructor of mod_confman_event
 *  which is created by mod_confman_item.
 */
$eventid = required_param("event", PARAM_INT);
$event = new mod_confman_event($eventid);

$itemid = optional_param("id", 0, PARAM_INT);
$token = optional_param("token", "", PARAM_ALPHANUMEXT);
$item = new mod_confman_item($itemid, $token, $eventid);

$preview = optional_param("preview", 0, PARAM_INT);
$embedded = optional_param("embedded", 0, PARAM_INT);
$noheader = optional_param("noheader", 0, PARAM_INT);

$PAGE->set_context(context_course::instance($event->course));
if ($embedded) {
    $PAGE->set_pagelayout('frametop');
} else {
    $PAGE->set_pagelayout('incourse'); //($USER->id == 0 || isguestuser($USER)) ? 'frametop' : 'incourse');
}
$PAGE->set_url(new moodle_url('/mod/confman/index.php', array('event' => $eventid, 'id' => $itemid, 'token' => $token, 'preview' => $preview, 'embedded' => $embedded, 'ts' => time()))); // the timestamp is used as atto would behave weird.
$PAGE->set_title($item->get_title());
$PAGE->set_heading($item->get_title());

//$PAGE->requires->js('/mod/confman/script/js.js');
//$PAGE->requires->css('/mod/confman/style/main.css');
//$PAGE->requires->css('/mod/confman/style/confman.min.css');

// Now that we have created our item we check if we are allowed to access.
if (!$item->can_edit && !$item->can_view) {
    if (!$noheader) echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('mod_confman/alert', array(
        'content' => 'Permission denied',
        'type' => 'danger',
        'url' => $CFG->wwwroot . '/my',
    ));
    if (!$noheader) echo $OUTPUT->footer();
    die();
}

// The form will be specific to the event stored in $event.
require_once($CFG->dirroot . '/mod/confman/classes/item_form.php');
$itemform = new item_form();
if ($data = $itemform->get_data()) {
    $item->store($data);
    if ($item->id > 0) {
        redirect($item->manage_link() . '&showsuccess=1');
        echo $OUTPUT->header();
        echo $OUTPUT->render_from_template('mod_confman/alert', array(
            'content' => get_string('item:stored', 'confman').'<br />'.get_string('item:you_can_modify', 'confman').': <a href="'.$item->manage_link().'">'.$item->manage_link().'</a>',
            'type' => 'success',
            'url' => $item->manage_link(),
        ));
        echo $OUPUT->footer();
        die();
    } else {
        echo $OUTPUT->render_from_template('mod_confman/alert', array(
            'content' => get_string('event:error', 'confman'),
            'type' => 'danger',
        ));
    }
}

if (!$noheader) echo $OUTPUT->header();
if (optional_param('showsuccess', 0, PARAM_INT) == 1) {
    echo $OUTPUT->render_from_template('mod_confman/alert', array(
        'content' => get_string('item:stored', 'confman').'<br />'.get_string('item:you_can_modify', 'confman').': <a href="'.$item->manage_link().'">'.$item->manage_link().'</a>',
        'type' => 'success',
        'url' => $item->manage_link(),
    ));
}

$item->data->event = $eventid;
$item->set_form_data($itemform);

if ($item->id > 0) {
    if (!$preview && ($item->can_manage || ($item->can_edit && !$item->is_obsolete))) {
        $itemform->display();
    } else {
        $item->prepare_output();
        echo html_writer::table($item->get_table());
    }

    // COMMENTS
    if (!$embedded) {
        require_once($CFG->dirroot . '/mod/confman/classes/comment_form.php');
        $commentform = new comment_form(str_replace($CFG->wwwroot, '', $PAGE->url));
        if ($data = $commentform->get_data()) {
            // Store the new comment.
            $comment = array(
                'comment' => $data->comment['text'],
                'created' => time(),
                'eventid' => $data->event,
                'itemid' => $data->id,
                'userid' => (!empty($USER->id) && !isguestuser($USER)) ? $USER->id : 0,
            );
            $comment['id'] = $DB->insert_record('confman_comments', $comment, true);
            echo $OUTPUT->render_from_template('mod_confman/alert', array(
                'content' => ($comment['id'] > 0) ? get_string('comment:stored:success', 'confman') : get_string('comment:stored:failed', 'confman'),
                'type' => ($comment['id'] > 0) ? 'success' : 'danger',
            ));
        }
        $commentform->set_data(array('id' => $item->id, 'event' => $event->id, 'token' => $token));
        $commentform->display();
    }

    $sql = "SELECT * FROM {confman_comments}
              WHERE eventid=? AND itemid=?
              ORDER BY created DESC";
    $comments = $DB->get_records_sql($sql, array($item->event->id, $item->id));
    foreach ($comments AS $comment) {
        $comment->created_readable = date("l, j. F Y H:i:s", $comment->created);
        if ($comment->userid > 0) {
            $user = $DB->get_record("user", array("id" => $comment->userid));
            $comment->user = "<a class=\"ui-li-aside\" href=\"".$CFG->wwwroot."/user/profile.php?id=".
                $user->id."\" data-ajax=\"false\">".$user->firstname." ".$user->lastname."</a>";
        } else {
            $comment->user = "<span class=\"ui-li-aside\">".get_string("user:external", "confman")."</span>";
        }
        echo $OUTPUT->render_from_template('mod_confman/comment', $comment);
    }
} else {
    // We show the form to enter a new item.
    if (!$event->is_obsolete) {
        $itemform->display();
    } else {
        echo $OUTPUT->render_from_template('mod_confman/alert', array(
            'content' => get_string('item:obsolete', 'confman'),
            'type' => 'warning',
        ));
    }
}

if (!$noheader) echo $OUTPUT->footer();
