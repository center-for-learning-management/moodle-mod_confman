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

class mod_confman_event {
    public function __construct($id) {
        global $DB;
        $confman = $DB->get_record('confman', array('id' => $id), '*', MUST_EXIST);
        $this->id = $id;
        $this->course = $confman->course;
        $this->name = $confman->name;
        $this->description = $confman->description;
        $this->submissionstart = $confman->submissionstart;
        $this->submissionend = $confman->submissionend;
        $this->event_organizer = $confman->event_organizer;
        $this->event_contact = $confman->event_contact;

        $targetgroups = explode("\n", $confman->targetgroups);
        $this->targetgroups = array();
        foreach ($targetgroups as $target) {
            $target = explode("#", $target);
            $this->targetgroups[] = array(
                "targetgroup" => trim(@$target[0]),
                "description" => @$target[1],
            );
        }

        $this->types = explode("\n", $confman->types);
        for ($i = 0; $i < count($this->types); $i++) {
            $this->types[$i] = trim($this->types[$i]);
        }

        $this->context = context_course::instance($this->course);

        $this->logo = $this->logo_url();

        $this->submissionstart_readable = date("Y-m-d, H:i:s", $this->submissionstart);
        $this->submissionend_readable = date("Y-m-d, H:i:s", $this->submissionend);
        $this->cmid = 0;
    }
    public function logo_url() {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_confman', 'draft', $this->id);
        foreach ($files as $f) {
            if ($f->get_filename() == ".") {
                continue;
            }
            $url = moodle_url::make_pluginfile_url(
                $f->get_contextid(), $f->get_component(), $f->get_filearea(),
                $f->get_itemid(), $f->get_filepath(), $f->get_filename()
                );
            return $url;
        }
        return "";
    }
    public function html() {
        global $CFG;
        $submissionstart = date("Y-m-d H:i", $this->submissionstart);
        $submissionend = date("Y-m-d H:i", $this->submissionend);
        $submissionlink = $CFG->wwwroot."/mod/confman/index.php?event=".$this->id;

        if ($this->logo != "") { ?>
            <img src="<?php echo $confman->logo; ?>" alt="logo" style="float: right;" />
        <?php
        }
        ?>

        <h1><?php echo $this->name; ?></h1>
        <p><?php echo get_string('event:submission:open', 'confman').": ".$submissionstart." - ".$submissionend; ?></p>
        <p><?php
            echo get_string('event:submission:link', 'confman').
                ": <a href=\"".$submissionlink."\" target=\"_blank\">".
                $submissionlink."</a>";
        ?></p>
        <div><?php echo $this->description; ?></div>
        <p><?php echo get_string('event:organizer', 'confman').": ".$this->event_organizer; ?></p>
        <p><?php echo get_string('event:contact', 'confman').": ".$this->event_contact; ?></p>
        <h3><?php echo get_string('code:embed', 'confman'); ?></h3>
        <pre>&lt;iframe src="<?php echo $submissionlink; ?>"&gt;&lt;/iframe&gt;</pre>
        <?php
        $this->list_items();
    }
    public function list_items() {
        global $DB, $CFG;
        $items = $DB->get_records('confman_items', array('event' => $this->id)); ?>

        <h3><?php echo get_string('event:submissions', 'confman'); ?></h3>
        <a href="view.php?id=<?php echo $this->cmid; ?>&act=listall" data-role="button" data-icon="action"><?php echo get_string('event:listall','confman'); ?></a>
        <ul class="confman_list" data-role="listview" data-filter="true" data-split-icon="gear" data-inset="true">
        <?php
        foreach ($items as $item) {
            $submissionlink = $CFG->wwwroot."/mod/confman/index.php?event=".$this->id."&id=".$item->id;
            $submissionedit = $CFG->wwwroot."/mod/confman/index.php?event=".$this->id."&id=".$item->id."&token=".$item->token;
        ?>
            <li>
                <div class="controls">
                    <a href="<?php echo $submissionlink; ?>" target="_blank">view</a>
                    <?php
                    if (has_capability('mod/confman:manage', $this->context)) { ?>
                    <a href="<?php echo $submissionedit; ?>" target="_blank">edit</a>
                    <?php
                    } /* has_capability mod confman:manage */
                    ?>
                </div>
                <h3><?php echo $item->title; ?></h3>
                <p><?php echo $item->firstname." ".$item->lastname; ?></p>
            </li>
        <?php
        }
        ?>
        </ul>
        <?php
    }
}

