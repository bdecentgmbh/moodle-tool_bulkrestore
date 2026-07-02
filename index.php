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
 * Bulk restore courses: upload multiple .mbz files and queue a background restore for each.
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use tool_bulkrestore\form\upload_form;

admin_externalpage_setup('toolbulkrestore');

$context = context_system::instance();
require_capability('tool/bulkrestore:restore', $context);

$PAGE->set_url(new moodle_url('/admin/tool/bulkrestore/index.php'));
$PAGE->set_title(get_string('pluginname', 'tool_bulkrestore'));

$mform = new upload_form();

if ($data = $mform->get_data()) {
    require_sesskey();

    // Read the uploaded backups straight from the draft area.
    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->backupfiles,
        'filename', false);

    $queued = 0;
    foreach ($draftfiles as $draftfile) {
        $ext = strtolower(pathinfo($draftfile->get_filename(), PATHINFO_EXTENSION));
        if ($ext === 'zip') {
            // A zip may bundle several backups: unpack it and queue each .mbz inside.
            foreach (\tool_bulkrestore\helper::extract_mbz_from_zip($draftfile) as $mbzpath) {
                \tool_bulkrestore\helper::queue_backup($context, $USER->id, $data->categoryid,
                    basename($mbzpath), $mbzpath);
                $queued++;
            }
        } else {
            // A directly uploaded .mbz backup.
            \tool_bulkrestore\helper::queue_backup($context, $USER->id, $data->categoryid,
                $draftfile->get_filename(), $draftfile);
            $queued++;
        }
    }

    // Clean up the draft area.
    $fs->delete_area_files($usercontext->id, 'user', 'draft', $data->backupfiles);

    $notice = $queued
        ? get_string('queued', 'tool_bulkrestore', $queued)
        : get_string('queuednone', 'tool_bulkrestore');
    redirect($PAGE->url, $notice,
        null, $queued ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING);
}

// Query recent restores for the status table.
$tracks = $DB->get_records('tool_bulkrestore_task', null, 'timecreated DESC', '*', 0, 50);

// Auto-refresh while anything is still queued or running.
foreach ($tracks as $track) {
    if ($track->status === 'queued' || $track->status === 'running') {
        $PAGE->set_periodic_refresh_delay(10);
        break;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_bulkrestore'));

$mform->display();

// Status table.
echo $OUTPUT->heading(get_string('statusheading', 'tool_bulkrestore'), 3);

if (!$tracks) {
    echo $OUTPUT->notification(get_string('statusnone', 'tool_bulkrestore'),
        \core\output\notification::NOTIFY_INFO);
} else {
    $categories = core_course_category::make_categories_list();

    $table = new html_table();
    $table->head = [
        get_string('col_filename', 'tool_bulkrestore'),
        get_string('col_category', 'tool_bulkrestore'),
        get_string('col_course', 'tool_bulkrestore'),
        get_string('col_status', 'tool_bulkrestore'),
        get_string('col_detail', 'tool_bulkrestore'),
        get_string('col_time', 'tool_bulkrestore'),
    ];
    $table->attributes['class'] = 'generaltable';

    $badges = [
        'queued' => 'badge bg-secondary text-white',
        'running' => 'badge bg-info text-white',
        'done' => 'badge bg-success text-white',
        'failed' => 'badge bg-danger text-white',
    ];

    foreach ($tracks as $track) {
        // Restored course link (only once the course exists).
        if ($track->courseid && $DB->record_exists('course', ['id' => $track->courseid])) {
            $course = get_course($track->courseid);
            $coursecell = html_writer::link(
                new moodle_url('/course/view.php', ['id' => $track->courseid]),
                format_string($course->fullname));
        } else {
            $coursecell = '-';
        }

        $badgeclass = $badges[$track->status] ?? 'badge bg-secondary';
        $statuscell = html_writer::span(
            get_string('status_' . $track->status, 'tool_bulkrestore'), $badgeclass);

        $catname = $categories[$track->categoryid] ?? (string)$track->categoryid;

        $table->data[] = [
            s($track->filename),
            $catname,
            $coursecell,
            $statuscell,
            $track->status === 'failed' ? s($track->statusdetail) : '',
            userdate($track->timecreated),
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
