<?php

gatekeeper();

$e_portfolio_group_setup_guid = get_input('e_portfolio_group_setup_guid');
$e_portfolio_group_setup = get_entity($e_portfolio_group_setup_guid);

if ($e_portfolio_group_setup->getSubtype() == "e_portfolio_group_setup" && $e_portfolio_group_setup->canEdit()) {
   
   if ($e_portfolio_group_setup->qualify_opened)
      $e_portfolio_group_setup->qualify_opened = false;
   else
      $e_portfolio_group_setup->qualify_opened =true;
}
forward($_SERVER['HTTP_REFERER']);
?>