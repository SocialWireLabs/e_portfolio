<div class="contentWrapper">

<?php

$action = "e_portfolio/import_pages";
$e_portfolio = $vars['entity'];
$e_portfoliopost = $e_portfolio->getGUID();
$container_guid = get_entity($e_portfoliopost)->container_guid;
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

$user_guid = elgg_get_logged_in_user_guid();

$access_label = elgg_echo('access');
$access_input = elgg_view('input/access', array('name' => 'access_id', 'value' => $e_portfolio->access_id));
$submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:import_pages')));

$options = array('type' => 'object', 'subtype' => 'e_portfolio', 'limit' => false, 'owner_guid' => $user_guid);
$e_portfolios = elgg_get_entities_from_metadata($options);
if ($container instanceof ElggGroup) {
   $members = $container->getMembers(array('limit'=>false));
   foreach ($members as $member) {
      $member_guid = $member->getGUID();
      $group_owner_guid = $container->owner_guid;
      if (($member_guid!=$user_guid)&&(($group_owner_guid==$member_guid)||(check_entity_relationship($member_guid,'group_admin',$container_guid)))){
         $other_e_portfolios = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'e_portfolio', 'limit' => false, 'owner_guid' => $member_guid, 'container_guid' => $container_guid));
	 if ($e_portfolios)
            $e_portfolios = array_merge($e_portfolios,$other_e_portfolios);
	 else
	    $e_portfolios = $other_e_portfolios;
      }
   }
}

$pages_label = elgg_echo('e_portfolio:pages_list');

?>

<form action="<?php echo elgg_get_site_url()."action/".$action?>" name="import_pages_e_portfolio" enctype="multipart/form-data" method="post">

<?php echo elgg_view('input/securitytoken'); ?>

<?php
$num_pages=0;
$checkbox_options = array();
foreach ($e_portfolios as $one_e_portfolio) {
   $one_e_portfolio_guid = $one_e_portfolio->getGUID();
   if ($e_portfoliopost != $one_e_portfolio_guid) {
      $owner = $one_e_portfolio->getOwnerEntity();
      $cont_guid = $one_e_portfolio->container_guid;
      $cont = get_entity($cont_guid);
      if ($cont instanceof ElggGroup) {
         $one_e_portfolio_title = elgg_echo(sprintf(elgg_echo('e_portfolio:title:user:group'),$owner->name,$cont->name));
      } else {
         $one_e_portfolio_title = elgg_echo(sprintf(elgg_echo('e_portfolio:title:user'),$owner->name));
      }
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $one_e_portfolio_guid, 'order_by_metadata' => array('name' => 'page_number', 'direction' => 'asc', 'as' => 'integer'));
      $pages = elgg_get_entities_from_metadata($options);     
      foreach ($pages as $page) {
         $page_url = e_portfolio_page_url($page);
	 $page_text = $page->title . " (" . $one_e_portfolio_title . ")";
	 $page_index = "<a href=\"{$page_url}\">{$page_text}</a>";
         $checkbox_options[$page_index] = $page->getGUID();
	 $num_pages = $num_pages+1;
      }
   }
}

if ($num_pages!=0){
   ?>
   <p>
   <b><?php echo elgg_echo("e_portfolio:pages_list"); ?></b><br>
   </p>
   <?php

   $checkbox_input = elgg_view('input/checkboxes', array ('name' => 'selected_pages', 'options' => $checkbox_options));
?>
   <?php 
   echo $checkbox_input;
   ?>
   <p>
   <b><?php echo $access_label; ?></b><br>
   <?php echo $access_input; ?>
   </p>
   <?php
   echo $submit_input;
   ?>
   <input type="hidden" name="e_portfoliopost" value="<?php echo $e_portfoliopost; ?>">

<?php
} else {
   echo elgg_echo("e_portfolio:not_pages_to_import");
}
?>
                    
</form>

<?php
}
?>

</div>
