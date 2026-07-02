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
 * Adhoc task that restores a single uploaded .mbz into a new course.
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkrestore\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Extracts one backup file and restores it into a brand new course.
 *
 * The heavy lifting is delegated entirely to the core restore subsystem, mirroring
 * the pattern used by admin/tool/recyclebin/classes/course_bin.php. One task is queued
 * per uploaded file so failures are isolated and long restores never block a request.
 */
class restore_course_task extends \core\task\adhoc_task {

    /**
     * A descriptive name shown in the admin task interface.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskname', 'tool_bulkrestore');
    }

    /**
     * Failed restores should not be retried automatically.
     *
     * @return bool
     */
    public function retry_until_success(): bool {
        return false;
    }

    /**
     * Run the restore.
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $data = $this->get_custom_data();
        $trackid = $data->trackid;

        $track = $DB->get_record('tool_bulkrestore_task', ['id' => $trackid]);
        if (!$track) {
            // Tracking row vanished (e.g. deleted); nothing to do.
            return;
        }

        $context = \context_system::instance();
        $userid = $track->userid;

        // Locate the stored .mbz (one file per tracking row, keyed on itemid = row id).
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_bulkrestore', 'backup', $trackid,
            'itemid, filepath, filename', false);
        $file = reset($files);

        if (!$file) {
            $this->mark_failed($track, get_string('error'));
            return;
        }

        // Mark as running so the status page reflects progress.
        $this->update_status($track, 'running');

        $newcourseid = null;
        $fulltempdir = null;
        try {
            // Extract the backup into a temp directory.
            $tempdir = \restore_controller::get_tempdir_name($context->id, $userid);
            $fulltempdir = make_backup_temp_directory($tempdir);
            $packer = get_file_packer('application/vnd.moodle.backup');
            $packer->extract_to_pathname($file, $fulltempdir);

            // Create the destination course. Its name is derived from the backup for a meaningful
            // placeholder; the restore itself replaces it with the (deduplicated) backup name.
            $info = \backup_general_helper::get_backup_information($tempdir);
            list($fullname, $shortname) = \restore_dbops::calculate_course_names(0,
                $info->original_course_fullname, $info->original_course_shortname);
            $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $track->categoryid);

            // Restore into the new course.
            $rc = new \restore_controller($tempdir, $newcourseid, \backup::INTERACTIVE_NO,
                \backup::MODE_GENERAL, $userid, \backup::TARGET_NEW_COURSE);
            if (!$rc->execute_precheck()) {
                $results = $rc->get_precheck_results();
                if (!empty($results['errors'])) {
                    $rc->destroy();
                    throw new \moodle_exception('restoreerror', 'error',
                        '', implode('; ', array_map('strval', $results['errors'])));
                }
            }
            $rc->execute_plan();
            $rc->destroy();

            // Success: record the course and clean up.
            $track->courseid = $newcourseid;
            $this->update_status($track, 'done');
            $fs->delete_area_files($context->id, 'tool_bulkrestore', 'backup', $trackid);
            fulldelete($fulltempdir);
        } catch (\Throwable $e) {
            // Roll back the half-created course and surface the error.
            if ($newcourseid && $DB->record_exists('course', ['id' => $newcourseid])) {
                delete_course($newcourseid, false);
            }
            if ($fulltempdir) {
                fulldelete($fulltempdir);
            }
            $this->mark_failed($track, $e->getMessage());
        }
    }

    /**
     * Persist a status change on the tracking row.
     *
     * @param \stdClass $track The tracking record (mutated in place).
     * @param string $status queued|running|done|failed
     */
    protected function update_status(\stdClass $track, string $status) {
        global $DB;
        $track->status = $status;
        $track->timemodified = time();
        $DB->update_record('tool_bulkrestore_task', $track);
    }

    /**
     * Mark a restore as failed with a detail message.
     *
     * @param \stdClass $track The tracking record.
     * @param string $detail The error detail to store.
     */
    protected function mark_failed(\stdClass $track, string $detail) {
        $track->statusdetail = $detail;
        $this->update_status($track, 'failed');
    }
}
