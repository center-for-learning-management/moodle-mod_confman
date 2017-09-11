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
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/confman/lib.php');

$navigation = "<a href=\"#panel\" data-role=\"button\" data-icon=\"bars\">Entries</a>";

required_param("event",PARAM_INT);

$itemid = optional_param("id",0,PARAM_INT);
$token = optional_param("token","",PARAM_TEXT);
$ITEM = new mod_confman_item($itemid,$token);



?><DOCTYPE html>
<html>
     <head>
          <title><?php echo get_string('pluginname','confman'); ?></title>
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css" />
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/jquery.mobile-1.4.5/confman.min.css" />
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/jquery.mobile-1.4.5/jquery.mobile.icons.min.css" />
          <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/mod/confman/ajax/css.css" />
          <script src="<?php echo $CFG->wwwroot; ?>/mod/confman/jquery.mobile-1.4.5/jquery-1.11.1.min.js"></script>
          <script src="<?php echo $CFG->wwwroot; ?>/mod/confman/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js"></script>
          <script src="<?php echo $CFG->wwwroot; ?>/mod/confman/ajax/js.js"></script>
     </head>
     <body>
          <div data-role="page" id="item">
               <div data-role="header">
                    <h1><?php echo $ITEM->title; ?></h1>
               </div>
               <div role="main" class="ui-content">
<?php

if($ITEM->id>0){
     if($ITEM->had_token){
          $ITEM->form();
     } else {
          $ITEM->html();
     }
} else {
     $ITEM->form();
}

$ITEM->comments();

?>
               
               </div>
          </div>
     </body>
</html>
