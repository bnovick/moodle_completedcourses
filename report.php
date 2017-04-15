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
 * Version details
 *
 * Report certificates block
 * --------------------------
 * Displays all issued certificates for users with unique codes.
 * The certificates will also be issued for courses that have been archived since issuing of the certificates
 *
 * @copyright  2015 onwards Manieer Chhettri | Marie Curie, UK | <manieer@gmail.com>
 * @author     Manieer Chhettri | Marie Curie, UK | <manieer@gmail.com> | 2015
 * @package    block_aoacompletedcourse_list
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once '../../course/lib.php';
require_login();
$home = $CFG->wwwroot;
$url = new moodle_url('/blocks/aoacompletedcourse_list/report.php');
$title = 'Completed Courses';
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

// Check capabilities.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->requires->css('/blocks/aoacompletedcourse_list/styles.css'); 
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();

echo $OUTPUT->heading($title);

echo '<br />';

$table = new html_table();
$table->head = array('Course', 'Time Completed', 'Print Certificate');

$comps = $DB->get_recordset('course_completions', array('userid' => $USER->id), 'timecompleted desc');
foreach ($comps as $comp) {
	if ($comp->timecompleted != NULL) {
		$course = $DB->get_record('course', array('id' => $comp->course));
		$coursecontext = context_course::instance($course->id);
		$linkcss = $course->visible ? " class=\"comp\" " : " class=\"dimmed oldcourse\" ";

		//If course is hidden, look for saved cert in Moodledata

		if ($course->visible == 0) {

			$info = ' <i class="fa fa-info-circle" aria-hidden="true" title="This course is no longer available."></i>';
			$rcourse = "<p><span $linkcss>" . format_string($course->fullname) . $info . "</span></p>";
			$form = '<p>'.get_string('nocert', 'block_aoacompletedcourse_list').'</p>';
		}

		//If course is active, generate cert via PHP post
		 else {
			$rcourse = "<p><a $linkcss title=\"" . format_string($course->fullname, true, array('context' => $coursecontext)) . "\" " .
			"href=\"$CFG->wwwroot/course/view.php?id=$course->id\">" . format_string($course->fullname) . "</a></p>";

			if ($cert = $DB->get_record('course_modules', array('course' => $comp->course, 'module' => 23), 'id')) {

				$form = '<form method="post" action="'.$home.'/mod/certificate/view.php" target="_blank"><div><input type="submit" value="Download Certificate" id="single_button_certform" style = "color: white; background: #255584;"><button id = "mobilesub" type="submit">
  					<i class="fa fa-download"></i></button><input type="hidden" name="id" value="' . $cert->id . '"><input type="hidden" name="action" value="get"></div></form>';
			} else {
				$form = '<p>'.get_string('nocert', 'block_aoacompletedcourse_list').'</p>';
			}

		}
		$tc = userdate($comp->timecompleted);

		$table->data[] = array($rcourse, $tc, $form);
	}
}

$comps->close();
echo html_writer::table($table);
echo $OUTPUT->footer();
