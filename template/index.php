<!DOCTYPE html>
<html lang="en">
<head>
    <title>Root page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php
    echo ASSETS_URL; ?>/assets/css/main.css" rel="stylesheet">
</head>
<script language="javascript">
    function showBox(catName) {
        var xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            alert('Your browser does not support AJAX!');
            return;
        }

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4) {
                var dd = document.getElementById('box');
                dd.innerHTML = xmlHttp.responseText;
            }
        };

        xmlHttp.open('GET', '?ajax_cat=' + escape(catName), true);
        xmlHttp.send(null);
    }

    function GetXmlHttpObject() {
        var xmlHttp = null;

        // Firefox, Opera 8.0+, Safari
        try {
            xmlHttp = new XMLHttpRequest();
        }

            // Internet Explorer
        catch (e) {
            try {
                xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
            }
        }

        return xmlHttp;
    }

    function showFavorites() {
        var cookies = new Array;
        var favs_txt = '';
        for (c in C = document.cookie.split('; '))
            cookies[(cs = C[c].split('='))[0]] = unescape(cs[1]);

        for (i in cookies) {
            if (i.substr(0, 5) == 'favs_')
                favs_txt +=
                    '<input type="checkbox" name="' + i.substr(5, i.length) + '" value="' + cookies[i] + '" onClick="setFavorites(this);" checked>' +
                    '<a href="?d=' + cookies[i] + '">' + i.substr(5, i.length) + '</a><br>';
        }

        favs_txt = (favs_txt != '') ? favs_txt : 'No favorites';
        document.getElementById('favorites').innerHTML = favs_txt;

    }

    function setFavorites(obj) {
        if (obj.checked) {
            document.cookie = 'favs_' + obj.name + '=' + escape(obj.value) + ';path=/;expires=Thu, 01-Jan-2010 00:00:01 GMT';
        } else {
            document.cookie = 'favs_' + obj.name + '=;path=/;expires=Thu, 01-Jan-1970 00:00:01 GMT';

            var inputs = document.getElementsByTagName('input');
            if (inputs)
                for (var i = 0; i < inputs.length; ++i)
                    if (typeof (inputs[i]) == 'object' && inputs[i].type == 'checkbox' && inputs[i].name == obj.name)
                        inputs[i].checked = false;
        }
        showFavorites();
    }

</script>
</head>
<body>
<?php
if ($view_mode !== "simple") : ?>
    #TODO
    <!--<center><a href='http://gnooo.com/'><img border=0 src="logo.gif" vspace=5 ></a><br></center>-->
<?php
endif; ?>

<header>
    <div class="">
        <div class="container" id="new_project_container">
            <form method="post" action="" id="new_project_form">
                <div class="row-flow">
                    <input type="hidden" name="action" value="create_project"/>
                    <input type="text" name="project_slug" value="" placeholder="Project slug. Example: myproject"/>
                    <input type="text" name="project_folder" value="" placeholder="Root project folder name"/>
                    <button type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>
</header>
<div class="container">

