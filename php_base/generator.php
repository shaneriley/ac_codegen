<script type="text/javascript">

cursorPosition = -1;

hexNumbers = "0123456789abcdef";

function replaceAll( oldstr, subs, newstr )
{
	retstr = oldstr;
	while( retstr.indexOf( subs ) > -1 )
	{
		retstr = retstr.replace(subs, newstr);
	}
	return retstr;
}

function dechex( decval )
{
	hexStr = decval.toString( 16 );
	if( decval < 16 )
		hexStr = "0" + hexStr;
	return hexStr.toLowerCase();
}

function hexdec( hexval )
{
	decvalue = 0;
	for( idx = 0; idx < hexval.length; idx++ )
	{
		decvalue<<= 4;
		decvalue+= hexNumbers.indexOf( hexval.charAt(idx) );
	}
	return decvalue;
}

function getPermaLink( code )
{
	stringURL = location.protocol + "//" + location.host + location.pathname;
	stringURL = replaceAll(stringURL, "index.php", "");
	
	stringURL += "index.php?mod=generator&data=" + code;
	
	document.getElementById("permaLink").value = stringURL;
}

function cursorId( position )
{
	newStr = "";
	newPos = -1;
	if( position > -1 )
	{
		if( position < 8 )
		{
			newStr = "pName";
			newPos = position;
		}
		else if( position < 16 )
		{
			newStr = "tName";
			newPos = position % 8;
		}
		
		return newStr + newPos;
	}
	else
		return "";
}

function changeOnKeyPress( handler )
{
	if (document.addEventListener)
	{
		document.addEventListener("keypress",typingHandler,false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent("onkeypress", typingHandler);
	}
	else
	{
		document.onkeypress = typingHandler;
	}
}

function changeCursor( position )
{
	oldElement = cursorId( cursorPosition );
	newElement = cursorId( position );
	
	if( oldElement != "" )
		document.getElementById(oldElement).src = './images/blank.gif';
	if( newElement != "" )
		document.getElementById(newElement).src = './images/highlight.gif';
		
	cursorPosition = position;
	
	if( position > -1 )
		changeOnKeyPress( typingHandler );
	else
		changeOnKeyPress( false );
}

function changeLetter( unicode )
{
	if( cursorPosition == -1)
		cursorPosition = 0;
	idx = 8;
	while( Math.pow(2, idx) <= unicode )
	{
		idx+= 8;
	}

	while( idx > 0)
	{
		charValue = (unicode >> (idx - 8)) % 256;
		inputData = "playerData";
		divElem = "playerNameImg";
		hexPosition = cursorPosition;
		if(cursorPosition >= 8)
		{
			inputData = "townData";
			divElem = "townNameImg";
			hexPosition = cursorPosition % 8;
		}
		element = document.getElementById(inputData);
		
		hexString = element.value;
		hexPosition = hexPosition * 2;
		
		element.value = hexString.substring( 0, hexPosition ) + dechex( charValue ) + hexString.substring( hexPosition + 2 );
			
		document.getElementById(divElem).style.background = "url('./imagestring.php?value=" + element.value + "&row=8')";
			
		changeCursor( ( cursorPosition + 1 ) % 16 );
		
		idx-= 8;
	}
	
	showFullCode();
}

function typingHandler( event )
{
	if( cursorPosition == -1)
		return 0;
	unicode = event.keyCode ? event.keyCode : event.charCode;
	
	if( unicode < 256 )
	{
		changeLetter( unicode );
	}
}

function updateNumber(elem, mLength)
{
	numberValue = document.getElementById(elem).value.toLowerCase();
	finalValue = "";
	
	for( cIdx = 0; cIdx < numberValue.length; cIdx++ )
	{
		curChar = numberValue.charCodeAt(cIdx);
		if((curChar > 96 && curChar < 103) || (curChar > 47 && curChar < 58))
			finalValue+= numberValue.charAt(cIdx);
	}
	if(finalValue == "")
		finalValue = "0";
	if(finalValue != numberValue)
		document.getElementById(elem).value = finalValue;
	while(finalValue.length < mLength)
	{
		finalValue = "0" + finalValue;
	}
	document.getElementById(elem + "Value").value = finalValue;
	
	showFullCode();
}

function changeCodeType()
{
	changeCursor(-1);
	cTypeSelect = document.getElementById("codeType");
	codeValue = parseInt(cTypeSelect.options[cTypeSelect.selectedIndex].value);
	
	if( codeValue == -1 )
	{
		document.getElementById("cTypeDiv").style.visibility = "visible";
	}
	else
	{
		document.getElementById("cTypeDiv").style.visibility = "hidden";
		document.getElementById("cType").value = dechex(codeValue);
	}
	updateNumber("cType", 2);
	showFullCode();
}

function showFullCode()
{
	checksum = 0;
	
	playerName = document.getElementById("playerData").value;
	townName = document.getElementById("townData").value;
	
	for(pos = 0; pos < 8; pos++)
	{
		checksum+= hexdec(playerName.substring(pos * 2, pos * 2 + 2 ));
		checksum+= hexdec(townName.substring(pos * 2, pos * 2 + 2 ));
	}
	itemNo = document.getElementById("itemNoValue").value;
	byte1 = document.getElementById("byte1Value").value;
	checksum+= hexdec(itemNo);
	checksum+= 0xff;
	
	checkCode = checksum % 4;
	
	codeType = hexdec(document.getElementById("cTypeValue").value);
	
	byte0 = dechex(codeType + (checkCode << 3));
	
	document.getElementById("fullCode").value = byte0 + byte1 + playerName + townName + itemNo;
	
	generateBlock();
}

function generateBlock()
{
	sendString = document.getElementById("fullCode").value;
	getPermaLink(sendString);
	
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		generateXML=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		generateXML=new ActiveXObject("Microsoft.XMLHTTP");
	}
	generateXML.onreadystatechange=function()
	{
		if (generateXML.readyState==4 && generateXML.status==200)
		{
			document.getElementById("generatorArea").innerHTML=generateXML.responseText;
		}
	}
	generateXML.open("GET","./generator.output.php?data=" + sendString,true);
	generateXML.send();
}

