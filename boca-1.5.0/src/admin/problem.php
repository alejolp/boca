<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 21/jul/2012 by cassio@ime.usp.br
require('header.php');
if(($ct = DBContestInfo($_SESSION["usertable"]["contestnumber"])) == null)
	ForceLoad("../index.php");

if (isset($_GET["delete"]) && is_numeric($_GET["delete"]) && isset($_GET["input"])) {
	$param = array();
	$param['number']=$_GET["delete"];
	$param['inputfilename']=$_GET["input"];
	if(!DBDeleteProblem ($_SESSION["usertable"]["contestnumber"], $param)) {
		MSGError('Error deleting problem');
		LogError('Error deleting problem');
	}
	ForceLoad("problem.php");
}

if (isset($_POST["Submit3"]) && isset($_POST["problemnumber"]) && is_numeric($_POST["problemnumber"]) && 
    isset($_POST["problemname"]) && $_POST["problemname"] != "") {
	if ($_POST["confirmation"] == "confirm") {
		if ($_FILES["probleminput"]["name"] != "") {
			$type=myhtmlspecialchars($_FILES["probleminput"]["type"]);
			$size=myhtmlspecialchars($_FILES["probleminput"]["size"]);
			$name=myhtmlspecialchars($_FILES["probleminput"]["name"]);
			$temp=myhtmlspecialchars($_FILES["probleminput"]["tmp_name"]);
			if (!is_uploaded_file($temp)) {
				IntrusionNotify("file upload problem.");
				ForceLoad("../index.php");
			}
		} else $name = "";

		$param = array();
		$param['number'] = $_POST["problemnumber"];
		$param['name'] = $_POST["problemname"];
		$param['inputfilename'] = $name;
		$param['inputfilepath'] = $temp;
		$param['fake'] = 'f';
		$param['colorname'] = $_POST["colorname"];
		$param['color'] = $_POST["color"];
		if($param['color']=='') $param['color']=-1;
		DBNewProblem ($_SESSION["usertable"]["contestnumber"], $param);
	}
	ForceLoad("problem.php");
}
?>
<br>
  <script language="javascript">
    function conf2(url) {
      if (confirm("Confirm the DELETION of the PROBLEM and ALL data associated to it (including the SUBMISSIONS)?")) {
		  if (confirm("Are you REALLY sure about what you are doing? DATA CANNOT BE RECOVERED!")) {
			  document.location=url;
		  } else {
			  document.location='problem.php';
		  }
      } else {
        document.location='problem.php';
      }
    }
  </script>
<table width="100%" border=1>
 <tr>
  <td><b>Problem #</b></td>
  <td><b>Short Name</b></td>
  <td><b>Fullname</b></td>
  <td><b>Basename</b></td>
  <td><b>Descfile</b></td>
  <td><b>Package file</b></td>
<!--  <td><b>Compare file</b></td>
  <td><b>Timelimit</b></td>-->
  <td><b>Color</b></td>
 </tr>
<?php
	$prob = DBGetFullProblemData($_SESSION["usertable"]["contestnumber"],true);
for ($i=0; $i<count($prob); $i++) {
  echo " <tr>\n";
  if($prob[$i]["fake"]!='t') {
	  echo "  <td nowrap><a href=\"javascript: conf2('problem.php?delete=" . $prob[$i]["number"] . "&input=" . rawurlencode($prob[$i]["inputfilename"]) . 
	"')\">" . $prob[$i]["number"] . "</a></td>\n";
  } else {
    echo "  <td nowrap>" . $prob[$i]["number"] . " (fake)</td>\n";
  }
  echo "  <td nowrap>" . $prob[$i]["name"] . "</td>\n";
  echo "  <td nowrap>" . $prob[$i]["fullname"] . "&nbsp;</td>\n";
  echo "  <td nowrap>" . $prob[$i]["basefilename"] . "&nbsp;</td>\n";
  if (isset($prob[$i]["descoid"]) && $prob[$i]["descoid"] != null && isset($prob[$i]["descfilename"])) {
	  echo "  <td nowrap><a href=\"../filedownload.php?" . filedownload($prob[$i]["descoid"], $prob[$i]["descfilename"]) . "\">" . 
		  basename($prob[$i]["descfilename"]) . "</td>\n";
  }
  else
    echo "  <td>&nbsp;</td>\n";
  if ($prob[$i]["inputoid"] != null) {
    $tx = $prob[$i]["inputhash"];
    echo "  <td nowrap><a href=\"../filedownload.php?" . filedownload($prob[$i]["inputoid"] ,$prob[$i]["inputfilename"]) ."\">" .
		$prob[$i]["inputfilename"] . "</a> " . 
		"<img title=\"hash: $tx\" alt=\"$tx\" width=\"25\" src=\"../images/bigballoontransp-hash.png\" />" . 
        "</td>\n";
  }
  else
    echo "  <td nowrap>&nbsp;</td>\n";
/*
  if ($prob[$i]["soloid"] != null) {
    $tx = $prob[$i]["solhash"];
    echo "  <td nowrap><a href=\"../filedownload.php?" . filedownload($prob[$i]["soloid"],$prob[$i]["solfilename"]) ."\">" . 
	$prob[$i]["solfilename"] . "</a> ".
	"<img title=\"hash: $tx\" alt=\"$tx\" width=\"25\" src=\"../images/bigballoontransp-hash.png\" />" . 
	"</td>\n";
  }
  else
    echo "  <td nowrap>&nbsp;</td>\n";
  if ($prob[$i]["timelimit"]!="")
    echo "  <td nowrap>" . $prob[$i]["timelimit"] . "</td>\n";
  else
    echo "  <td nowrap>&nbsp;</td>\n";
*/
  if ($prob[$i]["color"]!="") {
	  echo "  <td nowrap>" . $prob[$i]["colorname"] . 
		  "<img title=\"".$prob[$i]["color"]."\" alt=\"".$prob[$i]["colorname"]."\" width=\"25\" src=\"" . 
		  balloonurl($prob[$i]["color"]) . "\" /></td>\n";
  } else
    echo "  <td nowrap>&nbsp;</td>\n";
  echo " </tr>\n";
}
echo "</table>";
if (count($prob) == 0) echo "<br><center><b><font color=\"#ff0000\">NO PROBLEMS DEFINED</font></b></center>";