</div>
<table align=center border=0>
    <tr>
        <td valign="top">
            <div id="favorites"
                 style="position:absolute; left:50px; font-family: Verdana; font-size: 10px; padding:5px; background-color: efefef;"
                 nowrap>
                <?php
                $txt = '';
                foreach ($_COOKIE as $k => $v) {
                    if (substr($k, 0, 5) == "favs_") {
                        $txt .= '<input type="checkbox" name="' . substr(
                                $k,
                                5
                            ) . '" value="' . $v . '" onClick="setFavorites(this);" checked>' .
                            '<a href="?d=' . $v . '">' . substr($k, 5) . '</a><br>';
                    }
                }
                echo ($txt != "") ? $txt : "No favorites";
                ?>
            </div>
        </td>
        <td valign=top>
            <table border="0" align="center" cellpadding="2" cellspacing="2">
                <tr align="center" bgcolor="#0066CC">
                    <td height="18" bgcolor=#ffffff>&nbsp;</td>
                    <td bgcolor=#ffffff>&nbsp;</td>
                    <td><font color="#DCF0FE"><strong>Name</strong></font></td>
                    <?php
                    if ($view_mode != "simple") { ?>
                        <td><font color="#DCF0FE"><strong>Size</strong></font></td>
                        <td><font color="#DCF0FE"><strong>Zip</strong></font></td>
                        <?php
                    }
                    if ($fa_mode == "on") { ?>
                        <td><font color="#DCF0FE"><strong>base 64</strong></font></td>
                        <?php
                    } ?>
                </tr>
                <?php

                // ???
                function cmp($a, $b)
                {
                    $pi1 = pathinfo($a);
                    $ex1 = strtolower($pi1['extension']);
                    $pi2 = pathinfo($b);
                    $ex2 = strtolower($pi2['extension']);
                    if ($ex1 == $ex2) {
                        $a = $pi1['basename'];
                        $b = $pi2['basename'];
                        if ($a == $b) {
                            return 0;
                        }

                        return ($a < $b) ? -1 : 1;
                    }

                    return ($ex1 < $ex2) ? -1 : 1;
                }

                // ???
                //usort($fs, "cmp");
                sort($ds);

                // Shows level up link
                if (RELATIVE_PATH) {
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td align=center><a href=?d=$d><img src=index.php?iof=levelup border=0 alt='Level up!'></a>
                        </td>
                        <td class=filebg><a href="?d=<?= urlencode(dirname(RELATIVE_PATH)) ?>">...</a></td>
                        <td class=filebg align=right><font color=#555555>&nbsp;</td>
                        <td class=filebg>&nbsp;</td>
                    </tr>
                    <?php
                }

                // List all folders
                foreach ($ds as $f => $file) {
//								if ($_GET['d'] == "" || preg_match("!^_old-projects_/\d+$!i", $_GET['d']))
//									$checkbox = "<input type=\"checkbox\" name=\"" . $file . "\" value=\"" . str_replace($root_dir, "", str_replace("\\", "/", getcwd()) . "/" . urlencode($file)) . "\" onClick=\"setFavorites(this);\"" . (($_COOKIE["favs_" . $file] == str_replace($root_dir, "", str_replace("\\", "/", getcwd()) . "/" . urlencode($file))) ? " checked" : "") . ">";

                    ?>
                    <tr>
                        <td>$checkbox</td>
                        <td align=center width=0><img src=index.php?iof=folder></td>
                        <td class=filebg><a href="?d=<?= urlencode($file['relative_path']) ?>"><?= htmlspecialchars(
                                    $file['name']
                                ) ?></a></td>
                        <td class=filebg align=right><font color=#555555><?= format_size($file['full_path']) ?></td>

                        <?php
                        if ($view_mode != "simple") : // ???
                            ?>
                            <td class=filebg align=right>&nbsp;</td>
                        <?php
                        endif; ?>

                        <?php
                        /*
                                                    if (!preg_match("/^!|^\./", $file))
                                                           $x = "<a href=\"index.php?z=" . ((trim($_GET['d']) == '') ? "" : $_GET['d'] . "/") . "" . urlencode($file) . "\"><img src=index.php?iof=zip width=15 height=16 border=0 alt='Download ZIPped!'></a>";
                                                       else
                                                           $x = '&nbsp;';

                                                       if ($view_mode != "simple")
                                                           print "<td class=filebg>$x</td>";
                                                       if (!preg_match("/^!|^\./", $file))
                                                           $x = "<a href=\"index.php?z=" . urlencode($file) . "\"><img src=index.php?iof=save_16 width=16 height=16 border=0 alt='Download ZIPped!'></a>";
                                                       else
                                                           $x = '&nbsp;';
                       //                if ($view_mode != "simple") print "<td >$x</td>";
                       //               	if(!ereg("^!|^\.",$file)) $x = "<a href=\"index.php?z=".urlencode($file)."\"><img src=index.php?iof=print_16 width=16 height=16 border=0 alt='Download ZIPped!'></a>"; else $x='&nbsp;';
                       //                if ($view_mode != "simple") print "<td>$x</td>";
                       //               	if(!ereg("^!|^\.",$file)) $x = "<a href=\"index.php?z=".urlencode($file)."\"><img src=index.php?iof=db width=16 height=16 border=0 alt='Download ZIPped!'></a>"; else $x='&nbsp;';
                       //                if ($view_mode != "simple") print "<td>$x</td>";

                                                       if ($fa_mode == "on")
                                                           print "<td class=filebg>&nbsp;</td>";
                                                    */
                        ?>
                    </tr>
                    <?php
                }

                // List all files
                foreach ($fs as $f => $file) {
                    // ????
                    $ext = 'txt';
                    $pi = pathinfo($file['full_path']);
                    $ex = strtolower($pi['extension']);
                    if (in_array($ex, array('php', 'inc', 'lib'))) {
                        $ext = 'php';
                    }
                    if (in_array($ex, array('js', 'css'))) {
                        $ext = 'js';
                    }
                    if (in_array($ex, array('gif', 'jpg', 'jpeg', 'bmp', 'png', 'tiff'))) {
                        $ext = 'img';
                    }
                    if (in_array($ex, array('zip', 'rar', 'arj', 'gz', 'tar'))) {
                        $ext = 'zip';
                    }
                    if (in_array($ex, array('doc', 'rtf'))) {
                        $ext = 'doc';
                    }
                    if (in_array($ex, array('html', 'shtml', 'htm', 'phtml', 'mht'))) {
                        $ext = 'ie';
                    }

                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td align=center><img src="index.php?iof=$ext" border=0/></td>
                        <td class=filebg><a href="<?php
                            echo $file['relative_path']; ?>"><?= htmlspecialchars(
                                    $file['name']
                                ) ?></a></td>
                        <td class=filebg align=right><font color=#555555><?= format_size($file['full_path']) ?></td>
                        <?php
                        /*
                                                       if ($view_mode != "simple")
                                                           print "";
                                                       $fullname = str_replace($root_dir, "", str_replace("\\", "/", getcwd())) . "/" . urlencode($file);
                                                       if (1)
                                                           $x = "<a href=\"index.php?v=" . $fullname . "\" target=\"_blank\"><img src=index.php?iof=view width=16 height=16 border=0 alt='View the file!'></a>";
                                                       else
                                                           $x = '&nbsp;';
                                                       if ($view_mode != "simple")
                                                           print "<td class=filebg>$x</td>";
                                                       if ($fa_mode == "on")
                                                           print "<td class=filebg>[<a href=\"?d=" . $_GET['d'] . "&File=" . urlencode($file) . "\">convert</a>]</td>";
                                                       */
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </td>
        <td width=40 valign=top></td>
        <td width=60 valign=top><?php
            // HERE IS THE RIGHT COLUMN WITH !Old catelogue listing
            ?>
            <table width="100%" border="0" align="center" cellpadding="2" cellspacing="2">
                <tr align="center" bgcolor="#0066CC">
                    <!--td bgcolor=#ffffff>&nbsp;</td-->
                    <td height="22"><font color="#DCF0FE"><strong>Old</strong></font></td>
                </tr>
                <?php
                //	$d = dir($root_dir . '/__old_projects');
                //	$ds = array();
                //	if ($d) {
                //		while (false !== ($entry = $d->read()))
                //			if ($entry != "." && $entry != "..") {
                //				if (is_dir("_old-projects_/" . $entry))
                //					$ds[] = $entry;
                //			}
                //		$d->close();
                //		arsort($ds);
                //	}

                //	foreach ($ds as $f => $file) {
                //		print "<tr>";
                //		//print "<td align=center><img src=index.php?iof=folder></td>\n";
                //		print "<td class=filebg align=center height=20><a onmouseover=\"showBox('" . urlencode($file) . "');\" href=\"?d=_old-projects_/" . urlencode($file) . "\">$file</a></td>\n";
                //		print "</tr>\n";
                //	}
                ?>
            </table>
        </td>
        <td valign="top">
            <div id="box"
                 style="font-family: Verdana; font-size: 10px; position:absolute; border: dotted 1px; padding:10px; background-color: #efefef;"
                 nowrap>
                <?php
                //			$d = dir($root_dir . '/__old_projects/' . $ds[sizeof($ds) - 1]);
                echo "<h3 style='margin-bottom:3px;'>" . $ds[sizeof($ds) - 1] . "</h3>";
                //			while (false !== ($file = $d->read())) {
                //				if ($file != "." && $file != "..")
                //					echo "<a href=\"?d=_old-projects_/" . urlencode($ajax_cat) . "/" . $file . "\">" . $file . "</a><br>";
                //			}
                ?>
            </div>
        </td>
    </tr>
