<?
include_once("functions.php");

echo acucValuesToString($acucUsableChars);

echo "<br />\n";
sort($acucUsableChars);

echo acucValuesToString($acucUsableChars);

echo "<br />\n";

echo chr(0x23);
echo "<br />\n";
echo ord("#");
echo "<br />\n";
echo dechex(ord("#"));
?>