function parseFullCode()
{
	var codeData = document.getElementById("fullCode").value;
	var codeTypeList = document.getElementById("codeType");
	
	var byte0 = hexdec(codeData.substring(0, 2)) & 231;
	var byte1 = codeData.substring(2, 4);
	var playerName = codeData.substring(4, 20);
	var townName = codeData.substring(20, 36);
	var itemNo = codeData.substring(36, 40);
	
	document.getElementById("playerData").value = playerName;
	document.getElementById("townData").value = townName;
	document.getElementById("itemNo").value = itemNo;
	document.getElementById("byte1").value = byte1;
	
	document.getElementById("playerNameImg").style.background = "url('./imagestring.php?value=" + playerName + "&row=8')";
	document.getElementById("townNameImg").style.background = "url('./imagestring.php?value=" + townName + "&row=8')";
	
	updateNumber("itemNo", 4);
	updateNumber("byte1", 2);
		
	for( var opt = 0; opt < codeTypeList.options.length; opt++ )
	{
		if(byte0 == parseInt(codeTypeList.options[opt].value))
			codeTypeList.selectedIndex = opt;
	}
	changeCodeType();
}
</script>
<p class="secondaryHeader">Generator</p>
<form action="">
 <table style="width: 100%;" border="0">
  <tr>
   <td style="width: 10%;" rowspan="3">&nbsp;</td>
   <td style="width: 96px; padding: 0px 0px 0px 0px;">
    <b>Player name:</b><br />
    <div id="playerNameImg" style="width: 96px; height: 16px; background: url('./imagestring.php?value=2020202020202020&amp;row=8');"><?php
for( $i = 0; $i < 8; $i++ )
{
	echo "<a href=\"javascript:changeCursor($i)\"><img src=\"./images/blank.gif\" style=\"width: 12px; height: 16px\" alt=\"Player name\" id=\"pName" . $i . "\" /></a>";
}
?></div>
    <input type="text" readonly="readonly" id="playerData" value="2020202020202020" style="font-size: xx-small; font-family: monospace;" size="16" /><br /><br />
	<b>Town name:</b><br />
	<div id="townNameImg" style="width: 96px; height: 16px; background: url('./imagestring.php?value=2020202020202020&amp;row=8');"><?php
