<?php

/**
* Override the ElggFile so that
*/
	
class E_portfolioPluginFile extends ElggFile
{
	protected function initialiseAttributes()
	{
		parent::initialise_attributes();
		$this->attributes['subtype'] = "e_portfolio_file";
                $this->attributes['class'] = "ElggFile";
	}

	public function __construct($guid = null)
	{
      if ($guid && !is_object($guid)) {
         // Loading entities via __construct(GUID) is deprecated, so we give it the entity row and the
         // attribute loader will finish the job. This is necessary due to not using a custom
         // subtype (see above).
         $guid = get_entity_as_row($guid);
      }
		parent::__construct($guid);
	}
}

function e_portfolio_init() {

// Set up menu for logged in users
   $item = new ElggMenuItem('e_portfolio', elgg_echo('e_portfolios'), 'e_portfolio/all');
   elgg_register_menu_item('site', $item);
				
// Extend system CSS with our own styles, which are defined in the e_portfolio/css view
   elgg_extend_view('css/elgg','e_portfolio/css');

// Register a page handler, so we can have nice URLs
   elgg_register_page_handler('e_portfolio','e_portfolio_page_handler');
		
// Register entity type
   elgg_register_entity_type('object','e_portfolio');

// Register a URL handler for e_portfolio posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'e_portfolio_url');


// Register a URL handler for e_portfolio_page posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'e_portfolio_page_url');
										
// Show e_portfolios in groups
   add_group_tool_option('e_portfolio', elgg_echo('e_portfolio:enable_group_e_portfolios'));
   elgg_extend_view('groups/tool_latest', 'e_portfolio/group_module');

// Add a menu item to the user ownerblock
   elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'e_portfolio_owner_block_menu');
  
// Advanced permissions
   elgg_register_plugin_hook_handler('permissions_check', 'object', 'e_portfolio_permissions_check');

// Register library
   elgg_register_library('e_portfolio', elgg_get_plugins_path() . 'e_portfolio/lib/e_portfolio_lib.php');

// Register E_portfolioPluginFile subtype
   run_function_once("e_portfolio_file_add_subtype_run_once");  

}

function e_portfolio_file_add_subtype_run_once(){
   add_subtype("object","e_portfolio_file","E_portfolioPluginFile");
}

function e_portfolio_permissions_check($hook, $type, $return, $params) {
   if (($params['entity']->getSubtype() == 'e_portfolio_group_setup')||($params['entity']->getSubtype() == 'e_portfolio')||($params['entity']->getSubtype() == 'e_portfolio_page')||($params['entity']->getSubtype() == 'e_portfolio_artifact')||($params['entity']->getSubtype() == 'e_portfolio_file')||($params['entity']->getSubtype() == 'izap_videos')||($params['entity']->getSubtype() == 'rubric_rating')) {
      $user_guid = elgg_get_logged_in_user_guid();
      if (($params['entity']->getSubtype() == 'e_portfolio_group_setup')||($params['entity']->getSubtype() == 'e_portfolio')||($params['entity']->getSubtype() == 'rubric_rating')){
         $group_guid = $params['entity']->container_guid;
      } elseif ($params['entity']->getSubtype() == 'e_portfolio_page') {
         $cont_guid = $params['entity']->container_guid;
         $group_guid = get_entity($cont_guid)->container_guid;
      } else {
         $cont1_guid = $params['entity']->container_guid;
	 $cont2_guid = get_entity($cont1_guid)->container_guid;
         $group_guid = get_entity($cont2_guid)->container_guid;
      }
      $group = get_entity($group_guid);
      if ($group instanceof ElggGroup) {
         $group_owner_guid = $group->owner_guid;
         $operator=false;
         if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
            $operator=true;
         }
         if ($operator)
            return true;
      }
   }	
}

