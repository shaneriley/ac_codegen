var acucStringModifier = [
  "NiiMasaru",
  "KomatsuKunihiro",
  "TakakiGentarou",
  "MiyakeHiromichi",
  "HayakawaKenzo",
  "KasamatsuShigehiro",
  "SumiyoshiNobuhiro",
  "NomaTakafumi",
  "EguchiKatsuya",
  "NogamiHisashi",
  "IidaToki",
  "IkegawaNoriko",
  "KawaseTomohiro",
  "BandoTaro",
  "TotakaKazuo",
  "WatanabeKunio",
  "RichAmtower",
  "KyleHudson",
  "MichaelKelbaugh",
  "RaycholeLAneff",
  "LeslieSwan",
  "YoshinobuMantani",
  "KirkBuchanan",
  "TimOLeary",
  "BillTrinen",
  "nAkAyOsInoNyuuSankin",
  "zendamaKINAKUDAMAkin",
  "OishikutetUYOKUNARU",
  "AsetoAminofen",
  "fcSFCn64GCgbCGBagbVB",
  "YossyIsland",
  "KedamonoNoMori"
];

var acucKeyIndex = [0x00000012, 0x00000009];

function acucStringToValues(str) {
  var char_codes = [];
  $.each(str, function(i) {
    char_codes.push(str.charCodeAt(i));
  });
  return char_codes;
}

function acucValuesToString(char_codes, start_pos, end_pos) {
  var str = "",
      start_pos = start_pos || 0,
      end_pos = end_pos || char_codes.length;

  for (var i = start_pos; i < end_pos; i++) {
    str += String.fromCharCode(char_codes[i]);
  }
  return str;
}

function acucTranspositionCipher(char_code, direction, key_used) {
  var string_offset = char_code[acucKeyIndex[key_used]] & 0x0f,
      string_table = string_offset + (key_used * 16),
      array_string = acucStringToValues(acucStringModifier[string_table]),
      string_length = array_string.length,
      string_idx = 0,
      return_code = [];

  for (var i = 0; i < 21; i++) {
    return_code[i] = char_code[i];
    if (acucKeyIndex[key_used] !== i) {
      return_code[i] += array_string[string_idx] * direction;
      if (return_code[i] < 0) {
        return_code[i] += 256;
      }
      return_code[i] %= 256;
      string_idx++;
      string_idx %= string_length;
    }
  }
  return return_code;
}

function acucBitShift($arrayCode, $shift) {
  var $arrayBuffer = array_merge( array_slice( $arrayCode, 0, 1 ), array_slice( $arrayCode, 2, 19) );
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
