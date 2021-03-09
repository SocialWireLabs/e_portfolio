<?php

$e_portfolio_group_setup = $vars['entity'];
$page_number = $vars['page_number'];
$e_portfolio_group_setup_guid = $e_portfolio_group_setup->getGUID();

$user_id = elgg_get_logged_in_user_guid();
$subject_id = $e_portfolio_group_setup->group_guid;
$sort_by = $student;
$user_type = socialwire_marks_is_professor($user_id, $subject_id) ? 'professor' : 'student';
if ($user_type == 'professor') {
   if ((!$e_portfolio_group_setup->qualify_opened)||($e_portfolio_group_setup->var_pages)||($e_portfolio_group_setup->use_rubric))
      $user_type = 'professor_as_student';
}
$mark_type = socialwire_marks_get_mark_type($e_portfolio_group_setup);

$body = "";

if ($page_number == "all") {
   if ($e_portfolio_group_setup->var_pages) {
      $access = elgg_set_ignore_access(true);
      $marks = socialwire_marks_get_task_marks($e_portfolio_group_setup, $sort_by, null, "-1");
      $body .= elgg_view('socialwire_marks/marks', array('marks' => $marks, 'view_type' => 'task', 'user_type' => $user_type, 'mark_type' => $mark_type, 'page_number' => "-1"));
      elgg_set_ignore_access($access);
   } else {
      $i = 1;
      while ($i<=$e_portfolio_group_setup->num_pages) {
         $word_marks =  elgg_echo('item:object:socialwire_mark') . " (" . elgg_echo('e_portfolio:page') . ": " . $i . ")";;
	 $url_marks = elgg_add_action_tokens_to_url(elgg_get_site_url() . "e_portfolio/marks/$e_portfolio_group_setup_guid/$i");
	 $body .= "<p><a href=\"{$url_marks}\">{$word_marks}</a></p>";
         $i=$i+1;
      }
   }
} else {
   $access = elgg_set_ignore_access(true);
   $marks = socialwire_marks_get_task_marks($e_portfolio_group_setup, $sort_by, null,$page_number);
   $body .= elgg_view('socialwire_marks/marks', array('marks' => $marks, 'view_type' => 'task', 'user_type' => $user_type, 'mark_type' => $mark_type, 'page_number' => $page_number));
   elgg_set_ignore_access($access);
}
echo $body;

?>
