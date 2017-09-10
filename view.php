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

require('../../config.php');
require_once('lib.php');
 
$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'confman');
$confman = new mod_confman_event($cm->instance);


require_login($course, true, $cm);

$PAGE->set_url('/mod/confman/view.php', array('id' => $cm->id));
$PAGE->set_title(get_string('modulename', 'confman'));
$PAGE->set_heading(get_string('modulename', 'confman'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

?>
          <link rel="stylesheet" href="style/main.css" />
<?php

$confman->html();

echo $OUTPUT->footer();

?>
