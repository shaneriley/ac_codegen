function acucAdjustLetter(passcode) {
  var adjusted_passcode = passcode.slice(0),
      replacement = ["O", "l"],
      keys;
  for (var i = 0; i < 2; i++) {
    keys = $.map(adjusted_passcode, function(v, idx) { return (v === 0x30 + i ? i : undefined); });
    for (var j = 0; j < keys.length; j++) {
      adjusted_passcode[keys[j]] = replacement[i].charCodeAt(0);
    }
  }

  return adjusted_passcode;
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
function acucPassToCode(passcode) {
  var code = passcode.slice(0),
      idx;

  if (count(passcode) < 28) { return false; }

  for (var i = 0; i < 28; i++) {
    idx = acucUsableChars.indexOf(passcode[i]);
    if (idx === -1) { return false; }
    code[i] = passcode[idx];
  }

  return code;
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
