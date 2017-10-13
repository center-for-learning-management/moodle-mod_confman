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

/*
 * This page can be accessed by users that are not logged in
 * Permission to modifiy an item or manage files depends
 * on the capability of a user (if logged in) or if the user
 * new a secret token that is specific to a certain item in
 * the database.
 *
 * All permission-checks are done in the constructor of mod_confman_item
 */

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/confman/lib.php');

/*
 *  We just check if this parameter is given, it is needed in the constructor of mod_confman_event
 *  which is created by mod_confman_item.
 */
required_param("event", PARAM_INT);
$itemid = optional_param("id", 0, PARAM_INT);
$token = optional_param("token", "", PARAM_ALPHANUMEXT);

$item = new mod_confman_item($itemid, $token);

?><DOCTYPE html>
<html>
     <head>
          <title><?php echo get_string('pluginname', 'confman'); ?></title>
          <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
          <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
          <script src="<?php echo $CFG->wwwroot; ?>/mod/confman/script/js.js"></script>
          <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/style/confman.min.css" />
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/style/main.css" />
     </head>
     <body>
<?php

// Now that we have created our item we check if we are allowed to access.
if (!$item->can_edit && !$item->can_view) {
    $OUTPUT->header();
    echo "<p>Permission denied</p>";
} else {
?>
          <div data-role="page" id="item">
               <div data-role="header">
                    <h1><?php echo $item->title; ?></h1>
               </div>
               <div role="main" class="ui-content">
<?php

    if ($item->id > 0) {
        if ($item->had_token) {
            $item->form();
        } else {
            $item->html();
        }
    } else {
        $item->form();
    }

    $item->comments();

?>
               
               </div>
          </div>
<?php
} // Ends else if can_edit and can_view.
?>
     </body>
</html>
