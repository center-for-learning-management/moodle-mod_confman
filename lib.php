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
        global $CFG, $DB;
        $confman = $DB->get_record('confman', array('id' => $id)); //, '*', MUST_EXIST);
        $keys = array_keys((array)$confman);
        foreach ($keys AS $key) {
            $this->{$key} = $confman->{$key};
        }
        $this->id = $id;

        if (!empty($confman->cmid)) {
            $this->context = context_module::instance($confman->cmid);
            require_once($CFG->dirroot . '/lib/filelib.php');
            $this->intro = file_rewrite_pluginfile_urls($this->intro, 'pluginfile.php', $this->context->id, 'mod_confman', 'introx', $this->id);
        } else {
            $this->context = context_course::instance($this->course);
        }

        $this->can_manage = (has_capability('mod/confman:manage', $this->context));

        $targetgroups = explode("\n", $confman->targetgroups);
        $this->targetgroups = array();
        foreach ($targetgroups as $target) {
            $target = explode("#", $target);
            $this->targetgroups[] = array(
                "targetgroup" => trim(@$target[0]),
                "description" => trim(@$target[1]),
            );
        }

        $this->types = explode("\n", $confman->types);
        for ($i = 0; $i < count($this->types); $i++) {
            $this->types[$i] = trim($this->types[$i]);
        }

        $this->logo = $this->logo_url();

        $this->is_obsolete = (time() > $this->submissionend);
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
    /**
     * Renders the HTML-Output for an event.
     * @param subtype Render a subtype of this template, e.g. '_public'
     */
    public function html($subtype='') {
        global $CFG, $OUTPUT;
        $this->_submissionstart = date("Y-m-d H:i", $this->submissionstart);
        $this->_submissionend = date("Y-m-d H:i", $this->submissionend);
        $this->submissionlink = $CFG->wwwroot . "/mod/confman/index.php?embedded=1&event=".$this->id;

        return $OUTPUT->render_from_template('mod_confman/event' . $subtype, $this);
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

class mod_confman_item {
    static $packed_vars = array('approved', 'contents', 'description', 'memo', 'organization', 'targetgroups', 'title_pre', 'title_post', 'types');
    /**
     * Constructor for confman item.
     * @param id of item
     * @param token (optional) to edit without login
     * @param eventid (optional) eventid - only used when id is 0
     */
    public function __construct($id = 0, $token="", $eventid = 0) {
        global $CFG, $DB, $event;
        if (empty($eventid) && !empty($event->id)) $eventid = $event->id;
        $this->debug = optional_param("debug", 0, PARAM_INT);
        $this->itemcheck = cache::make('mod_confman', 'itemcheck');
        $this->hadtokenfor = cache::make('mod_confman', 'hadtokenfor');

        $this->confman = $CFG->wwwroot . '/mod/confman/';
        $this->id = $id;
        $this->token = $token;

        if ($id > 0) {
            $this->data = $DB->get_record('confman_items', array('id' => $this->id), '*', IGNORE_MISSING);
        } else {
            $this->data = (object) array(
                'id' => 0,
                'event' => $eventid,
                'contents' => '{}',
            );
        }

        $this->event = new mod_confman_event($this->data->event);

        try {
            $c = @json_decode($this->data->contents);
            foreach(self::$packed_vars AS $var) {
                if (!empty($c->{$var})) {
                    $this->data->{$var} = $c->{$var};
                } else {
                    $this->data->{$var} = '';
                }
            }
        } catch(Exception $e) {}

        if (!is_array($this->data->targetgroups)) {
            $this->data->targetgroups = array();
        }
        if (!is_array($this->data->types)) {
            $this->data->types = array();
        }

        $this->context = $this->event->context;
        $this->can_manage = (has_capability('mod/confman:manage', $this->context));
        $this->can_rate = (has_capability('mod/confman:rate', $this->context));
        $this->had_token = ($this->token != "" && @$this->data->token == $this->token);
        $this->is_obsolete = (time() > $this->event->submissionend);

        $this->can_view = ($this->had_token || $this->can_manage || $this->can_rate);
        $this->can_edit = ($this->id == 0 || $this->had_token || $this->can_manage);

        $this->data->files = array();
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_confman', 'content', $this->id);
        foreach ($files AS $file) {
            if (str_replace('.', '', $file->get_filename()) != ""){
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                $this->data->files[] = array(
                    'filename' => $file->get_filename(),
                    'url' => '' . $url,
                );
            }
        }

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

        if (optional_param("store_comment", 0, PARAM_INT) == 1) {
            $this->comment_store();
        }
    }
    public function get_table() {
        $table = new html_table();
        $table->width = '80%';
        $table->size = array('150', '');
        $table->data = array(
            new html_table_row(array(get_string('item:event', 'confman'), $this->event->name)),
            new html_table_row(array(get_string('item:title', 'confman'), $this->data->title)),
            new html_table_row(array(get_string('item:contributor', 'confman'), '<a href="mailto:' . $this->data->email . '">' . $this->data->contributor . '</a>')),
            new html_table_row(array(get_string('item:organization', 'confman'), $this->data->organization)),
            new html_table_row(array(get_string('item:type', 'confman'), implode(', ', $this->data->types))),
            new html_table_row(array(get_string('item:targetgroup', 'confman'), implode(', ', $this->data->targetgroups))),
            new html_table_row(array(get_string('item:description', 'confman'), $this->data->description)),
            new html_table_row(array(get_string('item:memo', 'confman'), $this->data->memo)),
            new html_table_row(array(get_string('item:files', 'confman'), $this->get_files())),
        );
        return $table;
    }
    /**
     * Returns all files of this in various formats.
     * @param format array, csv or html, default 'html'
     * @param delimiter between files, default ', '
     * @return files in the desired format
     */
    public function get_files($format = 'html', $delimiter = ', ') {
        switch($format) {
            case 'html':
                if (count($this->data->files) == 0) return get_string('none');
                $files = array();
                foreach ($this->data->files AS $file) {
                    $files[] = '<a href="' . $file['url'] . '" target="_blank">' . $file['filename'] . '</a>';
                }
                return implode($delimiter, $files);
            break;
            case 'csv':
            break;
            case 'array':
                return $this->data->files;
            break;
        }
    }
    public function get_title() {
        $title = 'n/a';
        if ($this->can_view && !empty($this->data->title)) {
            $title = $this->event->name . ": " . $this->data->title;
        } else if ($this->id == 0) {
            $title = $this->event->name;
        } else {
            $title = get_string('pluginname', 'confman');
        }
        return $title;
    }

    /**
     * Stores a file from a base64 encoded string.
     */
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
    /**
     * Removes a specific file.
     */
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

        if (!$file) return true;

        if ($file) {
            return $file->delete();
        }
    }

    public function store($data, $sendmail = 1) {
        // Remove our repatcha-replacement calculation.
        $itemcheck = cache::make('mod_confman', 'itemcheck');
        $itemcheck->set('itemcheck', '');

        if (!empty($data->isitemform)) {
            $data->description = $data->description['text'];
            $data->memo = $data->memo['text'];
            for($a = 0; $a < count($this->event->targetgroups); $a++) {
                if (!empty($data->{'targetgroup_' . $a})) {
                    $data->targetgroups[] = $this->event->targetgroups[$a]['targetgroup'];

                }
                unset($data->{'targetgroup_' . $a});
            }
            $data->types = array();
            for($a = 0; $a < count($this->event->types); $a++) {
                if (!empty($data->{'type_' . $a})) {
                    $data->types[] = $this->event->types[$a];
                }
                unset($data->{'type_' . $a});
            }
        }

        // Pack metadata into JSON.
        $c = (object) array();
        foreach(self::$packed_vars AS $var) {
            if (isset($data->{$var})) {
                $c->{$var} = $data->{$var};
            } else {
                $c->{$var} = '';
            }
        }
        $data->contents = json_encode($c);

        if (empty($data->token)) {
            $data->token = md5(date("Y-m-d H:i:s").rand(0, 1000));
        }

        global $DB;
        if ($data->id > 0) {
            $mailaction = "update";
            $DB->update_record('confman_items', $data);
        } else {
            $mailaction = "creation";
            $data->created = time();
            $data->modified = time();
            $this->id = $DB->insert_record('confman_items', $data, true);
        }
        $this->data = $data;

        $this->token = $this->data->token;

        if ($sendmail) {
            $this->mail("mail", $mailaction);
        }
    }

    public function manage_link() {
        return $this->confman . 'index.php?event=' . $this->event->id . '&id=' . $this->id . '&token=' . @$this->data->token;
    }

    public function mail($type = "mail", $action = "") {
        if (empty($this->event->{'mail_contributor_' . $action}) && empty($this->event->{'mail_organizer_' . $action})) return;
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
        $comments = array();
        if (!empty($this->eventid) && !empty($this->id)) {
            $dbcomments = $DB->get_records_sql(
                'SELECT * FROM {confman_comments} WHERE eventid=? AND itemid=? ORDER BY created DESC',
                array($this->eventid, $this->id)
            );
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
        }

        // Use $type instead of 'mail' to make various templates.
        $messagehtml = file_get_contents($CFG->dirroot."/mod/confman/templates/mail.html");

        $templatelines = explode("{", $messagehtml);
        $lines = array();
        // Required for mail-templates.
        $this->manageLink = $this->manage_link();

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

        if (!empty($this->event->{'mail_contributor_' . $action})) {
            email_to_user($touser, $fromuser, $subject, $messagetext, $messagehtml, "", true);
        }
        if (!empty($this->event->{'mail_organizer_' . $action})) {
            email_to_user($fromuser, $touser, $subject, $messagetext, $messagehtml, "", true);
        }

        if ($this->debug) {
            var_dump($touser);
            var_dump($fromuser);
            var_dump($messagehtml);
            var_dump($messagetext);
        }
    }

    /**
     * Prepares some data before it can be printed from mustache.
     */
    public function prepare_output() {
        global $CFG, $OUTPUT;
        $this->data->eventname = $this->event->name;
        $this->data->contributor = $this->data->title_pre;
        if (!empty($this->data->contributor)) $this->data->contributor .= ' ';
        $this->data->contributor .= $this->data->firstname . ' ' . $this->data->lastname;
        if (!empty($this->data->title_post)) $this->data->contributor .= ', ';
        $this->data->contributor .= $this->data->title_post;

        $this->data->actions = array();
        if ($this->can_view) {
            $this->data->actions[] = array(
                'classname' => 'preview',
                'icon' => $CFG->wwwroot . '/pix/t/preview.svg',
                'label' => get_string('view', 'core'),
                'url' => $CFG->wwwroot . '/mod/confman/index.php?event=' . $this->event->id . '&id=' . $this->id . '&preview=1',
            );
        }
        if ($this->can_edit) {
            $this->data->actions[] = array(
                'classname' => 'edit',
                'icon' => $CFG->wwwroot . '/pix/i/settings.svg',
                'label' => get_string('edit', 'core'),
                'url' => $this->manage_link(),
            );
        }
        if ($this->can_manage) {
            $icon = (!empty($this->data->approved) && $this->data->approved > 0) ? 'completion-auto-pass' : 'completion-auto-n';
            $this->data->actions[] = array(
                'classname' => 'approve',
                'icon' => $CFG->wwwroot . '/pix/i/' . $icon . '.svg',
                'label' => get_string('actions:approve', 'confman'),
                'onclick' => 'var a = this; require(["mod_confman/main"], function(MAIN) { MAIN.set_approved("' . $CFG->wwwroot . '", ' . $this->id . ', "' . $this->token . '", a); }); return false;',
                'url' => '#',
            );
        }
    }

    /**
     * Calls set_data for a form with its own data.
     * @param dataform object of type moodle_form
     */
    public function set_form_data($dataform) {
        $data = clone($this->data);
        $data->description = array(
            'format' => 1,
            'text' => $data->description,
        );
        $data->memo = array(
            'format' => 1,
            'text' => $data->memo,
        );
        $dataform->set_data($data);
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

function mod_confman_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options=array()) {
    // Make sure the filearea is one of those used by the plugin.

    if (!in_array($filearea, array('content', 'introx'))) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Users may want access to their submissions files even though they are not logged in to moodle.
    // Therefore we stored in the CACHE if the user had access to the item.
    // Make sure the user has access to item.

    $cache = cache::make('mod_confman', 'hadtokenfor');
    $hadtokenfor = $cache->get('hadtokenfor');
    if ($filearea == 'content' && (!$hadtokenfor || !in_array($itemid, $hadtokenfor))) {
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

function confman_add_instance($event) {
    global $DB, $COURSE;
    $event->course = $COURSE->id;
    $event->created = time();
    $event->cmid = $event->coursemodule;

    $event->id = $DB->insert_record('confman', $event, true);
    if ($event->id > 0) {
        // Now receive files.
        $modcontext = context_module::instance($event->cmid);
        $draftid = file_get_submitted_draft_itemid('introeditor');
        if (!empty($draftid)) {
            file_save_draft_area_files(
                $draftid, $modcontext->id, 'mod_confman', 'introx', $event->id,
                array('subdirs'=>true)
            );
        }
    }

    return $event->id;
}
function confman_update_instance($event) {
    global $DB, $COURSE;
    $event->id = $event->instance;
    $event->course = $COURSE->id;
    $event->cmid = $event->coursemodule;

    // Now receive files.
    $modcontext = context_module::instance($event->cmid);
    $draftid = file_get_submitted_draft_itemid('introeditor');

    $event->intro = file_save_draft_area_files(
        $draftid, $modcontext->id, 'mod_confman', 'introx', $event->id,
        array('subdirs'=>true), $event->intro
    );
    $DB->update_record('confman', $event);

    return true;
}
function confman_delete_instance($id) {
    global $DB;
    $DB->delete_records('confman_comments', array('eventid' => $id));
    $DB->delete_records('confman_items', array('event' => $id));
    $DB->delete_records('confman', array('id' => $id));

    return true;
}
