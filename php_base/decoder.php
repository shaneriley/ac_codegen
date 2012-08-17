<script type="text/javascript">
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

function updateText()
{
	sendString = document.getElementById("codeArea").value;
	
	sendString = replaceAll(sendString, " ", "");
	sendString = replaceAll(sendString, "\n", "");
	sendString = replaceAll(sendString, "\r", "");
	
	hexString = "";

	for( idx = 0; idx < sendString.length && idx < 28; idx++ )
	{
		if(sendString.charAt( idx ) == "#" )
			hexString = hexString + "d1";
		else
			hexString = hexString + dechex( sendString.charCodeAt( idx ) );
	}
	
	while( idx < 28 )
	{
		hexString = hexString + "20";
		idx++;
	}
	
	document.getElementById("passwordImage").src = "./imagestring.php?value=" + hexString + "&row=14";
	
	if( sendString.length == 28 )
	{
		decodeBlock();
		document.getElementById("permaLinkButton").disabled = "";
	}
	else
	{
		document.getElementById("permaLinkButton").disabled="disabled";
		document.getElementById("permaLink").value="";
	}
}

function getPermaLink( code )
{
	stringURL = location.protocol + "//" + location.host + location.pathname;
	stringURL = replaceAll(stringURL, "index.php", "");
	
	stringURL += "index.php?mod=decoder&code=" + code;
	
	document.getElementById("permaLink").value = stringURL;
}

function decodeBlock()
{
	sendString = document.getElementById("codeArea").value;
	
	sendString = replaceAll(sendString, " ", "");
	sendString = replaceAll(sendString, "\n", "");
	sendString = replaceAll(sendString, "\r", "");
	sendString = replaceAll(sendString, '%', '_');
	sendString = replaceAll(sendString, '&', '-');
	sendString = replaceAll(sendString, '#', '|');
	
	getPermaLink(sendString);
	
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		decodeXML=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		decodeXML=new ActiveXObject("Microsoft.XMLHTTP");
	}
	decodeXML.onreadystatechange=function()
	{
		if (decodeXML.readyState==4 && decodeXML.status==200)
		{
			document.getElementById("decodeArea").innerHTML=decodeXML.responseText;
		}
	}
	decodeXML.open("GET","./decode.output.php?code=" + sendString,true);
	decodeXML.send();
}
</script>
<p class="secondaryHeader">Decoder</p>
<div style="text-align: center;">
 <form action="">
  <textarea id="codeArea" rows="2" cols="14" class="codeBlock" onkeyup="updateText();"></textarea>
  <br />
  <span style="font-size: xx-small;">Permalink:</span><br />
  <input type="text" readonly="readonly" id="permaLink" class="permaLinkBlock" size="90" /><input type="button" value="Visit URL" class="permaLinkBlock" onclick="location.href=document.getElementById('permaLink').value" disabled="disabled" id="permaLinkButton" />
 </form><br />
 <img src="./imagestring.php?value=20202020202020202020202020202020202020202020202020202020&amp;row=14" alt="Password" id="passwordImage" />
</div>
<table borders="0" style="width: 100%">
 <tr>
  <td style="width: 20%;">
   &nbsp;
  </td>
  <td style="width: 60%;">
   <div id="decodeArea" class="resultBlock">Here information of the code will be presented.</div>
  </td>
  <td style="width: 20%;">
   &nbsp;
  </td>
 </tr>
</table>
<?php
if(array_key_exists("code", $_REQUEST))
{
	echo "<script type=\"text/javascript\">\n";
	
	echo "document.getElementById(\"codeArea\").value = \"";
	$codePrint = $_REQUEST["code"];
	$codePrint = str_replace(" ", "", $codePrint);
	$codePrint = str_replace("\n", "", $codePrint);
	$codePrint = str_replace("\r", "", $codePrint);
	$codePrint = str_replace("_", "%", $codePrint);
	$codePrint = str_replace("-", "&", $codePrint);
	$codePrint = str_replace("|", "#", $codePrint);
	
	echo substr($codePrint, 0, 14);
	echo '\n';
	echo substr($codePrint, 14);
	echo "\";\n";

	echo "updateText();\n";
	echo "</script>";
}
?>