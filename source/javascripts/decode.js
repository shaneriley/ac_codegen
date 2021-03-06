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

function acucTo8Bit(code) {
  var 8bit_code = [],
      bit6_idx = 0,
      bit8_idx = 0,
      byte6_idx = 0,
      byte8_idx = 0,
      value_byte = 0,
      current_bit = 0;

  while (true) {
    current_bit = (code[byte6_idx] >> bit6_idx) & 0x01;
    current_bit <<= bit8_idx;
    bit6_idx++;
    bit8_idx++;
    value_byte |= current_bit;

    if (bit8_idx === 8) {
      8bit_code[byte8_idx] = value_byte;
      byte8_idx++;
      if (byte8_idx === 21) { return 8bit_code; }
      bit8_idx = 0;
      value_byte = 0;
    }
    if (bit6_idx === 6) {
      bit6_idx = 0;
      byte6_idx++;
    }
  }
}

function acucDecodeBitShuffle(code, used_key) {
  var buffer = [],
      char_offset = used_key ? 2 : 13,
      char_count = used_key ? 20 : 19,
      table_idx, index_table,
      output_offset, value_byte,
      output_buffer = [],
      result_code;

  buffer = code.slice(0, char_offset).concat(code.slice(char_offset + 1, 20 - char_offset));

  table_idx = (code[char_offset] << 2) & 0x0c;
  index_table = acucSelectIndexTable[table_idx >> 2];

  for (i = 0; i < char_count; i++) {
    for (j = 0; j < 8; j++) {
      output_offset = index_table[j] + i;
      output_offset %= char_count;
      value_byte = buffer[output_offset];
      value_byte = (value_byte >> j) & 0x01;
      value_byte <<= j;
      output_buffer[i] |= value_byte;
    }
  }

  result_code = output_buffer.slice(0, char_offset).concat(code.slice(char_offset, 1), output_buffer.slice(char_offset, 20 - char_offset));
  result_code = result_code.concat(code.slice(result_code.length));
  return result_code;
}

function acucDecodeBitCode(code) {
  var decoded = code.slice(0),
      code_method = code[1] & 0x0f;

  if (code_method > 12) {
    decoded = acucBitShift(decoded, (0 - code_method) * 3);
    decoded = acucBitReverse(decoded);
    decoded = acucBitArrangeReverse(decoded);
  }
  else if (code_method > 8) {
    decoded = acucBitShift(decoded, code_method * 5);
    decoded = acucBitArrangeReverse(decoded);
  }
  else if (code_method > 4) {
    decoded = acucBitReverse(decoded);
    decoded = acucBitShift(decoded, code_method * 5);
  }
  else {
    decoded = acucBitArrangeReverse(decoded);
    decoded = acucBitShift(decoded, (0 - code_method) * 3);
  }

  return decoded;
}

function acucDecodeRSACipher(code) {
  var mod_count = 0,
      decoded = code.slice(0),
      prime_params = acucGetRSAKeyCode(code),
      prime_1 = prime_params[0],
      prime_2 = prime_params[1],
      prime_3 = prime_params[2],
      index_table = prime_params[3],
      prime_product = prime_1 * prime_2,
      less_product = (prime_1 - 1) * (prime_2 - 1),
      mod_value, value_byte, end_value, const_value_byte;

  do {
    mod_count++;
    end_value = (mod_count * less_product + 1) % prime_3;
    mod_value = (mod_count * less_product + 1) / prime_3;
  } while (end_value !== 0);

  for (var i = 0; i < 8; i++) {
    value_byte = code[index_table[i]];
    value_byte |= ((code[20] >> i) << 8) & 0x0100;
    const_value_byte = value_byte;
    for (var j = 0; j < mod_value - 1; j++) {
      value_byte = (value_byte * const_value_byte) % prime_product;
    }

    decoded[index_table[i]] = value_byte & 0xff;
  }

  return decoded;
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
