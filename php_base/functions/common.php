<?php
//////////////////////////////////////////////////////
// common.php                                       //
//////////////////////////////////////////////////////
// Functions used for decoding and encoding         //
//////////////////////////////////////////////////////

//////////////////////////////////////////////////////
// acucStringToValues( $string )
//
// Converts a string to a list of values in an
// array.
//
// Arguments:
//
//   $string
// The string to convert into an array.
//
// Returns:
//
// An array with the decimal values.
//////////////////////////////////////////////////////
function acucStringToValues( $string )
{
	$returnArray = array();
	
	for($idx = 0; $idx < strlen($string); $idx++)
	{
		$returnArray[$idx] = ord(substr($string, $idx, 1));
	}
	return $returnArray;
}

//////////////////////////////////////////////////////
// acucValuesToString( $arrayValues, [ $start = 0,
//                     [ $end = -1 ] ] )
//
// Converts an array of values to a string. You can
// set the start and end for it.
//
// Arguments:
//
//   $arrayValues
// An array containing the values you want to
// convert to a string.
//   $start
// Optional: The starting position of the values
// you want to convert.
//   $end
// Optional: The ending position of the values
// you want to convert.
//
// Returns:
//
// A string.
//////////////////////////////////////////////////////
function acucValuesToString( $arrayValues, $start = 0, $end = -1 )
{
	$returnString = "";
	
	if($end == -1)
		$end = count($arrayValues);
	
	for($idx = $start; $idx < $end; $idx++ )
	{
		$returnString.= chr( $arrayValues[$idx] );
	}
	return $returnString;
}

