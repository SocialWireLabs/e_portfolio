<div class="contentWrapper">

<?php

$action = "e_portfolio/edit_artifact";
$e_portfolio_artifact = $vars['entity'];
$e_portfolio_artifact_guid = $e_portfolio_artifact->getGUID();
$e_portfolio_page_guid = $e_portfolio_artifact->container_guid;
$e_portfolio_page = get_entity($e_portfolio_page_guid);
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

$artifact_type=$e_portfolio_artifact->artifact_type;
	
if (!elgg_is_sticky_form('edit_artifact_e_portfolio')) {
   $title = $e_portfolio_artifact->title;
   $description = $e_portfolio_artifact->description;
   $embed = html_entity_decode($e_portfolio_artifact->embed);
   switch($artifact_type){
      case 'urls_files':
         $comp_urls = explode(Chr(26),$e_portfolio_artifact->urls);
	 $comp_urls = array_map('trim',$comp_urls);
         break;
      case 'video':   
         $video_url = $e_portfolio_artifact->video_url;
         break;
   }
} else {
   $title = elgg_get_sticky_value('edit_artifact_e_portfolio','title');
   $description = elgg_get_sticky_value('edit_artifact_e_portfolio','description');
   $embed = elgg_get_sticky_value('edit_artifact_e_portfolio','embed');
   switch($artifact_type){
      case 'urls_files':
         $urls_names = elgg_get_sticky_value('edit_artifact_e_portfolio','urls_names');
         $urls = elgg_get_sticky_value('edit_artifact_e_portfolio','urls');
         $i=0;
         $comp_urls = array();
         foreach ($urls as $url){
            $comp_urls[$i] = $urls_names[$i] . Chr(24) . $urls[$i]; 
            $i=$i+1;
         }  
	 break;
      case 'video':   
         $video_url =  elgg_get_sticky_value('edit_artifact_e_portfolio','video_url');
         break;
   }
} 

elgg_clear_sticky_form('edit_artifact_e_portfolio');    

$submit_input_save = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_artifact')));
$submit_input_finish = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_artifact_finish')));
                
?>

<form action="<?php echo elgg_get_site_url()."action/".$action?>" name="edit_artifact_e_portfolio" enctype="multipart/form-data" method="post">

<?php echo elgg_view('input/securitytoken'); ?>

<p>
<b><?php echo elgg_echo("e_portfolio:title"); ?></b><br>
<?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:form:description"); ?></b><br>
<?php echo elgg_view("input/longtext", array('name' => 'description', 'value' => $description)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:form:embed"); ?></b><br>
<?php echo elgg_view("input/text", array('name' => 'embed', 'value' => $embed));?>
</p>

<?php
switch ($artifact_type) {
   case 'urls_files':
      ?>
      <p>
      <b> <?php echo elgg_echo("e_portfolio:form:urls"); ?></b><br>
      <?php
      if ((count($comp_urls)>0)&&(strcmp($comp_urls[0],"")!=0)) {
	 $i=0;
         foreach ($comp_urls as $url) {
            ?>
            <p class="clone_urls">
            <?php
	    $comp_url = explode(Chr(24),$url);
            $comp_url = array_map('trim',$comp_url);
            $url_name = $comp_url[0];
            $url_value = $comp_url[1];
            echo ("<b>" . elgg_echo("e_portfolio:form:url_name") . "</b>");
            echo elgg_view("input/text", array("name" => "urls_names[]","value" => $url_name));
            echo ("<b>" . elgg_echo("e_portfolio:form:url") . "</b>");
            echo elgg_view("input/text", array("name" => "urls[]","value" => $url_value));
	    if ($i>0){	
	       ?>
               <!-- remove url -->
               <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a>          
               <?php
	    }
	    ?>
	    </p>
	    <?php 
	    $i=$i+1;
         }
      } else {
         ?>
         <p class="clone_urls">
         <?php
         $comp_url = explode(Chr(24),$comp_urls);
         $comp_url = array_map('trim',$comp_url);
         $url_name = $comp_url[0];
         $url_value = $comp_url[1];
         echo ("<b>" . elgg_echo("e_portfolio:form:url_name") . "</b>");
         echo elgg_view("input/text", array("name" => "urls_names[]","value" => $url_name));
         echo ("<b>" . elgg_echo("e_portfolio:form:url") . "</b>");
         echo elgg_view("input/text", array("name" => "urls[]","value" => $url_value));
         ?>
         </p>         
         <?php
      }
      ?>
      <!-- add link to add more urls which triggers a jquery clone function -->
      <a href="#" class="add" rel=".clone_urls"><?php echo elgg_echo("e_portfolio:add_url"); ?></a>
      <br><br>
      </p>                 
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:files"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]', 'class' => 'multi'));?>
      </p>
      <?php
      $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link', 'types' => 'object','relationship_guid' => $e_portfolio_artifact_guid, 'inverse_relationship' => FALSE, 'subtypes' => 'e_portfolio_file','limit'=>0));
      if ((count($files)>0)&&(strcmp($files[0]->title,"")!=0)){
         foreach($files as $file) {
	    ?>
	    <div class="file_wrapper">
	       <a class="bold" onclick="changeFormValue(<?php echo $file->getGUID(); ?>), changeImage(<?php echo $file->getGUID(); ?>)">
	       <img id ="image_<?php echo $file->getGUID(); ?>" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/graphics/tick.jpeg">
	       </a>
	       <span><?php echo $file->title ?></span>
	       <?php echo elgg_view("input/hidden",array('name' => $file->getGUID(), 'id'=> $file->getGUID(), 'value' => '0'));?>
	    </div>
	    <br>
            <?php
         }
      }
      break;   
   case 'image':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:image"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]'));?>
      </p>
      <?php
      break;
   case 'audio':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:audio"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]'));?>
      </p>
      <?php
      break;
   case 'video':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:video"); ?></b><br>
      <?php echo elgg_view("input/text", array('name' => 'video_url', 'value' => $video_url));?>
      </p>
      <?php
      break;    
}
?>

<!-- add the add/delete functionality  -->
<script type="text/javascript">
// remove function for the jquery clone plugin
$(function(){
   var removeLink = '<a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete");?></a>';
   $('a.add').relCopy({ append: removeLink});
});
</script>
 
<br>

<?php
echo "$submit_input_save $submit_input_finish";
?>

<input type="hidden" name="e_portfolio_page_guid" value="<?php echo $e_portfolio_page_guid; ?>">
<input type="hidden" name="e_portfolio_artifact_guid" value="<?php echo $e_portfolio_artifact_guid; ?>">
<input type="hidden" name="artifact_type" value="<?php echo $artifact_type; ?>">

</form>

<?php
}
?>

<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/jquery.MultiFile.js"></script><!-- multi file jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/reCopy.js"></script><!-- copy field jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/js_functions.js"></script>
<script type="text/javascript">
    function changeImage(num) {
        if (document.getElementById('image_'+num).src == "<?php echo elgg_get_site_url(); ?>mod/e_portfolio/graphics/tick.jpeg")
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/e_portfolio/graphics/delete.jpeg";
        else
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/e_portfolio/graphics/tick.jpeg";
    }
</script>

</div>
