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
 * Course list block.
 *
 * @package    block_aoacompletedcourse_list
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once $CFG->dirroot . '/course/lib.php';
include_once $CFG->libdir . '/coursecatlib.php';

class block_aoacompletedcourse_list extends block_list {
	function init() {
		$this->title = get_string('pluginname', 'block_aoacompletedcourse_list');
	}

	function has_config() {
		return true;
	}

	function get_content() {
		global $CFG, $USER, $DB, $OUTPUT;

		if ($this->content !== NULL) {
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		$icon = '<img src="' . $OUTPUT->pix_url('i/course') . '" class="icon" alt="" />';

		$adminseesall = true;
		if (isset($CFG->block_aoacompletedcourse_list_adminview)) {
			if ($CFG->block_aoacompletedcourse_list_adminview == 'own') {
				$adminseesall = false;
			}
		}

		if (empty($CFG->disablemycourses) and isloggedin() and !isguestuser() and
			!(has_capability('moodle/course:update', context_system::instance()) and $adminseesall)) {
			// Just print My Courses
			// As this is producing navigation sort order should default to $CFG->navsortmycoursessort instead
			// of using the default.
			if (!empty($CFG->navsortmycoursessort)) {
				$sortorder = 'visible DESC, ' . $CFG->navsortmycoursessort . ' ASC';
			} else {
				$sortorder = 'visible DESC, sortorder ASC';
			}

			$i = 1;
			$comps = $DB->get_recordset('course_completions', array('userid' => $USER->id), 'timecompleted desc');
			
			foreach ($comps as $comp) {
				if($comp->timecompleted != NULL){
					if($i++ < 5){
						$course = $DB->get_record('course', array('id' => $comp->course));
						$coursecontext = context_course::instance($course->id);
						$linkcss = $course->visible ? " class=\"comp\" " : " class=\"dimmed oldcourse\" ";
						if ($course->visible == 0) {
							$info = ' <i class="fa fa-info-circle" aria-hidden="true" title="This course is no longer available."></i>';
							$this->content->items[]= "<p><span $linkcss>" . format_string($course->fullname) . $info . "</span></p>";
						} else {
							$this->content->items[]= "<p><a $linkcss title=\"" . format_string($course->fullname, true, array('context' => $coursecontext)) . "\" " .
							"href=\"$CFG->wwwroot/course/view.php?id=$course->id\">" . format_string($course->fullname) . "</a></p>";
						}
					}
				}

			}
			$comps->close();
			if ($this->content->items) {
				$this->content->footer = html_writer::link(new moodle_url('/blocks/aoacompletedcourse_list/report.php',
					array('userid' => $USER->id)),
					get_string('morecourses', 'block_aoacompletedcourse_list'));
				$this->title = 'COURSES COMPLETED';
				// make sure we don't return an empty list
				return $this->content;
			} else {
				return '';
			}
		}
	}

	/**
	 * Returns the role that best describes the course list block.
	 *
	 * @return string
	 */
	public function get_aria_role() {
		return 'navigation';
	}
}
