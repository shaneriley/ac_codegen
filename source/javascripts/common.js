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

function acucBitShift(keycode, shift) {
  var buffer = [keycode[0]].concat(keycode.slice(2, 19)),
      output = [],
      destination, offset;

  if (shift > 0) {
    destination = floor(shift / 8);
    offset = shift % 8;

    for (var i = 0; i < 20; i++)
    {
      output[(i + destination) % 20] = ((buffer[i] << offset) | ((buffer[(i + 19) % 20]) >> (8 - offset))) & 0xff;
    }

    buffer = acucStringToValues(acucValuesToString(output));
  }
  else if (shift < 0) {
    for (var i = 0; i < 20; i++) {
      output[i] = buffer[19 - i];
    }
    shift = 0 - shift;

    destination = ~~(shift / 8);
    offset = shift % 8;

    for (i = 0; i < 20; i++) {
      buffer[(i + destination) % 20] = output[i];
    }

    for (i = 0; i < 20; i++) {
      output[i] = ((buffer[i] >> offset) | ((buffer[(i + 19) % 20]) << (8 - offset))) & 0xff;
    }
    for (i = 0; i < 20; i++) {
      buffer[i] = output[19 - i];
    }
  }
  return ([]).concat(buffer.slice(0, 1), keycode.slice(1, 1), buffer.slice(1, 19));
}

function acucBitReverse(code) {
  var copy = code.slice(0);
  for (var i = 0; i < 21; i++) {
    i !== 1 && (copy[i] ^= 0xff);
  }
  return copy;
}

function acucBitArrangeReverse(code) {
  var buffer = ([]).concat(code.slice(0, 1), code.slice(2, 19)),
      output_buffer = [],
      source, destination;

  for (var i = 0; i < 20; i++) {
    source = buffer[19 - i];
    destination =
        ((source & 0x80) >> 7) |
        ((source & 0x40) >> 5) |
        ((source & 0x20) >> 3) |
        ((source & 0x10) >> 1) |
        ((source & 0x08) << 1) |
        ((source & 0x04) << 3) |
        ((source & 0x02) << 5) |
        ((source & 0x01) << 7);
    output_buffer[i] = destination;
  }

  return ([]).concat(output_buffer.slice(0, 1), code.slice(1, 1), output_buffer.slice(1, 19));
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
