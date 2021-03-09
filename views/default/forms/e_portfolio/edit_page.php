<div class="contentWrapper">

<?php

$action = "e_portfolio/edit_page";
$e_portfolio_page = $vars['entity'];
$e_portfolio_page_guid = $e_portfolio_page->getGUID();

$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($container instanceof ElggGroup) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
}

if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {

      $rating_opened = elgg_echo('e_portfolio:rating_opened');
      $form_body .= "<p>" . $rating_opened . "</p>";
      echo elgg_echo($form_body);

} else {

if (!elgg_is_sticky_form('edit_page_e_portfolio')) {
   $title = $e_portfolio_page->title;
   $skills = $e_portfolio_page->skills;
   $reflections = $e_portfolio_page->reflections;
   $allow_comments = $e_portfolio_page->allow_comments;
   $tags = $e_portfolio_page->tags;
   $access_id = $e_portfolio_page->access_id;
} else {
   $title = elgg_get_sticky_value('edit_page_e_portfolio','title');
   $skills = elgg_get_sticky_value('edit_page_e_portfolio','skills');
   $reflections = elgg_get_sticky_value('edit_page_e_portfolio','reflections');
   $allow_comments = elgg_get_sticky_value('edit_page_e_portfolio','allow_comments');
   $tags = elgg_get_sticky_value('edit_page_e_portfolio','tags');
   $access_id = elgg_get_sticky_value('edit_page_e_portfolio','access_id');
}

elgg_clear_sticky_form('edit_page_e_portfolio');

$allow_comments = elgg_echo('e_portfolio:allow_comments');
if ($allow_comments){
   $selected_allow_comments = "checked = \"checked\"";
} else {
   $selected_allow_comments = "";
}
$tag_label = elgg_echo('tags');
$tag_input = elgg_view('input/tags', array('name' => 'tags', 'value' => $tags));
$access_label = elgg_echo('access');
$access_input = elgg_view('input/access', array('name' => 'access_id', 'value' => $access_id));
$submit_input_save = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_page')));
$submit_input_finish = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_page_finish')));

?>

<form action="<?php echo elgg_get_site_url()."action/".$action?>" name="edit_page_e_portfolio" enctype="multipart/form-data" method="post">

<?php echo elgg_view('input/securitytoken'); ?>

<p>
<b><?php echo elgg_echo("e_portfolio:title"); ?></b><br>
<?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:skills"); ?></b><br>
<?php echo elgg_view("input/longtext", array('name' => 'skills', 'value' => $skills)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:reflections"); ?></b><br>
<?php echo elgg_view("input/longtext", array('name' => 'reflections', 'value' => $reflections)); ?>
</p>
<p>
<b>
<?php echo "<input type = \"checkbox\" name = \"allow_comments\" $selected_allow_comments> $allow_comments"; ?>
</b>         
</p><br>
<p>
<b>
<?php echo $tag_label; ?></b><br>
<?php echo $tag_input; ?></p><br>
<p>
<b><?php echo $access_label; ?></b><br>
<?php echo $access_input; ?>
</p>

<?php
echo "$submit_input_save $submit_input_finish";
?>
<input type="hidden" name="e_portfolio_page_guid" value="<?php echo $e_portfolio_page_guid; ?>">

</form>

<?php
}
?>

</div>