</table>
<?php

/*
else {
	?>
			<style type="text/css">
				td { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; }
				a {        text-decoration: none; }
				a:hover { text-decoration: underline; }
			</style>
		<center>
			<form name="form1" method="post" action="index.php">
				<input name="pass" type="password" id="pass">
				<input type="submit" name="Submit" value="Submit">
			</form>
	<?php
}
*/

if ($view_mode != "simple") {
    ?>
    <br>
    <center>
        <small> <span style='font-size:10px;font-family:Verdana'>God mode: [<a
                        href="?d=<?= $_GET['d'] ?>&GM=<?= $link_mode ?>">
	<?= $fa_mode ?>
						</a>] </span> </small>
        <br>
        <small> <span style='font-size:10px;font-family:Verdana'>Public IP: <a
                        href="http://83.218.201.35">83.218.201.35</a> </span> </small><br>
    </center>
    <?php
    if ($fa_mode == "on") {
        ?>
        <center>
            <form action="" method="post" name=form1>
						<textarea name=text style="font-family: Verdana; font-size: 9px; width: 350px; height: 100px;"><?php
                            if ($_POST['text']) {
                                if ($_POST['M'] == "E") {
                                    print base64_encode(stripslashes($_POST['text']));
                                }
                                if ($_POST['M'] == "D") {
                                    print base64_decode(stripslashes($_POST['text']));
                                }
                            } else {
                                print base64_encode(join("", file($_GET['File'])));
                            }
                            ?>
						</textarea>
                <br>
                <input type=submit value="Encode base 64" style="font-family: Verdana; font-size: 11px; width: 120px;"
                       onClick="document.form1.M.value = 'E';">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type=submit value="Decode base 64" style="font-family: Verdana; font-size: 11px; width: 120px;"
                       onClick="document.form1.M.value = 'D';">
                &nbsp;
                <input type=hidden name=M value="E">
            </form>
        </center>
        <center>
            <form method=get action="">
                <input type=text value="<?= gethostbyname($_GET['host']) ?>" name=host>
            </form>
        </center>
        <?php
    }
}
?>

<script defer src="<?php
echo ASSETS_URL; ?>/assets/js/jquery-3.6.0.min.js"></script>
<script defer src="<?php
echo ASSETS_URL; ?>/assets/js/main.js"></script>

</body>
</html>