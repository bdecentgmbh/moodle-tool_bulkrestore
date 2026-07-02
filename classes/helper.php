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
 * Bulk restore helper.
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkrestore;

defined('MOODLE_INTERNAL') || die();

/**
 * Shared logic for queueing background restores from uploaded backups.
 */
class helper {

    /**
     * Queue one background restore for a single .mbz backup.
     *
     * A tracking row is created first so the stored file and the adhoc task can both be
     * keyed on its id (one file per itemid). The backup is then copied into the plugin's
     * own file area and a restore task is queued.
     *
     * @param \context $context The system context the file area lives in.
     * @param int $userid The user who owns the restore.
     * @param int $categoryid Target category for the new course.
     * @param string $filename The .mbz filename (used as the display name and stored name).
     * @param \stored_file|string $source The backup as a stored_file, or a path on disk.
     * @return int The tracking row id.
     */
    public static function queue_backup(\context $context, int $userid, int $categoryid,
            string $filename, $source): int {
        global $DB;

        $track = new \stdClass();
        $track->filename = $filename;
        $track->categoryid = $categoryid;
        $track->status = 'queued';
        $track->userid = $userid;
        $track->timecreated = time();
        $track->timemodified = $track->timecreated;
        $trackid = $DB->insert_record('tool_bulkrestore_task', $track);

        // Copy the backup into our own file area under itemid = tracking row id.
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'tool_bulkrestore',
            'filearea' => 'backup',
            'itemid' => $trackid,
            'filepath' => '/',
            'filename' => $filename,
        ];
        $fs = get_file_storage();
        if ($source instanceof \stored_file) {
            $fs->create_file_from_storedfile($filerecord, $source);
        } else {
            $fs->create_file_from_pathname($filerecord, $source);
        }

        // Queue the background restore for this file.
        $task = new \tool_bulkrestore\task\restore_course_task();
        $task->set_userid($userid);
        $task->set_custom_data(['trackid' => $trackid]);
        $taskid = \core\task\manager::queue_adhoc_task($task);
        $DB->set_field('tool_bulkrestore_task', 'taskid', $taskid, ['id' => $trackid]);

        return $trackid;
    }

    /**
     * Extract a zip archive of backups and return the paths of the .mbz files it contains.
     *
     * The archive is unpacked into a per-request temp directory (auto-cleaned at the end
     * of the request); the returned paths are only valid until then, so callers must copy
     * the files into permanent storage (queue_backup() does this) before the request ends.
     *
     * @param \stored_file $zip The uploaded .zip archive.
     * @return string[] Absolute paths of the .mbz files found (searched recursively).
     */
    public static function extract_mbz_from_zip(\stored_file $zip): array {
        $tempdir = make_request_directory();
        get_file_packer('application/zip')->extract_to_pathname($zip, $tempdir);

        $found = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempdir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'mbz') {
                $found[] = $file->getPathname();
            }
        }
        sort($found);
        return $found;
    }
}