?>

<br><br><center><b>Clicking on a problem number will delete it.<br>
WARNING: deleting a problem will remove EVERYTHING related to it.<br>
It is NOT recommended to change anything while the contest is running.<br>
To import a problem, fill in the following fields.<br>
To replace the data of a problem, proceed as if it did not exist (data will be replaced without removing it).</b></center>

<form name="form1" enctype="multipart/form-data" method="post" action="problem.php">
  <input type=hidden name="confirmation" value="noconfirm" />
  <script language="javascript">
    function conf() {
			if(document.form1.problemname.value=="") {
				alert('Sorry, mandatory fields are empty');
			} else {
/*
				var s1 = String(document.form1.problemdesc.value);
				var l = s1.length;
				if(l >= 3 && (s1.substr(l-3,3).toUpperCase()==".IN" ||
							 s1.substr(l-4,4).toUpperCase()==".OUT" ||
							 s1.substr(l-4,4).toUpperCase()==".SOL" ||
							 s1.substr(l-2,2).toUpperCase()==".C" ||
							 s1.substr(l-2,2).toUpperCase()==".H" ||
							 s1.substr(l-3,3).toUpperCase()==".CC" ||
							 s1.substr(l-3,3).toUpperCase()==".GZ" ||
							 s1.substr(l-4,4).toUpperCase()==".CPP" ||
							 s1.substr(l-4,4).toUpperCase()==".HPP" ||
							 s1.substr(l-4,4).toUpperCase()==".ZIP" ||
							 s1.substr(l-4,4).toUpperCase()==".TGZ" ||
							 s1.substr(l-5,5).toUpperCase()==".JAVA")) {
					alert('Description file has invalid extension: ...'+s1.substr(l-3,3));
				} else {
*/
				var s2 = String(document.form1.probleminput.value);
				if(s2.length > 4) {
					if (confirm("Confirm?")) {
						document.form1.confirmation.value='confirm';
					}
				} else {
					alert('File package must be given');
				}
			}
    }
  </script>
  <center>
    <table border="0">
      <tr>
        <td width="35%" align=right>Number:</td>
        <td width="65%">
          <input type="text" name="problemnumber" value="" size="20" maxlength="20" />
        </td>
      </tr>
      <tr>
	 <td width="35%" align=right>Short Name (usually a letter):</td>
        <td width="65%">
          <input type="text" name="problemname" value="" size="20" maxlength="20" />
        </td>
      </tr>
<!--
      <tr>
        <td width="35%" align=right>Problem Fullname:</td>
        <td width="65%">
          <input type="text" name="fullname" value="" size="50" maxlength="100" />
        </td>
      </tr>
      <tr>
	 <td width="35%" align=right>Problem Basename (a.k.a. name of class expected to have the main):</td>
        <td width="65%">
          <input type="text" name="basename" value="" size="50" maxlength="100" />
        </td>
      </tr>
      <tr>
	 <td width="35%" align=right>Description file (PDF, txt, ...):</td>
        <td width="65%">
          <input type="file" name="problemdesc" value="" size="40" />
        </td>
      </tr>
-->
      <tr>
	 <td width="35%" align=right>Problem package (ZIP):</td>
        <td width="65%">
          <input type="file" name="probleminput" value="" size="40" />
        </td>
      </tr>
<!--
      <tr>
	 <td width="35%" align=right>Compare file archive (ZIP):</td>
        <td width="65%">
          <input type="file" name="problemsol" value="" size="40" />
        </td>
      </tr>
      <tr>
        <td width="35%" align=right>Timelimit (in sec):</td>
        <td width="65%">
          <input type="text" name="timelimit" value="" size="10" />
(optional: use a , followed by the number of repetitions to run)
        </td>
      </tr>
-->
      <tr>
        <td width="35%" align=right>Color name:</td>
        <td width="65%">
          <input type="text" name="colorname" value="" size="40" maxlength="100" />
        </td>
      </tr>
      <tr>
        <td width="35%" align=right>Color (RGB HTML format):</td>
        <td width="65%">
          <input type="text" name="color" value="" size="6" maxlength="6" />
        </td>
      </tr>
    </table>
  </center>
  <center>
      <input type="submit" name="Submit3" value="Send" onClick="conf()">
      <input type="reset" name="Submit4" value="Clear">
  </center>
</form>

</body>
</html>