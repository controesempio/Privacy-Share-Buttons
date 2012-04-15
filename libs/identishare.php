<?php
//
// Copyright (C) 2011 Jacob Barkdull, Roberto Guido
//
//   This program is free software: you can redistribute it and/or modify
//   it under the terms of the GNU Affero General Public License as
//   published by the Free Software Foundation, either version 3 of the
//   License, or (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

if (isset($_GET["source"])) {
	header("Content-type: text/plain");
	echo file_get_contents("." . $_SERVER["PHP_SELF"]);
	die();
}

$server = $_SERVER["SERVER_NAME"];
if (isset($_SERVER["HTTP_REFERER"])) {
	$referer = $_SERVER["HTTP_REFERER"];
	$referringurl = str_replace(array("http://", "www."), "", $referer);
	$jsondata = file_get_contents("http://identi.ca/api/search.json?q=" . $referringurl . "&rpp=100");
	$results = substr_count($jsondata, str_replace("/", "\/", addslashes($referringurl)));

	if ($results <= 0) {
		$results = "0";
	}
} else {
	$results = "0";
	$referer = "http://www.tildehash.com/";
}
$referer = str_replace(array("?", "&"), array("%3F", "%26"), $referer);
$referringurl = str_replace(array("?", "&"), array("%3F", "%26"), $referringurl);

$style = '<a href="http://identi.ca/index.php?action=newnotice&status_textarea=PAGE_TITLE_HERE' . $referer . '" target="_blank" style="display: inline-block; background-image: url(\'http://' . $server . '/identishare/share.png\'); background-repeat: no-repeat; width: 61px; height: 53px; padding: 10px 0px 0px 0px !important; margin: 0px !important; font-family: \'Arial\'; font-size: 20px; text-decoration: none; line-height: 1.2em; color: #000000; text-align: center;" title="Share on Identi.ca"><b style="float: none !important; margin: 0px !important;">' . $results . '</b></a>';

$style2 = <<<STYLE2
<div style="width: 130px; height: 23px; margin: 2px 0px 4px 0px;">
  <div dir="ltr" style="position:relative">
				<div style="height: 23px;">
				<div>
					<a href="http://identi.ca/index.php?action=newnotice&status_textarea=PAGE_TITLE_HERE${referer}" target="_blank" style="display: inline-block; background-image: url('identishare/button.png'); font-family: arial; text-decoration: none; line-height: 1.2em; color: #000000; width: 50px; height: 20px;" title="Share on Identi.ca"></a>
				</div>
				<div style="position: absolute; top: 1px; left:51px; background: no-repeat url('identishare/bubble-right.png'); height: 23px; width: 50px; text-align: center; overflow: hidden; font-size: 12px; padding-top:1px;">
						<a href="http://identi.ca/search/notice?q=${referringurl}&search=Search" target="_blank" style="vertical-align: top; color: #666666; font-family: 'Arial',sans-serif; text-decoration:none;  padding-right:8px"><b>$results</b></a>
					</div>
				</div>
			</div>
		</div>
STYLE2;

if (isset($_GET["style2"])) {
	$style = $style2;
}

if (!isset($_GET["noscript"])) {
	$style = str_replace(array("\n", "\t"), "", addslashes($style));
}

if (isset($_GET["title"])) {
	$style = str_replace("PAGE_TITLE_HERE", str_replace(array("?", "&"), array("%3F", "%26"), $_GET["title"]) . " ", $style);
} else {
	if (!isset($_GET["noscript"])) {
		$style = str_replace("PAGE_TITLE_HERE", '"+document.title+" - ', $style);
	} else {
		$style = str_replace("PAGE_TITLE_HERE", "", $style);
	}
}

$html = <<<HTML
<html>
	<head>
		<title>Share on Identi.ca</title>
	</head>

	<body marginwidth="0" marginheight="0">
		${style}
	</body>
</html>
HTML;

if (isset($_GET["noscript"])) {
	echo $html;
} else {
	header("Content-type: text/javascript");
	echo 'document.getElementById("identishare").style.display="inline-block";'."\n";
	echo 'document.getElementById("identishare").style.width="140px";'."\n";
	echo 'document.getElementById("identishare").style.overflow="hidden";'."\n";
	echo 'document.getElementById("identishare").innerHTML="' . $style . '";';
}
?>
