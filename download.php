<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");

// Get the guid
$e_portfolio_file_guid = get_input("e_portfolio_file_guid");

// Get the file
$e_portfolio_file = new E_portfolioPluginFile($e_portfolio_file_guid);

if ($e_portfolio_file) {
   $mime = $e_portfolio_file->mimetype;
   if (!$mime) {
      $mime = "application/octet-stream";
   }
   $filename = $e_portfolio_file->originalfilename;
   header("Pragma: public");
   header("Content-type: $mime");
   header("Content-Disposition: attachment; filename=\"$filename\"");
   $contents = $e_portfolio_file->grabFile();
   $splitString = str_split($contents, 8192);
   foreach($splitString as $chunk)
      echo $chunk;
      exit;
   } else {
      register_error(elgg_echo("e_portfolio:file_downloadfailed"));
      forward($_SERVER['HTTP_REFERER']);
   }
?>