<?php
/**
 * @author Dmitry Ketov <dketov@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block
 * @subpackage course_contacts
 *
 * Block: Course Contacts
 */

require_once($CFG->dirroot.'/user/profile/lib.php');

class block_course_contacts extends block_base {
    function init() {
      $this->title = get_string('coursecontact', 'admin'); 
    }

    function get_content () {
      global $SESSION, $OUTPUT, $CFG, $USER, $DB, $COURSE;

      $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id, MUST_EXIST);
      $coursecontactroles = explode(',', get_config('', 'coursecontact'));
      $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));

      ob_start();
      $seen = array();
      foreach ($coursecontactroles as $roleid) {
        $contacts = get_role_users($roleid, $coursecontext);
/*
	if(!empty($contacts)) {
          echo html_writer::tag('h3', reset($contacts)->rolename);
        }
*/

        foreach($contacts as $contact) {
	  if(in_array($contact->id, $seen)) {
            continue;
          }

	  echo html_writer::start_tag('div', array('class' => 'person'));
	  $seen[] = $contact->id;
          echo $OUTPUT->user_picture($contact, array('class' => 'profilepicture'));

          $record = $DB->get_record('user', array('id' => $contact->id));
	  echo html_writer::tag('span', get_string('fullnamedisplay', '', $contact),
	         array('class' => 'fullname'));

	  $link = new moodle_url('/message/index.php', array('id' => $contact->id));
	  $icon = new pix_icon('t/message', get_string('messageselectadd'));
          echo html_writer::tag('div',
                 html_writer::link($link->out(), get_string('messageselectadd')) . '&nbsp;' .
	         html_writer::link($link->out(), $OUTPUT->render($icon)),
	         array('class' => 'message')
          );

          if ($record->skype && !isset($hiddenfields['icqnumber'])) {
	    $link = 'callto:' . $record->skype;
            $icon = new moodle_url('http://mystatus.skype.com/smallicon/' . $record->skype); 
            echo html_writer::tag('div',
                   get_string('skypeid') . ':&nbsp' .
                   html_writer::link($link, $record->skype) . '&nbsp' .
	           html_writer::empty_tag('img', array('src' => $icon, 'alt' => get_string('status'))),
	           array('class' => 'skype')
            );
          }

          if ($record->icq && !isset($hiddenfields['icqnumber'])) {
	    $link = new moodle_url('http://web.icq.com/wwp', array('uin' => $record->icq));
            $icon = new moodle_url('http://web.icq.com/whitepages/online', 
                          array('uin' => $record->icq, 'img' => 5));
            echo html_writer::tag('div',
                   get_string('icqnumber') . ':&nbsp' .
                   html_writer::link($link, $record->icq) . '&nbsp' .
	           html_writer::empty_tag('img', array('src' => $icon, 'alt' => get_string('status'))),
	           array('class' => 'icq')
            );
          }
	   
/*
          if ($fields = $DB->get_records('user_info_field')) {
            foreach ($fields as $field) {
              require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
              $newfield = 'profile_field_'.$field->datatype;
              $formfield = new $newfield($field->id, $contact->id);
              if ($formfield->data !== NULL) {
                echo html_writer::tag('div', 
                  $formfield->field->name . ':&nbsp' . $formfield->display_data(),
	          array('class' => 'infofield'));
              }
            }
          }
*/
          echo html_writer::end_tag('div');	
        }
      }
      $this->content->text = ob_get_contents();

      ob_end_clean();

      return $this->content;
    }
}
?>
