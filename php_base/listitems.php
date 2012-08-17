<table style="width:100%; border-spacing: 0px 0px; font-size: medium;" border="0">
<?php

include("./itemlist.php");

reset( $itemlist );

$prevItem = "";

$useBG = 0;

while( list( $key, $val ) = each($itemlist) )
{
	if( $prevItem != $val )
	{
		if( $prevItem != "" )
		{
			echo "</td>\n";
			echo "  <td class=\"central\">$prevItem</td>\n";
			echo " </tr>\n";
		}
		$prevItem = $val;
		echo " <tr class=\"tableBG" . $useBG . "\">\n";
		echo "  <td class=\"tableList\">";
		
		$useBG = 1 - $useBG;
	}
	else
		echo "<br />\n";
	echo "<a href=\"javascript:document.getElementById('itemNo').value='";
	echo dechexpad($key, 2);
	echo "';updateNumber('itemNo');\">";
	echo dechexpad($key, 2);
	echo "</a>";
}
echo "</td>\n";
echo "  <td class=\"central\">$prevItem</td>\n";
echo " </tr>\n";
?>
</table>