<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
  <title>RetroCheater - Animal Crossing Universal Codes</title>
  <link rel="stylesheet" type="text/css" href="./acuc.css" />
 </head>
 <body>
  <p class="titleHeader">Animal Crossing Universal Codes</p>
  <p class="subtitleHeader">Based on code by MooglyGuy / UltraMoogleMan</p>
  <div class="secondaryHeader" width="100%" style="text-align: center"><a href="./index.php?mod=decoder">Decoder</a> - <a href="./index.php?mod=generator">Generator</a></div>
<?php

include_once("./functions.php");
include_once("./modules.php");

$action = $_REQUEST["mod"];

if( !array_key_exists( $action, $module ) )
{
	$action = "main";
}

include_once('./' . $action . '.php');

?>
  <hr />
  <p class="permaLinkBlock">This generator was originally coded by Ryan Holtz, and ported to PHP by Gary Kertopermono. When using this generator on your own site, please leave at least the credits to Ryan Holtz intact, since he did most of the original resource.</p>
  <p class="permaLinkBlock">You can download the generator <a href="./acuc2.zip">here</a>.</p>
 </body>
</html>