<code>
<?php
//KtsuKuKeGiKunY ItsuReSeZeNiyG
include_once("./functions.php");

//$testString = "R%aulwEj9CNTtAxdB3qJG4btPX6L";
$testString = "KtsuKuKeGiKunYItsuReSeZeNiyG";

echo "<b>String: </b>" . $testString . "<br />\n";

$toValues = acucStringToValues( $testString );

echo acucDataToHex( $toValues, 14);

echo "<br />\n";

$toFix = acucAdjustLetter( $toValues );

echo acucDataToHex( $toFix, 14);

echo "<br />\n";

$toCode = acucPassToCode( $toFix );

echo acucDataToHex( $toCode, 14);

echo "<br />\n";

$to8Bit = acucTo8Bit( $toCode );

echo acucDataToHex( $to8Bit, 21 );

echo "<br />\n";

$toTransCiph = acucTranspositionCipher( $to8Bit, ACUC_DIRECTION_BACKWARD, 1 );

echo acucDataToHex( $toTransCiph, 21 );

echo "<br />\n";

$toDecBitShuf = acucDecodeBitShuffle( $toTransCiph, 1);

echo acucDataToHex( $toDecBitShuf, 21 );

echo "<br />\n";

$toDecBitCode = acucDecodeBitCode( $toDecBitShuf);

echo acucDataToHex( $toDecBitCode, 21 );

echo "<br />\n";

$toDecRSACipher = acucDecodeRSACipher( $toDecBitCode);

echo acucDataToHex( $toDecRSACipher, 21 );

echo "<br />\n";

$toDecBitShuf = acucDecodeBitShuffle( $toDecRSACipher, 0);

echo acucDataToHex( $toDecBitShuf, 21 );

echo "<br />\n";

$toTransCiph = acucTranspositionCipher( $toDecBitShuf, ACUC_DIRECTION_FORWARD, 0 );

echo acucDataToHex( $toTransCiph, 21 );

echo "<br />\n";

$toDecSubCiph = acucDecodeSubstitutionCipher( $toTransCiph );

echo acucDataToHex( $toDecSubCiph, 21 );

echo "<br />\n";

$toSubCiph = acucSubstitutionCipher( $toDecSubCiph );

echo acucDataToHex( $toSubCiph, 21 );

echo "<br />\n";

$toTransCiph = acucTranspositionCipher( $toSubCiph, ACUC_DIRECTION_BACKWARD, 0 );

echo acucDataToHex( $toTransCiph, 21 );

echo "<br />\n";

$toBitShuf = acucBitShuffle( $toTransCiph, 0);

echo acucDataToHex( $toBitShuf, 21 );

echo "<br />\n";

$toRSACipher = acucChangeRSACipher( $toBitShuf);

echo acucDataToHex( $toRSACipher, 21 );

echo "<br />\n";

$toBitCode = acucBitMixCode( $toRSACipher );

echo acucDataToHex( $toBitCode, 21 );

echo "<br />\n";

$toBitShuf = acucBitShuffle( $toBitCode, 1);

echo acucDataToHex( $toBitShuf, 21 );

echo "<br />\n";

$toTransCiph = acucTranspositionCipher( $toBitShuf, ACUC_DIRECTION_FORWARD, 1 );

echo acucDataToHex( $toTransCiph, 21 );

echo "<br />\n";

$to6Bit = acucTo6Bit( $toTransCiph );

echo acucDataToHex( $to6Bit, 14 );

echo "<br />\n";

$toPass = acucCodeToPass( $to6Bit );

echo acucDataToHex( $toPass, 14);

echo "<br />\n";

echo acucValuesToString($toPass);
?>
</code>