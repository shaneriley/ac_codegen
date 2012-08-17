<p><?php
include_once("./functions.php");
include_once("./itemlist.php");
$code = $_REQUEST["code"];

$code = str_replace("_", "%", $code);
$code = str_replace("-", "&", $code);
$code = str_replace("|", "#", $code);

$stepToValues = acucStringToValues( $code );
$stepAdjustLetter = acucAdjustLetter( $stepToValues );
$stepToCode = acucPassToCode( $stepAdjustLetter );
if(!$stepToCode)
{
	echo "<b>Not a valid code!</b><br />\n";
	echo "<br />\n";
	echo "<b>Original code:</b><br />\n";
	echo str_replace("&", "&amp;", acucValuesToString($stepAdjustLetter, 0, 14)) . "<br />\n";
	echo str_replace("&", "&amp;", acucValuesToString($stepAdjustLetter, 14, 28)) . "<br />\n";
	echo "<br />\n";
	echo acucDataToHex($stepToAdjustLetter, 14) . "<br />\n";
}
else
{
	$stepTo8Bit = acucTo8Bit( $stepToCode );
	$stepTranCiph1 = acucTranspositionCipher( $stepTo8Bit, ACUC_DIRECTION_BACKWARD, 1 );
	$stepDecBitShuf1 = acucDecodeBitShuffle( $stepTranCiph1, 1);
	$stepDecBitCode = acucDecodeBitCode( $stepDecBitShuf1);
	$stepDecRSACiph = acucDecodeRSACipher( $stepDecBitCode);
	$stepDecBitShuf2 = acucDecodeBitShuffle( $stepDecRSACiph, 0);
	$stepTranCiph2 = acucTranspositionCipher( $stepDecBitShuf2, ACUC_DIRECTION_FORWARD, 0 );
	$stepFinal = acucDecodeSubstitutionCipher( $stepTranCiph2 );
	
	$playerName = array_slice( $stepFinal, 2, 8 );
	$townName = array_slice( $stepFinal, 10, 8 );
	$itemNumber = ($stepFinal[18] << 8) + $stepFinal[19];
	
	$codeChecksum = array_sum( array_slice( $stepFinal, 2, 16 ) ) + $itemNumber;
	$codeCheck = $stepFinal[0] & 0x18;
	$codeType = $stepFinal[0] - $codeCheck;
	$codeCheck >>= 3;
	$codeRealCheck = ($codeChecksum + $stepFinal[1]) & 0x03;
	
	echo "<b>Original code:</b><br />\n";
	echo acucValuesToString($stepToValues, 0, 14) . "<br />\n";
	echo acucValuesToString($stepToValues, 14, 28) . "<br />\n";
	echo "<br />\n";
	
	echo "<b>Player name:</b><br />\n";
	echo acucValuesToString($playerName) . "<br />\n";
	echo "<img src=\"./imagestring.php?value=";
	echo str_replace( " ", "", acucDataToHex( $playerName, 10 ) );
	echo "&amp;row=8\" alt=\"" . acucValuesToString($playerName) . "\" /><br />\n";
	echo acucDataToHex( $playerName, 8 );
	echo "<br />\n";
	
	echo "<b>Town name:</b><br />\n";
	echo acucValuesToString($townName) . "<br />\n";
	echo "<img src=\"./imagestring.php?value=";
	echo str_replace( " ", "", acucDataToHex( $townName, 10 ) );
	echo "&amp;row=8\" alt=\"" . acucValuesToString($townName) . "\" /><br />\n";
	echo acucDataToHex( $townName, 8 );
	echo "<br />\n";
	
	echo "<b>Item:</b> 0x". dechexpad($itemNumber, 4) . " - ";
	if( array_key_exists( $itemNumber, $itemlist ) )
		echo $itemlist[$itemNumber];
	else
		echo "Not defined";
	echo " (" . $itemNumber . ")";
	echo "<br />\n";
	echo "<br />\n";
	
	$isWorking = true;
	
	echo "<b>Code type: </b> 0x" . dechexpad($codeType) . " - ";
	if( array_key_exists( $codeType, $acucCodeTypes ) )
	{
		if( $acucCodeTypes[$codeType] > -1 )
			echo $acucTypes[$acucCodeTypes[$codeType]];
		else
		{
			echo "No code";
			$isWorking = false;
		}
	}
	else
		echo "Undefined";
	echo "<br />\n";
	echo "<br />\n";
	
	echo "<b>Code checksum:</b> 0x" . dechexpad($codeChecksum, 4) . " (" . $codeChecksum. ")<br />\n";
	echo "<b>Decoded code check:</b> 0x" . dechexpad($codeCheck, 2) . "<br />\n";
	echo "<b>Real code check:</b> 0x" . dechexpad($codeRealCheck, 2) . "<br />\n";
	echo "<br />\n";
	echo "<b>Working code: </b> ";
	if( !$isWorking )
		echo "No";
	elseif( $codeCheck != $codeRealCheck )
		echo "Unlikely";
	else
		echo "Likely";
	echo "<br />\n";
	echo "<br />\n";
	
	echo "<b>Decoded data:</b><br />\n";
	echo acucDataToHex( $stepFinal );
}
?></p>