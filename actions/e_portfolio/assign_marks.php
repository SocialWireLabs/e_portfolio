<?php

gatekeeper();

$task_guid = get_input('task_guid');
$task = get_entity($task_guid);

if (!$task->qualify_opened) {
   register_error(elgg_echo('e_portfolio:error_rating_closed'));
} else {

$user_guid = elgg_get_logged_in_user_guid();
	
$rating = get_input("rating");
$mark_type = get_input("mark_type");
$page_number = get_input("page_number");
$owner_guid = get_input('owner_guid');

$access = elgg_set_ignore_access(true);
$marks = socialwire_marks_get_marks(null, $owner_guid, $task_guid, $container_guid, $page_number);
if ($marks){
   socialwire_marks_update_mark($marks[0]->getGUID(), $rating, $mark_type, $page_number);
} else {
   if (strcmp($rating,"not_qualified")!=0) 
      socialwire_marks_create_mark($user_guid,$owner_guid,$task_guid,$rating,$page_number);
}
elgg_set_ignore_access($access);

//System message
system_message(elgg_echo("e_portfolio:marks_assigned"));

}

//Forward
forward($_SERVER['HTTP_REFERER']);

?>