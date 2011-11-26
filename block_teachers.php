<?php
/**
 * @author Dmitry Ketov <dketov@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block
 * @subpackage teachers
 *
 * Block: Teachers
 */

require_once($CFG->dirroot.'/user/profile/lib.php');

class block_teachers extends block_base {
    function init() {
      $this->title = get_string('defaultcourseteacher');
    }

    function get_content () {
      global $SESSION, $OUTPUT, $CFG, $USER, $DB, $COURSE;

      $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id, MUST_EXIST);
      $coursecontactroles = explode(',', get_config('', 'coursecontact'));

      ob_start();

      foreach ($coursecontactroles as $roleid) {
        $teachers = get_role_users($roleid, $coursecontext);

        $this->title = '';
        foreach($teachers as $teacher) {
          $this->title .= $teacher->rolename . '<br/>';
          echo $OUTPUT->user_picture($teacher, array('class' => 'profilepicture'));

          $record = $DB->get_record('user', array('id' => $teacher->id));

	  $link = new moodle_url('/message/index.php', array('id' => $teacher->id));
	  $icon = new pix_icon('t/message', get_string('messageselectadd'));
          echo html_writer::tag('div',
                 html_writer::link($link->out(), get_string('messageselectadd')) . '&nbsp' .
	         html_writer::link($link->out(), $OUTPUT->render($icon)) 
          );

	  $link = 'callto:' . $record->skype;
          $icon = new moodle_url('http://mystatus.skype.com/smallicon/' . $record->skype); 
          echo html_writer::tag('div',
                 get_string('skypeid') . ':&nbsp' .
                 html_writer::link($link, $record->skype) . '&nbsp' .
	         html_writer::empty_tag('img', 
                   array('src' => $icon, 'alt' => get_string('status')))
          );

	  $link = new moodle_url('http://web.icq.com/wwp', array('uin' => $record->icq));
          $icon = new moodle_url('http://web.icq.com/whitepages/online', 
                        array('uin' => $record->icq, 'img' => 5));
          echo html_writer::tag('div',
                 get_string('icqnumber') . ':&nbsp' .
                 html_writer::link($link, $record->icq) . '&nbsp' .
	         html_writer::empty_tag('img', 
                   array('src' => $icon, 'alt' => get_string('status')))
          );
	   
          if ($fields = $DB->get_records('user_info_field')) {
            foreach ($fields as $field) {
              require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
              $newfield = 'profile_field_'.$field->datatype;
              $formfield = new $newfield($field->id, $teacher->id);
              echo html_writer::tag('div', 
                $formfield->field->name . ':&nbsp' .
		$formfield->display_data());
            }
          }
        }
      }
      $this->content->text = ob_get_contents();
      ob_end_clean();

      return $this->content;
    }
}
?>
