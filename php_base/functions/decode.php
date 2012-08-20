<?php
//////////////////////////////////////////////////////
// decode.php                                       //
//////////////////////////////////////////////////////
// Functions used solely for decoding               //
//////////////////////////////////////////////////////

//////////////////////////////////////////////////////
// acucAdjustLetter( $arrayPasscode )
//
// Replaces each instance of 0 with O and each
// instance of 1 with l. Since Animal Crossing does
// not destinguish between 0 and O or 1 and l, it
// would not matter which character would be used,
// but in order to simplify it for the decoder, one
// has to be picked.
//
// Arguments:
//
//   $arrayPasscode
// An array with values where the letters need to
// be replaced.
//
// Returns:
//
// An array with the replaced characters.
//////////////////////////////////////////////////////
function acucAdjustLetter( $arrayPasscode )
{
	$arrayReturnPasscode = $arrayPasscode;
	
	$arrayReplacement = array("O", "l");
	for($rIdx = 0; $rIdx < 2; $rIdx++)
	{
		$arrayKeys = array_keys($arrayReturnPasscode, 0x30 + $rIdx);
		for($kIdx = 0; $kIdx < count($arrayKeys); $kIdx++)
		{
			$arrayReturnPasscode[ $arrayKeys[ $kIdx ] ] = ord( $arrayReplacement[ $rIdx ] );
		}
	}
	
	return $arrayReturnPasscode;
}

//////////////////////////////////////////////////////
// acucPassToCode( $arrayPasscode )
//
// Converts the passcode to usable code for the
// decoder.
//
// Arguments:
//
//   $arrayPasscode
// The passcode to decode.
//////////////////////////////////////////////////////
function acucPassToCode( $arrayPasscode )
{
	global $acucUsableChars;
	
	$arrayReturnPasscode = $arrayPasscode;
	
	if( count($arrayPasscode) < 28 )
		return false;
	
	for( $idx = 0; $idx < 28; $idx++ )
	{
		$arrayReturnPasscode[$idx] = array_search( $arrayPasscode[$idx], $acucUsableChars);
		if( $arrayReturnPasscode[$idx] === false )
		return false;
	}
	
	return $arrayReturnPasscode;
}

//////////////////////////////////////////////////////
// acucTo8Bit( $arrayPasscode )
//
// Converts the passcode to an 8-bit code. The
// passcode originally is stored as a 6-bit code,
// after which the code gets converted to a readable
// string. This function turns it back to 8-bit.
//
// Arguments:
//
//   $arrayPasscode
// The passcode to convert to an 8-bit code.
//////////////////////////////////////////////////////
function acucTo8Bit( $arrayPasscode )
{
	$returnArrayCode = array();
	
	$bit6Idx = 0;
	$bit8Idx = 0;
	$byte6Idx = 0;
	$byte8Idx = 0;
	
	$valueByte = 0;
	$currentBit = 0;
	
	while( true )
	{
		$currentBit = ( $arrayPasscode[ $byte6Idx ] >> $bit6Idx ) & 0x01;
		$currentBit <<= $bit8Idx;
		$bit6Idx++;
		$bit8Idx++;
		$valueByte |= $currentBit;
		
		if($bit8Idx == 8)
		{
			$returnArrayCode[ $byte8Idx ] = $valueByte;
			$byte8Idx++;
			if( $byte8Idx == 21 )
				return $returnArrayCode;
			$bit8Idx = 0;
			$valueByte = 0;
		}
		if($bit6Idx == 6)
		{
			$bit6Idx = 0;
			$byte6Idx++;
		}
	}
}

