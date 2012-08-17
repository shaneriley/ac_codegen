<?php

function acucDecode( $codeString )
{
	$returnArray = acucStringToValues( $codeString );
	$returnArray = acucAdjustLetter( $returnArray );
	$returnArray = acucPassToCode( $returnArray );
	$returnArray = acucTo8Bit( $returnArray );
	$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_BACKWARD, 1 );
	$returnArray = acucDecodeBitShuffle( $returnArray, 1);
	$returnArray = acucDecodeBitCode( $returnArray);
	$returnArray = acucDecodeRSACipher( $returnArray);
	$returnArray = acucDecodeBitShuffle( $returnArray, 0);
	$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_FORWARD, 0 );
	
	return acucDecodeSubstitutionCipher( $returnArray );
}

function acucEncode( $arrayCode )
{
	$returnArray = acucSubstitutionCipher( $arrayCode );
	$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_BACKWARD, 0 );
	$returnArray = acucBitShuffle( $returnArray, 0);
	$returnArray = acucChangeRSACipher( $returnArray);
	$returnArray = acucBitMixCode( $returnArray );
	$returnArray = acucBitShuffle( $returnArray, 1);
	$returnArray = acucTranspositionCipher( $returnArray, ACUC_DIRECTION_FORWARD, 1 );
	$returnArray = acucTo6Bit( $returnArray );
	$returnArray = acucCodeToPass( $returnArray );

	return acucValuesToString($returnArray);
}

function dechexpad( $decValue, $minLength = 0 )
{
	$hexValue = dechex( $decValue );

	return str_pad( $hexValue, max( $minLength, ceil( strlen( $hexValue ) / 2 ) * 2 ), "0", STR_PAD_LEFT );
}