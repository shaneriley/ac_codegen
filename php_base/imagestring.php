<?php

$hexString = $_REQUEST["value"];
$rowLength = $_REQUEST["row"];
$highlight = -1;
if(array_key_exists("highlight", $_REQUEST))
	$highlight = $_REQUEST["highlight"];

$stringText = array();

for($i = 0; $i < strlen( $hexString ); $i+= 2)
{
	array_push($stringText, hexdec(substr($hexString, $i, 2)));
}

header("Content-type: image/png");

header("Content-type: image/png");

$im = imagecreate(
				(192 / 16) * min( count( $stringText ), $rowLength ),
				(256 / 16) * ceil( count( $stringText ) / $rowLength )
				);

$font = imagecreatefromPNG("images/acucfont.png");

for( $idx = 0; $idx < count( $stringText ); $idx++ )
{
	imagecopy($im, $font,
			(192 / 16) * ($idx % $rowLength ),
			(256 / 16) * floor( $idx / $rowLength ),
			(192 / 16) * ( $stringText[$idx] & 0x0f ),
			(256 / 16) * ( $stringText[$idx] >> 4 ),
			192 / 16, 256 / 16 );
}

$w=ImageColorAllocate($im, 255, 255, 255);
if( $highlight > -1 && $highlight < count( $stringText ) )
{
	$border = ImageColorAllocate($im, 255, 0, 0);
	imagerectangle ( $im,
					(192 / 16) * ($highlight % $rowLength ),
					(256 / 16) * floor( $highlight / $rowLength ),
					((192 / 16) * (1 + ($highlight % $rowLength ))) - 1,
					((256 / 16) * (1 + floor( $highlight / $rowLength ))) - 1,
					$border );
}
ImagePng($im);
ImageDestroy($im);
ImageDestroy($font);

?>