//////////////////////////////////////////////////////
// acucDecodeBitShuffle( $arrayCode , $usedKey)
//
//////////////////////////////////////////////////////
function acucDecodeBitShuffle( $arrayCode, $usedKey)
{
	global $acucSelectIndexTable;
	
	$outputBuffer = array();
	
	if( $usedKey == 0 )
	{
		$charOffset = 13;
		$charCount = 19;
	}
	else
	{
		$charOffset = 2;
		$charCount = 20;
	}
	
	$arrayBuffer = array_merge( array_slice( $arrayCode, 0, $charOffset), array_slice( $arrayCode, $charOffset + 1, 20 - $charOffset) );
	
	$tableNumber = ( $arrayCode[ $charOffset ] << 2 ) &0x0c;
	$indexTable = $acucSelectIndexTable[ $tableNumber >> 2 ];
	
	for( $idx = 0; $idx < $charCount; $idx++ )
	{
		for($tIdx = 0; $tIdx < 8; $tIdx++)
		{
			$outputOffset = $indexTable[ $tIdx ] + $idx;
			$outputOffset %= $charCount;
			$valueByte = $arrayBuffer[$outputOffset];
			$valueByte = ($valueByte >> $tIdx ) & 0x01;
			$valueByte <<= $tIdx;
			$arrayOutputBuffer[$idx] |= $valueByte;
		}
	}
	
	$arrayReturnCode = array_merge( array_slice( $arrayOutputBuffer, 0, $charOffset),
										array_slice( $arrayCode, $charOffset, 1),
										array_slice( $arrayOutputBuffer, $charOffset, 20-$charOffset));
	$arrayReturnCode = array_merge( $arrayReturnCode, array_slice($arrayCode, count($arrayReturnCode)));
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucDecodeBitCode( $arrayCode )
//
//////////////////////////////////////////////////////
function acucDecodeBitCode( $arrayCode )
{
	$arrayReturnCode = $arrayCode;
	$codeMethod = $arrayCode[1] & 0x0f;
	
	if( $codeMethod > 12 )
	{
		$arrayReturnCode = acucBitShift( $arrayReturnCode, ( 0 - $codeMethod ) * 3 );
		$arrayReturnCode = acucBitReverse( $arrayReturnCode );
		$arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
	}
	elseif( $codeMethod > 8 )
	{
		$arrayReturnCode = acucBitShift( $arrayReturnCode, $codeMethod * 5 );
		$arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
	}
	elseif( $codeMethod > 4 )
	{
		$arrayReturnCode = acucBitReverse( $arrayReturnCode );
		$arrayReturnCode = acucBitShift( $arrayReturnCode, $codeMethod * 5 );
	}
	else
	{
		$arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
		$arrayReturnCode = acucBitShift( $arrayReturnCode, ( 0 - $codeMethod ) * 3 );
	}
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucDecodeRSACipher( $arrayCode )
//
//////////////////////////////////////////////////////
function acucDecodeRSACipher( $arrayCode )
{
	$modCount = 0;
	$arrayReturnCode = $arrayCode;

	$primeParams = acucGetRSAKeyCode( $arrayCode );
	
	$prime1 = $primeParams[0];
	$prime2 = $primeParams[1];
	$prime3 = $primeParams[2];
	$indexTable = $primeParams[3];
	
	$primeProduct = $prime1 * $prime2;
	
	$lessProduct = ( $prime1 - 1 ) * ( $prime2 - 1 );
	
	do
	{
		$modCount++;
		$loopEndValue = ($modCount * $lessProduct + 1 ) % $prime3;
		$modValue = ($modCount * $lessProduct + 1 ) / $prime3;
	} while( $loopEndValue != 0 );
	
	for( $idx = 0; $idx < 8; $idx++ )
	{
		$valueByte = $arrayCode[ $indexTable[$idx] ];
		$valueByte |= ( ( $arrayCode[20] >> $idx ) << 8 ) & 0x0100;
		$currentValueByte = $valueByte;
		for( $mIdx = 0; $mIdx < $modValue - 1; $mIdx++ )
		{
			$valueByte = ( $valueByte * $currentValueByte ) % $primeProduct;
		}
		
		$arrayReturnCode[ $indexTable[$idx] ] = $valueByte & 0xff;
	}
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucDecodeSubstitutionCipher( $arrayCode )
//
//////////////////////////////////////////////////////
function acucDecodeSubstitutionCipher( $arrayCode )
{
	global $acucCodeChangeTable;
	
	$arrayReturnCode = $arrayCode;
	
	for( $idx = 0; $idx < 21; $idx++ )
	{
		$arrayReturnCode[$idx] = array_search($arrayCode[$idx], $acucCodeChangeTable);
	}
	
	return $arrayReturnCode;
}
?>