function confman_add_instance($event) {
    global $DB, $COURSE;
    $event->course = $COURSE->id;
    $time = new DateTime("now");
    $event->created = $time;

    return $DB->insert_record('confman', $event);
}
function confman_update_instance($event) {
    global $DB, $COURSE;
    $event->id = $event->instance;
    $event->course = $COURSE->id;

    return $DB->update_record('confman', $event);
}
function confman_delete_instance($id) {
    global $DB;
    if (! $event = $DB->get_record('confman', 'id', $id)) {
        return false;
    }
    $result = true;

    if (! $DB->delete_records('confman', 'id', $event->id)) {
        $result = false;
    }

    return $result;
}


class mod_confman_item {
    public function __construct($id, $token="") {
        global $DB, $CFG;
        $this->debug = optional_param("debug", 0, PARAM_INT);
        $this->itemcheck = cache::make('mod_confman', 'itemcheck');
        $this->hadtokenfor = cache::make('mod_confman', 'hadtokenfor');
        $this->storedcache = cache::make('mod_confman', 'stored');
        $this->stored = $this->storedcache->get('stored');

        $this->confman = $CFG->wwwroot . '/mod/confman/';
        $this->id = $id;
        $this->token = $token;

        $this->errors = 0;
        $this->error = array();
        $this->stored = 0;

        $entries = $DB->get_records_sql('SELECT * FROM {confman_items} WHERE id=?', array($this->id));
        foreach ($entries as $entry) {
            $this->data = $entry;
        }
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }

        if ($this->id > 0) {
            $this->eventid = @$this->data->event;
        } else {
            $this->eventid = optional_param("event", 0, PARAM_INT);
        }
        $this->event = new mod_confman_event($this->eventid);

        $time = new DateTime("now");
        $this->is_obsolete = ($time->getTimestamp() > $this->event->submissionend);

        $vars = array('contents', 'targetgroups', 'description', 'types', 'organization', 'title_pre', 'title_post', 'memo');
        $c = @json_decode($this->data->contents);
        foreach($vars AS $var) {
            if (isset($c->{$var})) {
                $this->data->{$var} = $c->{$var};
            } else {
                $this->data->{$var} = '';
            }
        }

        if (!is_array($this->data->targetgroups)) {
            $this->data->targetgroups = array();
        }
        if (!is_array($this->data->types)) {
            $this->data->types = array();
        }

        $this->context = context_course::instance($this->event->course);
        $this->can_manage = (has_capability('mod/confman:manage', $this->context));
        $this->can_rate = (has_capability('mod/confman:rate', $this->context));

        $this->had_token = ($this->token != "" && @$this->data->token == $this->token);

        $this->can_view = ($this->had_token || $this->can_manage || $this->can_rate);
        $this->can_edit = ($this->id == 0 || $this->had_token || $this->can_manage);

        if ($this->can_view && @$this->data->title != "") {
            $this->title = $this->event->name.": ".$this->data->title;
        } else if ($this->id == 0) {
            $this->title = $this->event->name;
        } else {
            $this->title = get_string('pluginname', 'confman');
        }

        $this->manageLink = $this->manage_link();
        if ($this->debug) {
            var_dump($this);
        }

        // If we have access to this item we store this in CACHE to enable downloading of files.
        if ($this->can_view || $this->can_edit) {
            $hadtokenfor = $this->hadtokenfor->get('hadtokenfor');
            if (!$hadtokenfor) {
                $hadtokenfor = array();
            }
            $hadtokenfor[] = $this->id;
            $this->hadtokenfor->set('hadtokenfor', $hadtokenfor);
        }