/**
 * Add a menu item to the user ownerblock
*/
function e_portfolio_owner_block_menu($hook, $type, $return, $params) {
   if (elgg_instanceof($params['entity'], 'user')) {
      $url = "e_portfolio/owner/{$params['entity']->username}";
      $item = new ElggMenuItem('e_portfolio', elgg_echo('e_portfolios'), $url);
      $return[] = $item;
   } else {
      if ($params['entity']->e_portfolio_enable != "no") {
         $url = "e_portfolio/group/{$params['entity']->guid}/all";
         $item = new ElggMenuItem('e_portfolio', elgg_echo('e_portfolio:group'), $url);
         $return[] = $item;
      }
   }
   return $return;
}

		
/**
* E_portfolio page handler; allows the use of fancy URLs
*
* @param array $page from the page_handler function
* @return true|false depending on success
*/
function e_portfolio_page_handler($page) {
   if (isset($page[0])) {
         elgg_push_breadcrumb(elgg_echo('e_portfolios'));
	 $base_dir = elgg_get_plugins_path() . 'e_portfolio/pages/e_portfolio';
         switch($page[0]) {
            case "add":   
	       set_input('container_guid',$page[1]);
	       include "$base_dir/add.php"; 
               break;
	    case "add_page":  
	       set_input('e_portfoliopost',$page[1]);
	       include "$base_dir/add_page.php"; 
               break;
	    case "import_pages":
	       set_input('e_portfoliopost', $page[1]);
	       include "$base_dir/import_pages.php"; 
	       break;
	    case "add_artifact":
	       set_input('e_portfolio_page_guid', $page[1]);
	       include "$base_dir/add_artifact.php"; 			
               break;
	    case "edit":  
	       $entity = get_entity($page[1]);
	       $subtype = $entity->getSubtype();
	       switch ($subtype) {
	          case "e_portfolio":
		     set_input('e_portfoliopost',$page[1]);
		     include "$base_dir/edit.php"; 
		     break;
		  case "e_portfolio_page";
		     set_input('e_portfolio_page_guid',$page[1]);
	             include "$base_dir/edit_page.php"; 
                     break;
		  case "e_portfolio_artifact";
		     set_input('e_portfolio_artifact_guid',$page[1]);
	             include "$base_dir/edit_artifact.php"; 
                     break;
	       }
               break;
            case "view":  
	       set_input('entity_guid', $page[1]);
               include "$base_dir/read.php"; 
               break;
	    case "owner":
               set_input('username', $page[1]);
               include "$base_dir/index.php";
               break;
	    case "group":
	       set_input('container_guid',$page[1]);
	       include "$base_dir/index.php";
	       break;
	    case "friends":
               include "$base_dir/friends.php";
               break;
            case "all":
               include "$base_dir/everyone.php";
               break;
	    case 'setup_group':
	       set_input('container_guid',$page[1]);
	       include "$base_dir/setup_group.php";
	       break;
	    case 'marks':
	       set_input('e_portfolio_group_setup_guid',$page[1]);
	       set_input('page_number',$page[2]);
	       include "$base_dir/marks.php";
	       break;
	    default:
	       return false;
         }
   } else {
      forward();
   }
   return true;
}

/**
 * Returns the URL from a e_portfolio entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function e_portfolio_url($hook, $type, $url, $params) {
   $e_portfolio = $params['entity'];
   // Check that the entity is a e_portfolio object
   if ($e_portfolio->getSubtype() !== 'e_portfolio') {
        // This is not a e_portfolio object, so there's no need to go further
        return;
   }
   $title = elgg_get_friendly_title($e_portfolio->title);
   return $url . "e_portfolio/view/" . $e_portfolio->getGUID() . "/" . $title;
}

/**
 * Returns the URL from a e_portfolio_page entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function e_portfolio_page_url($hook, $type, $url, $params) {
   $e_portfolio_page = $params['entity'];
   // Check that the entity is a e_portfolio_page object
   if ($e_portfolio_page->getSubtype() !== 'e_portfolio_page') {
        // This is not a e_portfolio_page object, so there's no need to go further
        return;
   }
   $title = elgg_get_friendly_title($e_portfolio_page->title);
   $e_portfolio = get_entity($e_portfolio_page->container_guid);
   return $url . "e_portfolio/view/" . $e_portfolio_page->getGUID() . "/" . $title;
}

// Make sure the e_portfolio initialisation function is called on initialisation
elgg_register_event_handler('init','system','e_portfolio_init');
		
// Register actions
$action_base = elgg_get_plugins_path() . 'e_portfolio/actions/e_portfolio';
elgg_register_action("e_portfolio/add","$action_base/add.php");
elgg_register_action("e_portfolio/edit","$action_base/edit.php");
elgg_register_action("e_portfolio/delete","$action_base/delete.php");
elgg_register_action("e_portfolio/add_page","$action_base/add_page.php");
elgg_register_action("e_portfolio/edit_page","$action_base/edit_page.php");
elgg_register_action("e_portfolio/move_page","$action_base/move_page.php");
elgg_register_action("e_portfolio/import_pages","$action_base/import_pages.php");
elgg_register_action("e_portfolio/add_artifact","$action_base/add_artifact.php");
elgg_register_action("e_portfolio/edit_artifact","$action_base/edit_artifact.php");
elgg_register_action("e_portfolio/move_artifact","$action_base/move_artifact.php");
elgg_register_action("e_portfolio/open","$action_base/open.php");
elgg_register_action("e_portfolio/close","$action_base/close.php");
elgg_register_action("e_portfolio/open_page","$action_base/open_page.php");
elgg_register_action("e_portfolio/close_page","$action_base/close_page.php");
elgg_register_action("e_portfolio/open_close_qualify","$action_base/open_close_qualify.php");
elgg_register_action("e_portfolio/rate_page","$action_base/rate_page.php");
elgg_register_action("e_portfolio/assign_marks","$action_base/assign_marks.php");
elgg_register_action("e_portfolio/assign_game_points","$action_base/assign_game_points.php");
elgg_register_action("e_portfolio/setup_group","$action_base/setup_group.php");
				
?>