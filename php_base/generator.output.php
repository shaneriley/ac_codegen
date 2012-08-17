<?php
include_once("./functions.php");
include_once("./itemlist.php");
$hexString = $_REQUEST["data"];

$stringText = array();

for($i = 0; $i < strlen( $hexString ); $i+= 2)
{
	array_push($stringText, hexdec(substr($hexString, $i, 2)));
}

array_push($stringText, 0);

$returnArray = acucSubstitutionCipher( $stringText );
$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_BACKWARD, 0 );
$returnArray = acucBitShuffle( $returnArray, 0);
$returnArray = acucChangeRSACipher( $returnArray);
$returnArray = acucBitMixCode( $returnArray );
$returnArray = acucBitShuffle( $returnArray, 1);
$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_FORWARD, 1 );
$returnArray = acucTo6Bit( $returnArray );
$finalCodeData = acucCodeToPass( $returnArray );
$finalCode = acucValuesToString($finalCodeData);

echo $finalCode . "<br />\n";

$finalCodePrint = $finalCodeData;
for($i = 0; $i < count($finalCodePrint); $i++)
{
	if($finalCodePrint[$i] == 35 )
	$finalCodePrint[$i] = 209;
}

echo "<img src=\"./imagestring.php?value=" . str_replace(" ", "", acucDataToHex( $finalCodePrint, 30) ) . "&row=14\" alt=\"Password\" />";
?>