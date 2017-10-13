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
$string['capability:missing'] = 'Dazu sind Sie nicht berechtigt!';

$string['event:name'] = 'Name';
$string['event:description'] = 'Beschreibung';
$string['event:submissionstart'] = 'Start für Einreichungen';
$string['event:submissionend'] = 'Deadline für Einreichungen';
$string['event:organizer'] = 'Ansprechperson';
$string['event:contact'] = 'Mailadresse der Ansprechperson';
$string['event:logo'] = 'Logo';
$string['event:submit'] = 'Event speichern';
$string['event:types'] = 'Beitragstypen';
$string['event:targetgroups'] = 'Zielgruppen';

$string['event:listall'] = 'Alle Einreichungen auflisten';
$string['event:submissions'] = 'Einreichungen';
$string['event:submission:open'] = 'Einreichungen offen';
$string['event:submission:link'] = 'Einreichungs-Link';
$string['event:submission:link:lbl'] = 'Externe Nutzer/innen können mit diesem Link Einreichungen tätigen.';
$string['event:stored'] = 'Ihr Event wurde gespeichert!';
$string['event:error'] = 'Ein Fehler ist passiert. Bitte prüfen Sie Ihre Eingaben!';
$string['event:invalidvalue'] = 'Ungültiger Wert gefunden!';
$string['event:file:infostorefirst'] = 'Bitte speichern Sie Ihre Eingaben bevor Sie Dateien hinzufügen!';

$string['form:submit'] = 'Speichern';

$string['code:embed'] = 'Embed Code';
$string['confman:manage'] = 'Einreichungen verwalten';
$string['confman:rate'] = 'Einreichungen bewerten';

$string['item:section:personaldata'] = 'Persönliche Daten';
$string['item:section:yoursubmission'] = 'Ihr Beitrag';
$string['item:section:files'] = 'Zusätzliche Dateien';

$string['item:event'] = 'Event';
$string['item:title'] = 'Titel';
$string['item:contributor'] = 'Einreicher/in';
$string['item:organization'] = 'Organisation';
$string['item:title_pre'] = 'Titel (vorangestellt)';
$string['item:firstname'] = 'Vorname';
$string['item:lastname'] = 'Nachname';
$string['item:title_post'] = 'Titel (nachgestellt)';
$string['item:email'] = 'e-Mail';
$string['item:email2'] = 'e-Mail Bestätigung';

$string['item:type'] = 'Art des Beitrags';
$string['item:targetgroup'] = 'Zielgruppe';
$string['item:description'] = 'Beschreibung';
$string['item:memo'] = 'Memo';
$string['item:file:append'] = 'Datei anfügen';
$string['item:submit'] = 'Beitrag speichern';
$string['item:manage_link'] = 'Beitrag verwalten';

$string['item:file'] = 'Datei';
$string['item:files'] = 'Dateien';

$string['item:stored'] = 'Ihr Beitrag wurde gespeichert!';
$string['item:error'] = 'Ein Fehler ist aufgetreten, bitte prüfen Sie Ihre Angaben!';
$string['item:invalidvalue'] = 'Ungültiger Wert gefunden!';
$string['item:obsolete'] = 'Der Zeitraum für Einreichungen ist abgelaufen. Sie können nicht mehr speichern oder bearbeiten!';
$string['item:file:infostorefirst'] = 'Bitte speichern Sie Ihren Beitrag einmal, bevor Sie Dateien hinzufügen!';
$string['item:you_can_modify'] = 'Sie können die Daten mit folgendem Link vewalten';

$string['item:check'] = 'Mensch oder Maschine?';

$string['comments'] = 'Kommentare';
$string['comment:add'] = 'Kommentar hinzufügen';
$string['comment:store'] = 'Kommentar speichern';
$string['comment:stored:success'] = 'Kommentar erfolgreich gespeichert';
$string['comment:stored:failed'] = 'Kommentar nicht gespeichert';

$string['user:external'] = 'Externe Person';

$string['error:view'] = 'Sie können diesen Eintrag nicht sehen.';
$string['error:missing:eventid'] = 'eventid fehlt';

$string['form:submit'] = 'Speichern';

$string['confman:manage'] = 'Verwalte Beiträge';
$string['confman:rate'] = 'Bewerte Beiträge';

$string['none'] = 'Keine';

$string['mail:subject:mail'] = $string['item:stored'];
$string['mail:subject:comment'] = 'Kommentar angefügt';
$string['mail:subject:file_append'] = 'Datei angefügt';
$string['mail:subject:file_delete'] = 'Datei gelöscht';

$string['mail:thankyou:mail'] = 'Sg. Referentin, Sg. Referent!<br /><br />
     Vielen Dank für Ihre Einreichung!<br /><br />
     Sie können nun Dateien zu Ihrem Beitrag auf der Plattform bis einschließlich {data:event:submissionend_readable} hochladen.
     Bitte beachten Sie, Ihre Unterlagen mit der entsprechenden CC Lizenz zu versehen - bzw. dies bei Ihren Unterlagen nachzubessern.<br /><br />
     Nochmals Vielen Dank für Ihre Einreichung und Ihr Referat!<br /><br />
     Herzliche Grüße im Namen des Organisationsteams!';

$string['mail:thankyou:comment'] = 'Sg. Referentin, Sg. Referent!<br /><br />
     Es wurde ein Kommentar bei Ihrer Einreichung gespeichert!<br /><br />
     Herzliche Grüße im Namen des Organisationsteams!';

$string['mail:thankyou:file_append'] = 'Sg. Referentin, Sg. Referent!<br /><br />
     Vielen Dank für den Upload einer Datei!<br /><br />
     Sie können innerhalb des Bearbeitungszeitraums, also bis einschließlich {data:event:submissionend_readable} hochladen, oder die vorhandenen wieder entfernen.
     Bitte beachten Sie, Ihre Unterlagen mit der entsprechenden CC Lizenz zu versehen - bzw. dies bei Ihren Unterlagen nachzubessern.<br /><br />
     Nochmals Vielen Dank für Ihre Einreichung und Ihr Referat!<br /><br />
     Herzliche Grüße im Namen des Organisationsteams!';

$string['mail:thankyou:file_delete'] = 'Sg. Referentin, Sg. Referent!<br /><br />
     Sie haben eine Datei gelöscht!<br /><br />
     Sie können innerhalb des Bearbeitungszeitraums, also bis einschließlich {data:event:submissionend_readable} hochladen, oder die vorhandenen wieder entfernen.
     Bitte beachten Sie, Ihre Unterlagen mit der entsprechenden CC Lizenz zu versehen - bzw. dies bei Ihren Unterlagen nachzubessern.<br /><br />
     Nochmals Vielen Dank für Ihre Einreichung und Ihr Referat!<br /><br />
     Herzliche Grüße im Namen des Organisationsteams!';

$string['defaults:targetgroups'] = "digi.komp 4#Primarstufe\ndigi.komp 8#Sekundarstufe I\ndigi.komp 12#Sekundarstufe II\ndigi.komp P#Lehrer/innenfortbildung";
$string['defaults:types'] = "Vortrag\nWorkshop";

$string['cachedef_itemcheck'] = 'Das Ergebnis jener Berechnung, die gegen Spambots helfen soll.';
$string['cachedef_hadtokenfor'] = 'Speichert alle ItemIDs für die der Nutzer einen Token hatte um den Download von Dateien zu ermöglichen.';
