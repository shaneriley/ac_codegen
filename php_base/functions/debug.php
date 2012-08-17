<?php
//////////////////////////////////////////////////////
// debug.php                                        //
//////////////////////////////////////////////////////
// Debug functions, can also be used for in- and    //
// output                                           //
//////////////////////////////////////////////////////

//////////////////////////////////////////////////////
// acucDataToHex( $arrayCode )
//
// A function to convert the decimal values of the
// code to hexadecimal.
//////////////////////////////////////////////////////
function acucDataToHex( $arrayCode, $perLine = -1 )
{
	$returnString = "";
	
	if( $perLine == -1 )
		$perLine = count( $arrayCode );
	
	for( $idx = 0; $idx < count($arrayCode); $idx++ )
	{
		$returnString.= str_pad( dechex($arrayCode[$idx]), 2, "0", STR_PAD_LEFT);
		if(($idx + 1) % $perLine == 0)
			$returnString.=  "<br />\n";
		else
			$returnString.=  " ";
	}
	
	return $returnString;
}
?>