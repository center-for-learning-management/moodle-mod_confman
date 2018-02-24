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
 * @package   mod_confman
 * @copyright 2017 Digital Education Society (http://www.dibig.at)
 * @author    Robert Schrenk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'ConfMan';
$string['modulenameplural'] = 'ConfMans';
$string['pluginname'] = 'ConfMan';
$string['pluginadministration'] = 'ConfMan';
$string['capability:missing'] = 'Missing required Capability';

$string['event:name'] = 'Title';
$string['event:description'] = 'Description';
$string['event:submissionstart'] = 'Start of Submission';
$string['event:submissionend'] = 'End of Submission';
$string['event:organizer'] = 'Contact-Person';
$string['event:contact'] = 'Contact-Mail';
$string['event:logo'] = 'Logo';
$string['event:submit'] = 'Store Event';
$string['event:types'] = 'Types of Submissions';
$string['event:targetgroups'] = 'Targetgroups';

$string['event:listall'] = 'List all Submissions';
$string['event:submissions'] = 'Submissions';
$string['event:submission:open'] = 'Submissions open';
$string['event:submission:link'] = 'Submission-Link';
$string['event:submission:link:lbl'] = 'External Users can enter their submissions using this link.';
$string['event:stored'] = 'Your Event was saved!';
$string['event:error'] = 'An error occured, please check your data below!';
$string['event:invalidvalue'] = 'Invalid value given!';
$string['event:file:infostorefirst'] = 'Please store your event once before you can attach files!';

$string['form:submit'] = 'Save';

$string['code:embed'] = 'Embed Code';
$string['confman:manage'] = 'Manage ConfMan Entries';
$string['confman:rate'] = 'Rate ConfMan Entries';

$string['item:section:personaldata'] = 'Personal Data';
$string['item:section:yoursubmission'] = 'Your Submission';
$string['item:section:files'] = 'Additional Files';

$string['item:event'] = 'Event';
$string['item:title'] = 'Title';
$string['item:organization'] = 'Organization';
$string['item:contributor'] = 'Contributor';
$string['item:title_pre'] = 'Title (before name)';
$string['item:firstname'] = 'Firstname';
$string['item:lastname'] = 'Lastname';
$string['item:title_post'] = 'Title (after name)';
$string['item:email'] = 'e-Mail';
$string['item:email2'] = 'e-Mail Confirmation';

$string['item:type'] = 'Type of Submission';
$string['item:targetgroup'] = 'Targetgroup';
$string['item:description'] = 'Description';
$string['item:memo'] = 'Memo';
$string['item:file:append'] = 'Append File';
$string['item:submit'] = 'Add new Submission';
$string['item:manage_link'] = 'Manage Submission';

$string['item:file'] = 'File';
$string['item:files'] = 'Files';

$string['item:stored'] = 'Your submission was saved!';
$string['item:error'] = 'An error occured, please check your data below!';
$string['item:invalidvalue'] = 'Invalid value given!';
$string['item:obsolete'] = 'Submission deadline exceeded. You can not submit/modify items!';
$string['item:file:infostorefirst'] = 'Please store your submission once before you can attach files!';
$string['item:you_can_modify'] = 'You can modify this data using the following link';

$string['item:check'] = 'Prove you are a human';

$string['comments'] = 'Comments';
$string['comment:add'] = 'Add comment';
$string['comment:store'] = 'Save comment';
$string['comment:stored:success'] = 'Comment saved successfully';
$string['comment:stored:failed'] = 'Comment could not be saved';

$string['user:external'] = 'External user';

$string['error:view'] = 'You can not view this item.';
$string['error:missing:eventid'] = 'Missing eventid';

$string['form:submit'] = 'Save';

$string['confman:manage'] = 'Manage ConfMan Entries';
$string['confman:rate'] = 'Rate ConfMan Entries';

$string['none'] = 'None';

$string['mail:subject:mail'] = 'Your submission was saved!';
$string['mail:subject:comment'] = 'Comment added';
$string['mail:subject:file_append'] = 'File added';
$string['mail:subject:file_delete'] = 'File removed';
$string['mail:thankyou:mail'] = 'Dear Contributor,<br /><br />
     Thank you for your submission!<br /><br />
     You can now upload files to your submission until {data:event:submissionend_readable}. Please make sure to licence your materials under a Creative Commons Licence
     and include the licence information inside your materials.<br /><br />
     Kind regards on behalf of the comittee!';

$string['mail:thankyou:comment'] = 'Dear Contributor,<br /><br />
     A comment was added to your submission!<br /><br />
     Kind regards on behalf of the comittee!';

$string['mail:thankyou:file_append'] = 'Dear Contributor,<br /><br />
     Thank you for uploading a file to your submission!<br /><br />
     You can upload additional files to your submission until {data:event:submissionend_readable}. Please make sure to licence your materials under a Creative Commons Licence
     and include the licence information inside your materials.<br /><br />
     Kind regards on behalf of the comittee!';

$string['mail:thankyou:file_delete'] = 'Dear Contributor,<br /><br />
     You removed a file from your submission!<br /><br />
     You can upload files to your submission until {data:event:submissionend_readable}. Please make sure to licence your materials under a Creative Commons Licence
     and include the licence information inside your materials.<br /><br />
     Kind regards on behalf of the comittee!';

$string['defaults:targetgroups'] = 'group A#Description*group B#Description*group C#Description*group D#Description';
$string['defaults:types'] = 'Presentation*Workshop';

$string['cachedef_itemcheck'] = 'This is result of the calculation that rejects spam bots';
$string['cachedef_hadtokenfor'] = 'Stores all itemids that we had a token for in order to make download files possible';
