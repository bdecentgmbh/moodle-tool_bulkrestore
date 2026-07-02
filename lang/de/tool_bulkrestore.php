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
 * Language strings (German).
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Kurse per Massenwiederherstellung wiederherstellen';
$string['bulkrestore:restore'] = 'Kurse per Massenwiederherstellung aus Sicherungsdateien wiederherstellen';

// Upload form.
$string['backupfiles'] = 'Sicherungsdateien (.mbz oder .zip)';
$string['backupfiles_help'] = 'Laden Sie eine oder mehrere Moodle-Kurssicherungsdateien (.mbz) hoch und/oder .zip-Archive, die jeweils mehrere .mbz-Dateien bündeln. Jede gefundene Sicherung wird als neuer Kurs in der Zielkategorie wiederhergestellt. Die Wiederherstellungen laufen im Hintergrund, sodass Sie diese Seite schließen können, sobald sie in die Warteschlange eingereiht wurden.';
$string['targetcategory'] = 'Zielkategorie';
$string['targetcategory_help'] = 'Die Kategorie, in der die neu wiederhergestellten Kurse angelegt werden.';
$string['restore'] = 'Wiederherstellen';

// Queueing feedback.
$string['queuednone'] = 'Im Upload wurden keine Kurssicherungen (.mbz) gefunden.';
$string['queued'] = '{$a} Kurssicherung(en) für die Wiederherstellung in die Warteschlange eingereiht. Sie werden im Hintergrund wiederhergestellt und erscheinen unten, sobald sie abgeschlossen sind.';

// Status table.
$string['statusheading'] = 'Aktuelle Massenwiederherstellungen';
$string['statusnone'] = 'Es wurden noch keine Massenwiederherstellungen in die Warteschlange eingereiht.';
$string['col_filename'] = 'Sicherungsdatei';
$string['col_category'] = 'Zielkategorie';
$string['col_course'] = 'Wiederhergestellter Kurs';
$string['col_status'] = 'Status';
$string['col_detail'] = 'Detail';
$string['col_time'] = 'Eingereiht';
$string['status_queued'] = 'In Warteschlange';
$string['status_running'] = 'Wird wiederhergestellt';
$string['status_done'] = 'Fertig';
$string['status_failed'] = 'Fehlgeschlagen';

// Task.
$string['taskname'] = 'Kurssicherung per Massenwiederherstellung wiederherstellen';
$string['restoredcoursename'] = 'Wiederhergestellt: {$a}';

// Privacy.
$string['privacy:metadata'] = 'Das Plugin „Kurse per Massenwiederherstellung wiederherstellen“ speichert selbst keine personenbezogenen Daten; hochgeladene Sicherungsdateien werden vom Kern-Subsystem für Sicherung und Wiederherstellung verarbeitet.';
