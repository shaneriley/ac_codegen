<?php
//////////////////////////////////////////////////////
// encode.php                                       //
//////////////////////////////////////////////////////
// Functions used solely for encoding               //
//////////////////////////////////////////////////////

//////////////////////////////////////////////////////
// acucSubstitutionCipher( $arrayCode )
//
//////////////////////////////////////////////////////
function acucSubstitutionCipher( $arrayCode )
{
	global $acucCodeChangeTable;
	
	$arrayReturnCode = $arrayCode;
	
	for($idx = 0; $idx < 21; $idx++)
	{
		$arrayReturnCode[$idx] = $acucCodeChangeTable[ $arrayCode[$idx] ];
	}
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitShuffle( $arrayCode, $usedKey )
//
//////////////////////////////////////////////////////
function acucBitShuffle( $arrayCode, $usedKey )
{
	global $acucSelectIndexTable;
	
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
	
	$arrayBuffer = array_merge(
						array_slice( $arrayCode, 0, $charOffset),
						array_slice( $arrayCode, $charOffset + 1, 20 - $charOffset) );
						
	$arrayOutputBuffer = array_fill(0, $charCount, 0);
	
	$tableNumber = ( $arrayCode[ $charOffset ] << 2 ) &0x0c;
	$indexTable = $acucSelectIndexTable[ $tableNumber >> 2 ];
		
	for($idx = 0; $idx < $charCount; $idx++)
	{
		$temporaryByte = $arrayBuffer[$idx];
		for($tIdx = 0; $tIdx < 8; $tIdx++)
		{
			$outputOffset = $indexTable[ $tIdx ] + $idx;
			$outputOffset %= $charCount;
			$valueByte = $temporaryByte >> $tIdx;
			$outputByte = $arrayOutputBuffer[$outputOffset];
			$valueByte &= 0x01;
			$valueByte <<= $tIdx;
			$valueByte = $valueByte | $outputByte;
			$arrayOutputBuffer[$outputOffset] = $valueByte;
		}
	}
	
	$arrayReturnCode = array_merge( array_slice( $arrayOutputBuffer, 0, $charOffset),
										array_slice( $arrayCode, $charOffset, 1),
										array_slice( $arrayOutputBuffer, $charOffset, 20-$charOffset));
	$arrayReturnCode = array_merge( $arrayReturnCode, array_slice($arrayCode, count($arrayReturnCode)));
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitShuffle( $arrayCode )
//
//////////////////////////////////////////////////////
function acucChangeRSACipher( $arrayCode )
{
	$arrayReturnCode = $arrayCode;
	
	$primeParams = acucGetRSAKeyCode( $arrayCode );
	
	$prime1 = $primeParams[0];
	$prime2 = $primeParams[1];
	$prime3 = $primeParams[2];
	$indexTable = $primeParams[3];
	
	$checkByte = 0;
	$primeProduct = $prime1 * $prime2;
	
	for($idx = 0; $idx < 8; $idx++)
	{
		$valueByte = $arrayCode[ $indexTable[ $idx ] ];
		$currentValueByte = $valueByte;
		for( $mIdx = 0; $mIdx < $prime3 - 1; $mIdx++ )
		{
			$valueByte = ( $valueByte * $currentValueByte ) % $primeProduct;
		}
		
		$arrayReturnCode[ $indexTable[ $idx ] ] = $valueByte & 0xff;
		$valueByte = ($valueByte >> 8 ) & 0x01;
		$checkByte |= $valueByte << $idx;
	}
	
	$arrayReturnCode[ 20 ] = $checkByte;
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucBitMixCode( $arrayCode )
//
//////////////////////////////////////////////////////
function acucBitMixCode( $arrayCode )
{
	$arrayReturnCode = $arrayCode;
	
	$switchByte = $arrayCode[1] & 0x0f;

	switch( $switchByte )
    {
    case 13:
    case 14:
    case 15:
        $arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
        $arrayReturnCode = acucBitReverse( $arrayReturnCode );
        $arrayReturnCode = acucBitShift( $arrayReturnCode, $switchByte * 3 );
        break;
    case 9:
    case 10:
    case 11:
    case 12:
        $arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
        $arrayReturnCode = acucBitShift( $arrayReturnCode, ( 0 - $switchByte ) * 5 );
        break;
    case 5:
    case 6:
    case 7:
    case 8:
        $arrayReturnCode = acucBitShift( $arrayReturnCode, ( 0 - $switchByte ) * 5 );
        $arrayReturnCode = acucBitReverse( $arrayReturnCode );
        break;
    case 0:
    case 1:
    case 2:
    case 3:
    case 4:
        $arrayReturnCode = acucBitShift( $arrayReturnCode, $switchByte * 3 );
        $arrayReturnCode = acucBitArrangeReverse( $arrayReturnCode );
        break;
    }
	
	return $arrayReturnCode;
}

//////////////////////////////////////////////////////
// acucTo6Bit( $arrayCode )
//
//////////////////////////////////////////////////////
function acucTo6Bit( $arrayCode )
{
	$returnArrayCode = array();
	
	$bit6Idx = 0;
	$bit8Idx = 0;
	$byte6Idx = 0;
	$byte8Idx = 0;
	
	$valueByte = 0;
	$passByte = 0;
	$totalBytes = 0;
	
	while( true )
	{
		$passByte = $arrayCode[$byte8Idx] >> $bit8Idx;
		$passByte = ($passByte & 0x01 ) << $bit6Idx;
		$bit6Idx++;
		$bit8Idx++;
		$valueByte |= $passByte;
		if( $bit6Idx == 6 )
		{
			$returnArrayCode[$byte6Idx] = $valueByte;
			$valueByte = 0;
			$bit6Idx = 0;
			$byte6Idx++;
			$totalBytes++;
			if($totalBytes == 28)
				return $returnArrayCode;
		}
		if( $bit8Idx == 8)
		{
			$bit8Idx = 0;
			$byte8Idx++;
		}
	}
}

//////////////////////////////////////////////////////
// acucCodeToPass( $arrayCode )
//
//////////////////////////////////////////////////////
function acucCodeToPass( $arrayCode )
{
	global $acucUsableChars;
	
	$arrayPasscode = array();

    for($idx = 0; $idx < 28; $idx++)
    {
        $arrayPasscode[$idx] = $acucUsableChars[ $arrayCode[$idx] ];
    }
	return $arrayPasscode;
}

?>