//////////////////////////////////////////////////////
// acucTranspositionCipher( $arrayCode, $direction,
//                          $usedKey )
//
//////////////////////////////////////////////////////
function acucTranspositionCipher( $arrayCode, $direction, $usedKey )
{
	global $acucKeyIndex, $acucStringModifier;
	
	$stringOffset = $arrayCode[ $acucKeyIndex[ $usedKey] ] & 0x0f;
	$stringTable = $stringOffset + ( $usedKey * 16 );
	
	$translationOffset = 0;
	
	$arrayString = acucStringToValues( $acucStringModifier[ $stringTable ] );
	
	$stringLength = count($arrayString);
	$sIdx = 0;
	
	$arrayReturnCode = array();
	
	for( $idx = 0; $idx < 21; $idx++ )
	{
		$arrayReturnCode[$idx] = $arrayCode[$idx];
		if( $acucKeyIndex[$usedKey] != $idx )
		{
			$arrayReturnCode[$idx] += $arrayString[ $sIdx ] * $direction;
			if( $arrayReturnCode[$idx] < 0 )
			$arrayReturnCode[$idx] += 256;
			$arrayReturnCode[$idx] %= 256;
			$sIdx++;
			$sIdx %= $stringLength;
		}
	}
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitShift( $arrayCode, $shift )
//
//////////////////////////////////////////////////////
function acucBitShift( $arrayCode, $shift )
{
	$arrayBuffer = array_merge( array_slice( $arrayCode, 0, 1 ), array_slice( $arrayCode, 2, 19) );
	$arrayOutputBuffer = array();
		
	if( $shift > 0 )
	{
		$destPosition = floor($shift / 8);
		$destOffset = $shift % 8;
		
		for( $idx = 0; $idx < 20; $idx++ )
		{
			$arrayOutputBuffer[($idx + $destPosition) % 20] = ( ( $arrayBuffer[$idx] << $destOffset ) | ( ($arrayBuffer[($idx + 19) % 20] ) >> (8 - $destOffset) ) ) & 0xff;
		}
		
		// This short hack was required due to the fact that
		// array_slice would otherwise crap out.
		$arrayBuffer = acucStringToValues(acucValuesToString($arrayOutputBuffer));
	}
	elseif( $shift < 0 )
	{
		for( $idx = 0; $idx < 20; $idx++ )
		{
			$arrayOutputBuffer[$idx] = $arrayBuffer[19 - $idx];
		}
		$shift = 0 - $shift;
		
		$destPosition = floor($shift / 8);
		$destOffset = $shift % 8;
		
		for( $idx = 0; $idx < 20; $idx++ )
		{
			$arrayBuffer[ ( $idx + $destPosition ) % 20 ] = $arrayOutputBuffer[$idx];
		}
		
		for( $idx = 0; $idx < 20; $idx++ )
		{
			$arrayOutputBuffer[$idx] = (( $arrayBuffer[$idx] >> $destOffset ) | ( ($arrayBuffer[($idx + 19) % 20] ) << (8 - $destOffset) )) & 0xff;
		}
		for( $idx = 0; $idx < 20; $idx++ )
		{
			$arrayBuffer[$idx] = $arrayOutputBuffer[19 - $idx];
		}
	}
	$arrayReturnCode = array_merge(
								array_slice( $arrayBuffer, 0, 1),
								array_slice( $arrayCode, 1, 1),
								array_slice( $arrayBuffer, 1, 19));

	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitReverse( $arrayCode )
//
//////////////////////////////////////////////////////
function acucBitReverse( $arrayCode )
{
	$arrayReturnCode = $arrayCode;
	for( $idx = 0; $idx < 21; $idx++)
	{
		if($idx != 1)
			$arrayReturnCode[$idx] ^= 0xff;
	}
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitArrangeReverse( $arrayCode )
//
//////////////////////////////////////////////////////
function acucBitArrangeReverse( $arrayCode )
{
	$arrayBuffer = array_merge(
							array_slice( $arrayCode, 0, 1),
							array_slice( $arrayCode, 2, 19));

	$arrayOutputBuffer = array();
	
	for( $idx = 0; $idx < 20; $idx++)
	{
		$sourceValue = $arrayBuffer[19 - $idx];
		$destValue =
				( ( $sourceValue & 0x80 ) >> 7) |
				( ( $sourceValue & 0x40 ) >> 5) |
				( ( $sourceValue & 0x20 ) >> 3) |
				( ( $sourceValue & 0x10 ) >> 1) |
				( ( $sourceValue & 0x08 ) << 1) |
				( ( $sourceValue & 0x04 ) << 3) |
				( ( $sourceValue & 0x02 ) << 5) |
				( ( $sourceValue & 0x01 ) << 7);
		$arrayOutputBuffer[$idx] = $destValue;
	}
	
	$arrayReturnCode = array_merge(
								array_slice($arrayOutputBuffer,0, 1),
								array_slice($arrayCode, 1, 1),
								array_slice($arrayOutputBuffer, 1, 19));

	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucGetRSAKeyCode( $arrayCode )
//
//////////////////////////////////////////////////////
function acucGetRSAKeyCode( $arrayCode )
{
	global $acucPrimes, $acucSelectIndexTable;
	
	$bit10 = 0;
	$bit32 = 0;
	$byteTable = 0;
	
	$bit10 = $arrayCode[15] % 4;
	$bit32 = floor( ( $arrayCode[15] & 0x0f ) / 4 );
	
    if( $bit10 == 3 )
    {
        $bit10 = ( $bit10 ^ $bit32 ) & 0x03;
        if( $bit10 == 3 )
			$bit10 = 0;
    }

    if( $bit32 == 3 )
    {
        $bit32 = ($bit10 + 1) & 0x03;
        if( $bit32 == 3 )
			$bit32 = 1;
    }

    if( $bit10 == $bit32 )
    {
        $bit32 = ($bit10 + 1) & 0x03;
        if( $bit32 == 3 )
			$bit32 = 1;
    }

    $byteTable = ( ( $arrayCode[15] >> 2 ) & 0x3c ) >> 2;
	
	$returnParams = array(
		$acucPrimes[$bit10],
		$acucPrimes[$bit32],
		$acucPrimes[$arrayCode[5]],
		$acucSelectIndexTable[$byteTable]);
		
	return $returnParams;
}
?>