for( $i = 0; $i < 8; $i++ )
{
	echo "<a href=\"javascript:changeCursor(".($i + 8).")\"><img src=\"./images/blank.gif\" style=\"width: 12px; height: 16px;\" alt=\"Town name\" id=\"tName" . $i . "\" /></a>";
}
?></div>
    <input type="text" readonly="readonly" id="townData" value="2020202020202020" style="font-size: xx-small; font-family: monospace;" size="16" /><br /><br />
   </td>
   <td>
    <div style="width: 192px; height: 256px; background: url('./images/acucfont.png'); padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px; line-height: 0px;"><?php
	for( $i = 0; $i < 256; $i++ )
	{
		if($i > 0 && $i % 16 == 0)
			echo "<br />\n";
		echo "<a href=\"javascript:changeLetter($i)\"><img src=\"./images/blank.gif\" style=\"width: 12px; height: 16px;\" alt=\"$i\" onmouseover=\"this.src='./images/highlight.gif'\" onmouseout=\"this.src='./images/blank.gif'\" /></a>";
	}
	?></div>
   </td>
   <td>&nbsp;</td>
   <td style="width: 10%;" rowspan="3">&nbsp;</td>
  </tr>
  <tr>
   <td>
   	<b>Item number:</b><br />
	<input type="text" id="itemNo" style="font-size: x-large; font-family: monospace;" size="4" maxlength="4" onclick="changeCursor(-1);" onkeyup="updateNumber('itemNo', 4)" /><input type="hidden" id="itemNoValue" value="0000" /><br /><br />
   </td>
   <td style="width: 192px;"><div style="overflow: auto; height: 120px;"><?php
include("listitems.php");
   ?></div></td>
   <td>
    <b>Code type:</b><br />
	<select id="codeType" onclick="changeCodeType()" onchange="changeCodeType()">
<?php
reset($acucCodeTypes);
while(list($key, $val) = each($acucCodeTypes))
{
	echo "     <option value=\"$key\">" . dechexpad($key, 2) . " - " . $acucTypes[$val] . "</option>\n";
}
?>
	 <option value="-1">Custom</option>
	</select><br />
	<table border="0">
	 <tr>
	  <td style="width: 50%;">
	   <b>Byte 1:</b><br />
		<input type="text" id="byte1" style="font-size: x-large; font-family: monospace;" size="2" maxlength="2" onclick="changeCursor(-1);" onkeyup="updateNumber('byte1', 2)" value="ff" /><input type="hidden" id="byte1Value" value="ff" />
	  </td>
	  <td style="width: 50%;">
	   <div style="width: 100%; height: 100%; visibility: hidden;" id="cTypeDiv"><b>Code type:</b><br />
	   <input type="text" id="cType" style="font-size: x-large; font-family: monospace;" size="2" maxlength="2" onclick="changeCursor(-1);" onkeyup="updateNumber('cType', 2)" value="00" /><input type="hidden" id="cTypeValue" value="00" /></div>
	  </td>
	 </tr>
	</table>
	<input type="text" id="fullCode" style="font-family: monospace;" value="2020202020202020202020202020202020202020" size="40" readonly="readonly" />
   </td>
  </tr>
  <tr>
   <td style="text-align: center;" colspan="3">
    <span style="font-size: xx-small;">Permalink:</span><br />
    <input type="text" readonly="readonly" id="permaLink" class="permaLinkBlock" size="90" /><input type="button" value="Visit URL" class="permaLinkBlock" onclick="location.href=document.getElementById('permaLink').value" id="permaLinkButton" />
	<div id="generatorArea" class="resultBlock">&nbsp;</div>
   </td>
  </tr>
 </table>
</form>
<script type="text/javascript">
changeCodeType();

<?php
if($_REQUEST["data"])
{
	echo "document.getElementById(\"fullCode\").value=\"" . $_REQUEST["data"] . "\";\n";
	echo "parseFullCode();\n";
}
?>
</script>