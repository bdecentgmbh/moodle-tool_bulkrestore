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
 * Language strings.
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Bulk restore courses';
$string['bulkrestore:restore'] = 'Bulk restore courses from backup files';

// Upload form.
$string['backupfiles'] = 'Backup files (.mbz or .zip)';
$string['backupfiles_help'] = 'Upload one or more Moodle course backup (.mbz) files, and/or .zip archives that each bundle several .mbz files. Every backup found is restored into a new course in the target category. The restores run in the background, so you can close this page once they are queued.';
$string['targetcategory'] = 'Target category';
$string['targetcategory_help'] = 'The category in which the new restored courses will be created.';
$string['restore'] = 'Restore';

// Queueing feedback.
$string['queuednone'] = 'No course backups (.mbz) were found in the upload.';
$string['queued'] = '{$a} course backup(s) queued for restore. They will be restored in the background and appear below as they complete.';

// Status table.
$string['statusheading'] = 'Recent bulk restores';
$string['statusnone'] = 'No bulk restores have been queued yet.';
$string['col_filename'] = 'Backup file';
$string['col_category'] = 'Target category';
$string['col_course'] = 'Restored course';
$string['col_status'] = 'Status';
$string['col_detail'] = 'Detail';
$string['col_time'] = 'Queued';
$string['status_queued'] = 'Queued';
$string['status_running'] = 'Restoring';
$string['status_done'] = 'Done';
$string['status_failed'] = 'Failed';

// Task.
$string['taskname'] = 'Bulk restore a course backup';
$string['restoredcoursename'] = 'Restored: {$a}';

// Privacy.
$string['privacy:metadata'] = 'The Bulk restore courses plugin does not store any personal data itself; uploaded backup files are processed by the core backup and restore subsystem.';