        if (optional_param("store", 0, PARAM_INT) == 1) {
            $this->retrieve();
            if ($this->itemcheck->get('itemcheck') == optional_param("itemcheck", 0, PARAM_INT)) {
                $this->store();
            } else {
                $this->errors++;
                $this->error['itemcheck'] = true;
            }
        }
        if (optional_param("store_comment", 0, PARAM_INT) == 1) {
            $this->comment_store();
        }
    }

    public function retrieve() {
        if (isset($this->data)) {
            $this->origdata = clone($this->data);
        } else {
            $this->origdata = new stdClass();
        }
        $relocate = false;

        $keys = array(
            "title_pre", "title_post", "firstname", "lastname", "email", "email2",
            "title", "description", "organization", "memo"
        );
        foreach ($keys as $key) {
            $this->data->{$key} = optional_param($key, "", PARAM_TEXT);
        }
        $keys = array("targetgroups", "types");
        foreach ($keys as $key) {
            $this->data->{$key} = optional_param_array($key, array(), PARAM_RAW);
        }
    }

    public function store() {
        global $DB, $_FILES;

        $tz = new DateTime("now");
        if (!isset($this->data->created)) {
            $this->data->created = $tz->getTimestamp();
        }
        $this->data->modified = $tz->getTimestamp();
        $this->data->contents = json_encode(array(
            "targetgroups" => $this->data->targetgroups,
            "description" => $this->data->description,
            "types" => $this->data->types,
            "organization" => $this->data->organization,
            "title_pre" => $this->data->title_pre,
            "title_post" => $this->data->title_post,
            "memo" => $this->data->memo,
        ), JSON_NUMERIC_CHECK);

        if (!isset($this->data->event) || $this->data->event == 0) {
            $this->data->event = $this->event->id;
        }
        if (!isset($this->data->token) || $this->data->token == "" || $this->data->token == "NULL") {
            $this->data->token = md5(date("Y-m-d H:i:s").rand(0, 1000));
            $this->token = $this->data->token;
        }

        $this->manageLink = $this->manage_link();

        if (!filter_var($this->data->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors++;
            $this->error['email'] = true;
        }

        if (@$this->origdata->email != @$this->data->email) {
            if ($this->data->email != $this->data->email2) {
                $this->errors++;
                $this->error['email2'] = true;
            }
        }
        if ($this->errors == 0) {
            if ($this->id > 0) {
                $DB->update_record('confman_items', $this->data);
                //$this->storedcache->set('stored', 1);
                $this->stored = 1;
            } else {
                $this->id = $DB->insert_record('confman_items', $this->data, true);
                $relocate = true;
                $this->token = $this->data->token;
                $this->stored = 1;
                $this->storedcache->set('stored', 1);
                $this->manageLink = $this->manage_link();
            }

            if (isset($_FILES['file'])) {
                $this->file_append();
            }

            $this->mail();

            if ($relocate) {
                $relocateurl = $this->manageLink;
                header('Location: '.$relocateurl);
                echo "Forward to ".$relocateurl;
            }
        }
    }

    private function manage_link() {
        return $this->confman . 'index.php?event=' . $this->event->id . '&id=' . $this->id . '&token=' . @$this->data->token;
    }

    public function mail($type = "mail") {
        global $CFG, $DB;
        $touser = new stdClass();
        $touser->email = $this->data->email;
        $touser->firstname = $this->data->firstname;
        $touser->lastname = $this->data->lastname;
        $touser->maildisplay = true;
        $touser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
        $touser->id = -99; // invalid userid, as the user has no userid in our moodle.
        $touser->firstnamephonetic = "";
        $touser->lastnamephonetic = "";
        $touser->middlename = "";
        $touser->alternatename = "";

        // Using support-user: $fromuser = core_user::get_support_user().
        $fromuser = new stdClass();
        $fromuser->email = $this->event->event_contact;
        $fromuser->firstname = $this->event->event_organizer;
        $fromuser->lastname = "";
        $fromuser->maildisplay = true;
        $fromuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
        $fromuser->id = -99; // invalid userid, as the user has no userid in our moodle.
        $fromuser->firstnamephonetic = "";
        $fromuser->lastnamephonetic = "";
        $fromuser->middlename = "";
        $fromuser->alternatename = "";

        $fs = get_file_storage();
        $itemfiles = $fs->get_area_files($this->context->id, 'mod_confman', 'content', $this->id);
        $files = array();

        foreach ($itemfiles as $f) {
            if ($f->get_filename() == ".") {
                continue;
            }
            // Variable $f is an instance of stored_file.
            $url = moodle_url::make_pluginfile_url(
                $f->get_contextid(), $f->get_component(), $f->get_filearea(),
                $f->get_itemid(), $f->get_filepath(), $f->get_filename()
            );
            $files[] = "<li><a href=\"".$url."\">".$f->get_filename()."</a></li>";
        }
        if (count($files) == 0) {
            $files[] = "<li>".get_string("none", "confman")."</li>";
        }
        $dbcomments = $DB->get_records_sql(
            'SELECT * FROM {confman_comments} WHERE eventid=? AND itemid=? ORDER BY created DESC',
            array($this->eventid, $this->id)
        );
        $comments = array();
        if (count($dbcomments) > 0) {
            $comments[] = "<h2>".get_string('comments', 'confman')."</h2>";
            $comments[] = "<ul data-role=\"listview\" data-inset=\"true\">\n";
        }
        foreach ($dbcomments as $comment) {
            $comment->created_readable = date("l, j. F Y H:i:s", $comment->created);
            if ($comment->userid > 0) {
                $user = $DB->get_record("user", array("id" => $comment->userid));
                $comment->user = "<a class=\"ui-li-aside\" href=\"".$CFG->wwwroot."/user/profile.php?id=".
                    $user->id."\" data-ajax=\"false\">".$user->firstname." ".$user->lastname."</a>";
            } else {
                $comment->user = "<span class=\"ui-li-aside\">".get_string("user:external", "confman")."</span>";
            }
            $comments[] = "<li data-role=\"list-divider\">".$comment->created_readable."</li>";
            $comments[] = "<li><p>".$comment->comment.$comment->user."</p></li>";
        }
        if (count($dbcomments) > 0) {
            $comments[] = "</ul>\n";
        }
        // Use $type instead of 'mail' to make various templates.
        $messagehtml = file_get_contents($CFG->dirroot."/mod/confman/templates/mail.html");

        $templatelines = explode("{", $messagehtml);
        $lines = array();

        foreach ($templatelines as $line) {
            if (strpos($line, "}") > -1) {
                $key = substr($line, 0, strpos($line, "}"));
                $remainder = substr($line, strpos($line, "}") + 1);

                $keytype = substr($key, 0, strpos($key, ":"));
                $keyidentifier = substr($key, strpos($key, ":") + 1);

                switch($keytype){
                    case "lang":
                        if ($keyidentifier == "mail:thankyou") {
                            $keyidentifier = "mail:thankyou:".$type;
                        }
                        $key = get_string($keyidentifier, "confman");
                    break;
                    case "this":
                        if (isset($this->{$keyidentifier})) {
                            $key = $this->{$keyidentifier};
                        } else {
                            $key = "{+".$key."+}";
                        }
                    break;
                    case "item":
                        if (isset($this->data->{$keyidentifier})) {
                            switch ($keyidentifier) {
                                case "targetgroups":
                                case "types":
                                    $key = implode(", ", $this->data->{$keyidentifier});
                                break;
                                default:
                                    $key = $this->data->{$keyidentifier};
                            }
                        } else {
                            $key = "{*".$key."*}";
                        }
                    break;
                    case "event":
                        if (isset($this->event->{$keyidentifier})) {
                            $key = $this->event->{$keyidentifier};
                        } else {
                            $key = "{*".$key."*}";
                        }
                    break;
                    case "files":
                        $key = implode("\n", $files);
                    break;
                    case "comments":
                        $key = implode("\n", $comments);
                    break;
                    default:
                        $key = "{{".$key."}}";
                }
                $lines[] = $key.$remainder;
            }
        }
        $messagehtml = implode("", $lines);

        // Replace fields that are used in language strings.
        $messagehtml = str_replace("{data:event:submissionend_readable}", $this->event->submissionend_readable, $messagehtml);
        $messagehtml = str_replace("{data:event:submissionstart_readable}", $this->event->submissionstart_readable, $messagehtml);

        $messagetext = html_to_text($messagehtml);

        $subject = get_string('mail:subject:'.$type, 'confman');

        email_to_user($touser, $fromuser, $subject, $messagetext, $messagehtml, "", true);
        // And vice versa.
        email_to_user($fromuser, $touser, $subject, $messagetext, $messagehtml, "", true);

        if ($this->debug) {
            var_dump($touser);
            var_dump($fromuser);
            var_dump($messagehtml);
            var_dump($messagetext);
        }
    }

    public function form() {
        if ($this->is_obsolete) {
            echo "<p class=\"alert alert-error\">".get_string('item:obsolete', 'confman')."</p>";
            if (!$this->can_manage) {
                $this->html();
                return;
            }
        }
        ?>
        <div class="item">
        <?php

        if ($this->stored > 0) {
            $this->storedcache->set('stored', 0);
            echo "<p class=\"alert alert-success\">".
                get_string('item:stored', 'confman').
                "<br />".
                get_string('item:you_can_modify', 'confman').": <a href=\"".$this->manage_link()."\">".$this->manage_link()."</a>".
                "</p>";
        }
        if ($this->errors > 0) {
            echo "<p class=\"alert alert-error\">".get_string('item:error', 'confman')."</p>";
        }

        ?>
        <div><?php echo $this->event->description; ?></div>
        <form method="POST" enctype="multipart/form-data"
              action="?event=<?php echo $this->event->id; ?>&id=<?php echo $this->id."&token=".$this->token.
                      (($this->debug) ? "&debug=".$this->debug : ""); ?>"
              data-ajax="false">
            <input type="hidden" name="store" value="1">
            <h3><?php echo get_string('item:section:personaldata', 'confman'); ?></h3>
            <div data-role="fieldset">
                <label for="item-title_pre"><?php echo get_string('item:title_pre', 'confman'); ?></label>
                <input type="text" name="title_pre" id="item-title_pre" value="<?php echo @$this->data->title_pre; ?>"
                       placeholder="<?php echo get_string('item:title_pre', 'confman'); ?>">
                <?php
                if (@$this->error["title_pre"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                }?>
            </div>
            <div data-role="fieldset">
                <label for="item-firstname"><?php echo get_string('item:firstname', 'confman'); ?></label>
                <input type="text" name="firstname" id="item-firstname" value="<?php echo @$this->data->firstname; ?>"
                       placeholder="<?php echo get_string('item:firstname', 'confman'); ?>">
                <?php
                if (@$this->error["firstname"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-lastname"><?php echo get_string('item:lastname', 'confman'); ?></label>
                <input type="text" name="lastname" id="item-lastname" value="<?php echo @$this->data->lastname; ?>"
                       placeholder="<?php echo get_string('item:lastname', 'confman'); ?>">
                <?php
                if (@$this->error["lastname"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-title_post"><?php echo get_string('item:title_post', 'confman'); ?></label>
                <input type="text" name="title_post" id="item-title_post" value="<?php echo @$this->data->title_post; ?>"
                       placeholder="<?php echo get_string('item:title_post', 'confman'); ?>">
                <?php
                if (@$this->error["title_post"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-organization"><?php echo get_string('item:organization', 'confman'); ?></label>
                <input type="text" name="organization" id="item-organization" value="<?php echo @$this->data->organization; ?>"
                       placeholder="<?php echo get_string('item:organization', 'confman'); ?>">
                <?php
                if (@$this->error["organization"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-email"><?php echo get_string('item:email', 'confman'); ?></label>
                <input type="text" name="email" id="item-email" value="<?php echo @$this->data->email; ?>"
                       placeholder="<?php echo get_string('item:email', 'confman'); ?>">
                <?php
                if (@$this->error["email"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-email2"><?php echo get_string('item:email2', 'confman'); ?></label>
                <input type="text" name="email2" id="item-email2" placeholder="<?php echo get_string('item:email2', 'confman'); ?>" value="<?php echo @$this->data->email2; ?>">
                <?php
                if (@$this->error["email2"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>

            <h3><?php echo get_string('item:section:yoursubmission', 'confman'); ?></h3>
            <div data-role="fieldset">
                <label for="item-title"><?php echo get_string('item:title', 'confman'); ?></label>
                <input type="text" name="title" id="item-title" value="<?php echo @$this->data->title; ?>"
                       placeholder="<?php echo get_string('item:title', 'confman'); ?>">
                <?php
                if (@$this->error["title"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset"<?php if(count($this->event->types) == 0 || $this->event->types[0] == '') { echo " style=\"display: none;\""; } ?>>
                <label><?php echo get_string('item:type', 'confman'); ?></label>
                <?php
                foreach ($this->event->types as $type) {
                ?>
                <label>
                    <input data-role="none" type="checkbox" name="types[]" value="<?php echo $type; ?>"
                        <?php echo ((in_array($type, $this->data->types)) ? "checked=\"checked\"" : ""); ?> />
                    <?php echo $type; ?>
                </label>
                <?php
                } // End foreach types.
                if (@$this->error["types"]) {
                    echo "<p class=\"alert alert-error\">" . get_string('item:invalidvalue', 'confman') . "</p>";
                }
                ?>
            </div>
            <div data-role="fieldset"<?php if(count($this->event->targetgroups) == 0 || (isset($this->event->targetgroups[0]['targetgroup']) && $this->event->targetgroups[0]['targetgroup'] == '')) { echo " style=\"display: none;\""; } ?>>
                <label for="item-targetgroup"><?php echo get_string('item:targetgroup', 'confman'); ?></label>

                <?php
                foreach ($this->event->targetgroups as $target) {
                ?>
                <label>
                    <input data-role="none" type="checkbox" name="targetgroups[]" value="<?php echo $target["targetgroup"]; ?>"
                    <?php echo ((@in_array($target["targetgroup"], $this->data->targetgroups)) ? "checked=\"checked\"" : ""); ?> />
                    <?php
                        echo $target["targetgroup"].(($target["description"] != "") ? "(".$target["description"].")" : "");
                    ?>
                </label>
                <?php
                }
                if (@$this->error["targetgroups"]) {
                    echo "<p class=\"alert alert-error\">" . get_string('item:invalidvalue', 'confman') . "</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-description"><?php echo get_string('item:description', 'confman'); ?></label>
                <textarea name="description" id="item-description"><?php echo @$this->data->description; ?></textarea>
                <?php
                if (@$this->error["description"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-memo"><?php echo get_string('item:memo', 'confman'); ?></label>
                <textarea name="memo" id="item-memo"><?php echo @$this->data->memo; ?></textarea>
                <?php
                if (@$this->error["memo"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>
            <div data-role="fieldset">
                <label for="item-check"><?php echo get_string('item:check','confman'); ?></label>
                <p><?php
                $calcs = array("+" , "-");
                $z1 = rand(10,20);
                $z2 = rand(1,10);
                $calc = rand(0, count($calcs) - 1);
                $calc = $z1 . " " . $calcs[$calc] . " " . $z2;

                $this->itemcheck->set('itemcheck', eval("return " . $calc . ";"));
                echo $calc . " =";
                ?>
                <input type="numerical" id="item-check" name="itemcheck"/>
                </p>
                <?php
                if (@$this->error["itemcheck"]) {
                    echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                } ?>
            </div>

            <input type="submit" value="<?php echo get_string('form:submit', 'confman'); ?>">
        </form>
        <?php
        $this->file_upform();
    }

    public function file_upform(){
        ?>
        <h3><?php echo get_string('item:section:files', 'confman'); ?></h3>

        <?php
        if ($this->id > 0) {
            ?>
            <div data-role="fieldset">
                <label for="item-file"><?php echo get_string('item:file:append', 'confman'); ?></label>
                <input type="file" name="file" id="item-file"
                       onchange="mod_confman.fileAppend(<?php echo $this->id; ?>,'<?php echo $this->token; ?>');" />
            </div>
            <?php
            $fs = get_file_storage();
            $files = $fs->get_area_files($this->context->id, 'mod_confman', 'content', $this->id);
            ?>
            <ul id="item-files" data-role="listview" data-split-icon="delete" data-inset="true">
            <?php
            foreach ($files as $f) {
                if ($f->get_filename() == ".") {
                    continue;
                }
                // Parameter $f is an instance of stored_file.
                $url = moodle_url::make_pluginfile_url(
                    $f->get_contextid(), $f->get_component(), $f->get_filearea(),
                    $f->get_itemid(), $f->get_filepath(), $f->get_filename()
                );
                ?>
                <li data-filename="<?php echo $f->get_filename(); ?>">
                    <a href="<?php echo $url; ?>" data-ajax="false" target="_blank"><?php echo $f->get_filename(); ?></a>
                    <a href="#" onclick="mod_confman.fileDelete(<?php
                        echo $this->id.",'".$this->data->token."','".$f->get_filename()."'";
                    ?>);"></a>
                </li>
                <?php
            }
            echo "</ul>\n";
        } else { // End if $this->id>0.
        ?>
        <p><?php echo get_string('item:file:infostorefirst', 'confman'); ?></p>
        <?php
        } // End if $this->id>0.
        ?>
        </div>
        <?php
    }

    public function file_append($filename, $base64) {
        if (!$this->had_token && !$this->can_manage) {
            // No permission to modify files.
            return "";
        }
        $x = explode(";base64,", $base64);
        $content = base64_decode(@$x[1]);

        $fs = get_file_storage();
        $fileinfo = array(
            'contextid' => $this->context->id, 'component' => 'mod_confman',
            'filearea' => 'content', 'itemid' => $this->id, 'filepath' => '/',
            'filename' => $filename, 'timecreated' => time(), 'timemodified' => time()
        );

        $file = $fs->get_file(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']
        );

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }
        $fs->create_file_from_string($fileinfo, $content);

        $this->mail("file_append");

        $file = $fs->get_file(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']
        );

        $url = moodle_url::make_pluginfile_url(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']
        );

        return $url;
    }

    public function file_delete($filename) {
        if (!$this->had_token && !$this->can_manage) {
            // No permission to modify files.
            return false;
        }
        $fs = get_file_storage();

        $file = $fs->get_file(
            $this->context->id, 'mod_confman', 'content',
            $this->id, '/', $filename
        );

        // Delete it if it exists.
        if ($file) {
            $file->delete();
            $this->mail("file_delete");
            return true;
        } else {
            var_dump($file);
        }
        return false;
    }

    public function html() {
        if ($this->had_token || $this->can_manage || $this->can_rate) {
            $managelink = $this->confman."index.php?event=".$this->event->id."&id=".$this->id."&token=".$this->data->token;
            ?>

            <div class="item">
                <div class="block">
                    <p class="heading"><?php echo get_string('item:event', 'confman'); ?></p>
                        <p><?php echo $this->event->name; ?></p>
                    </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:title', 'confman'); ?></p>
                    <p><?php echo $this->data->title; ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:contributor', 'confman'); ?></p>
                    <p><?php
                        echo $this->data->title_pre." ".$this->data->firstname." ".
                             $this->data->lastname." ".$this->data->title_post;
                    ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:email', 'confman'); ?></p>
                    <p><?php
                        echo $this->data->email;
                    ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:organization', 'confman'); ?></p>
                    <p><?php echo $this->data->organization; ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:type', 'confman'); ?></p>
                    <p><?php echo implode(", ", $this->data->types); ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:targetgroup', 'confman'); ?></p>
                    <p><?php echo implode(", ", $this->data->targetgroups); ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:description', 'confman'); ?></p>
                    <p><?php echo $this->data->description; ?></p>
                </div>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:memo', 'confman'); ?></p>
                    <p><?php echo $this->data->memo; ?></p>
                </div>
                <?php
                if ($this->had_token && $this->is_obsolete) {
                    $this->file_upform();
                } else {
                ?>
                <div class="block">
                    <p class="heading"><?php echo get_string('item:files', 'confman'); ?></p>
                    <p class="filearea">
                    <?php
                    $i = 0;
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($this->context->id, 'mod_confman', 'content', $this->id);
                    foreach ($files as $f) {
                        if ($f->get_filename() == ".") {
                            continue;
                        }
                        $i++;
                        $url = moodle_url::make_pluginfile_url(
                            $this->context->id, 'mod_confman', 'content',
                            $this->id, '/', $f->get_filename()
                        );
                        // Parameter $f is an instance of stored_file.
                        ?>
                        <a href="<?php echo $url; ?>" target="_blank" data-role="button" data-inline="true" data-icon="tag">
                        <?php echo $f->get_filename(); ?></a>
                        <?php
                    }
                    if ($i == 0) {
                        echo get_string('none', 'confman');
                    } ?></p>
                </div>
                <?php
                } // Ends else from if had_token and is_obsolete
                ?>
            </div>

            <?php
        } else if ($this->id > 0) {
            echo "<p>".get_string('error:view', 'confman')."</p>";
        }
    }

    public function comments() {
        global $DB, $CFG;
        // Either we are allowed to manage, rate, or we knew the token!
        if (!$this->can_manage && !$this->can_rate && !$this->had_token) {
            return;
        }
        if ($this->id == 0) {
            return;
        } ?>
        <div class="item" style="margin-top: 20px;">
        <?php
        if (isset($this->comment_stored)) {
            if ($this->comment_stored) {
            ?>
                <p class="alert alert-success"><?php echo get_string('comment:stored:success', 'confman'); ?></p>
            <?php
            } else {
            ?>
                <p class="alert alert-error"><?php echo get_string('comment:stored:failed', 'confman'); ?></p>
            <?php
            }
        }

            $comments = $DB->get_records_sql(
                'SELECT * FROM {confman_comments} WHERE eventid=? AND itemid=? ORDER BY created DESC',
                array($this->eventid, $this->id)
            );
            if (count($comments) > 0) {
                echo "<ul data-role=\"listview\" data-inset=\"true\">\n";
            }
            foreach ($comments as $comment) {
                $comment->created_readable = date("l, j. F Y H:i:s", $comment->created);
                if ($comment->userid > 0) {
                    $user = $DB->get_record("user", array("id" => $comment->userid));
                    $comment->user = "<a class=\"ui-li-aside\" href=\"".$CFG->wwwroot."/user/profile.php?id=".
                        $user->id."\" data-ajax=\"false\">".$user->firstname." ".$user->lastname."</a>";
                } else {
                    $comment->user = "<span class=\"ui-li-aside\">".get_string("user:external", "confman")."</span>";
                }
                ?>
                <li data-role="list-divider"><?php echo $comment->created_readable; ?></li>
                <li>
                    <p style="white-space: pre;">
                        <?php echo $comment->comment; ?>
                        <?php echo $comment->user; ?>
                    </p>
                </li>
                <?php
            }
            if (count($comments) > 0) {
                echo "</ul>\n";
            }
            if ($this->id > 0) {
            ?>
            <form method="POST" enctype="multipart/form-data"
                action="?event=<?php echo $this->event->id; ?>&id=<?php
                    echo $this->id."&token=".$this->token.(($this->debug) ? "&debug=".$this->debug : "");
                ?>" data-ajax="false">
                <input type="hidden" name="store_comment" value="1">
                <h3><?php echo get_string('comment:add', 'confman'); ?></h3>
                <div data-role="fieldset">
                    <textarea name="comment"></textarea>
                    <?php
                    if (@$this->error["comment"]) {
                        echo "<p class=\"alert alert-error\">".get_string('item:invalidvalue', 'confman')."</p>";
                    } ?>
                </div>
                <input type="submit" value="<?php echo get_string('comment:store', 'confman'); ?>" />
            </form>
            </div>
            <?php
            } // End this->id>0.
    }

    public function comment_store() {
        global $DB, $USER;

        if ($this->id == 0) {
            $this->errors++;
            $this->error["comment"] = true;
            return;
        }

        if (!$this->can_manage && !$this->can_rate && !$this->had_token) {
            return;
        }
        $commentstr = optional_param("comment", "", PARAM_TEXT);
        if ($commentstr == "") {
            $this->errors++;
            $this->error["comment"] = true;
            return;
        }
        $comment = new stdClass();
        $comment->eventid = $this->eventid;
        $comment->itemid = $this->id;
        $comment->comment = $commentstr;
        $comment->userid = $USER->id;
        $time = new DateTime();
        $comment->created = $time->getTimestamp();
        $this->comment_stored = $DB->insert_record('confman_comments', $comment, true);
        $this->mail("comment");
    }

    public static function asutf8($str) {
        if (preg_match('!!u', $str)) {
            return $str;
        } else {
            return utf8_encode($str);
        }
    }
}

function mod_confman_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'content') {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Users may want access to their submissions files even though they are not logged in to moodle.
    // Therefore we stored in the CACHE if the user had access to the item.
    // Make sure the user has access to item.

    $cache = cache::make('mod_confman', 'hadtokenfor');
    $hadtokenfor = $cache->get('hadtokenfor');
    if (!$hadtokenfor || !in_array($itemid, $hadtokenfor)) {
        return false;
    }

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_confman', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
