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
 * Bulk restore upload form.
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkrestore\form;

defined('MOODLE_INTERNAL') || die();

use core_course_category;

require_once($CFG->libdir . '/formslib.php');

/**
 * Lets an administrator upload multiple .mbz files and pick a target category.
 */
class upload_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        // Multi-file upload of .mbz backups (or .zip archives bundling several), saved to a draft area.
        $mform->addElement('filemanager', 'backupfiles',
            get_string('backupfiles', 'tool_bulkrestore'), null, [
                'subdirs' => 0,
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'accepted_types' => ['.mbz', '.zip'],
            ]);
        $mform->addHelpButton('backupfiles', 'backupfiles', 'tool_bulkrestore');
        $mform->addRule('backupfiles', get_string('required'), 'required', null, 'client');

        // Target category for the newly restored courses.
        $categories = core_course_category::make_categories_list('moodle/course:create');
        $mform->addElement('select', 'categoryid',
            get_string('targetcategory', 'tool_bulkrestore'), $categories);
        $mform->addHelpButton('categoryid', 'targetcategory', 'tool_bulkrestore');
        $mform->addRule('categoryid', get_string('required'), 'required', null, 'client');
        $mform->setType('categoryid', PARAM_INT);

        $this->add_action_buttons(false, get_string('restore', 'tool_bulkrestore'));
    }
}
