<?php

// TODO: modernize session
session_start();

// TODO:
// 1. Show some apache & php server variables. Like: memory limit, <?php tag, etc.
// 2. d variable security: filter ../../ requests
// 3. Wrong folder error message

/* ???
  if($ajax_cat = $_GET['ajax_cat']) {
  echo "<h3>".$ajax_cat."</h3>";
  $d = dir("_old-projects_/".$ajax_cat);
  while (false !== ($file = $d->read())) {
  if ($file!="." && $file!="..")
  echo "<a href=\"?d=_old-projects_/".urlencode($ajax_cat)."/".$file."\">".$file."</a><br>";
  }
  exit;
  }
 */

//if (!eregi("\.\./$", $_GET['d']) && !eregi("\.\.$", $_GET['d']) && !eregi("^[a-z]:", $_GET['d']))
//	chdir(urldecode($_GET['d']));

$action = $_POST['action'] ?? ($_GET['action'] ?? null);

$dir = $_POST['d'] ?? ($_GET['d'] ?? null);

// Define current path.
if ($dir && is_dir(HOME_PATH . '/' . $dir)) {
    define('CURRENT_PATH', rtrim(HOME_PATH . '/' . $dir, '/'));
} else {
    define('CURRENT_PATH', HOME_PATH);
}

// Define relative path.
if (CURRENT_PATH === HOME_PATH) {
    define('RELATIVE_PATH', null);
} else {
    define('RELATIVE_PATH', $dir);
}

// Change directory.
chdir(CURRENT_PATH);

// ???
$password = "";
//echo("Time: ".time()."<br>");
// ???
//if ( $_POST['pass'] == $password ) {
//    $_SESSION['sess'] = 1;
//}

// Get IP subnet.
$a = explode(".", $_SERVER['REMOTE_ADDR']);
$user_ip = (int)($a[0] . $a[1] . $a[2]);

// TODO: Add ban by IP

// Get list of files and folders in root directory
$ds = $fs = array();
$d = dir(CURRENT_PATH);
while (false !== ($entry = $d->read())) {
    if ($entry !== '.' && $entry !== '..') {
        // Add file to files array.
        if (is_file($entry)) {
            $fs[$entry] = array(
                'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                'full_path' => CURRENT_PATH . '/' . $entry,
                'name' => $entry,
            );
        }
        // Add folder to folders array.
        if (is_dir($entry) && $entry !== '_old-projects_') {
            $ds[$entry] = array(
                'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                'full_path' => CURRENT_PATH . '/' . $entry,
                'name' => $entry,
            );
        }
    }
}
$d->close();

// Sort files and folders in ASC order
asort($fs);
asort($ds);

// Set options.
$view_mode = $_POST['m'] ?? ($_GET['m'] ?? 'simple');
// Full access mode.
$fa = $_POST['fa'] ?? ($_GET['fa'] ?? null);
if ($fa === 'on') {
    $fa_mode = true;
} else {
    $fa_mode = false;
}
// ??? Link type.
if ($fa_mode) {
    $link_mode = false;
} else {
    $link_mode = true;
}

// If page opened on local server NOT from internet
if ($user_ip === 1921681 || $user_ip === 12700 || $_SESSION['sess'] === 1) {
    switch ($action) {
        case 'create_project':
            create_project();
            break;
        default:
            break;
    }
    /* ???
    if ($d = $_GET['z']) {
        chdir(".");
        $data = date("Ymd_His");
        $path = $d;
        if (eregi("\/", $d))
            $d = eregi_replace(".*[\/]([a-zA-Z0-9_-]+)$", "\\1", $d);
        $fname = "d:/fafi/tmp_" . $d . ".zip";
        $f_list = array();

        if ($handle = opendir($root_dir . $path . "/")) {
            while (false !== ($file = readdir($handle))) {
                if ($file == "passwords.php") {
                    ## Clean passwords.php for zip file
                    copy($root_dir . $path . "/passwords.php", $root_dir . $path . "/tmp_passwords.php");
                    $filename = $root_dir . $path . "/passwords.php";
                    $contents = join(file($filename));
                    $contents = eregi_replace("\'[^;]+\'", "''", $contents);
                    $f = fopen($filename, 'w+');
                    fwrite($f, $contents);
                    fclose($f);
                    ## Creates dump
                    require($root_dir . $path . "/tmp_passwords.php");
                    if ($database_host && $database_user) {
                        mysql_connect($database_host, $database_user, $database_pass);
                        mysql_select_db($database_name);
                    }
                    $r = mysql_query("SHOW TABLES");
                    while ($rr = mysql_fetch_row($r)) {
                        $sql_dump .= sqldumptable($rr[0], ($rr[0] == 'options') ? 1 : 0);
                    }
                    ## Clean dump data from table `options`
                    //$sql_dump = eregi_replace("(INSERT INTO `options` VALUES\('[0-9]+',')[^;]*(',')[^;@]*('\);)","\\1\\2\\3",$sql_dump);
                    $f = fopen($root_dir . $path . "/db_dump.sql", 'w+');
                    fwrite($f, $sql_dump);
                    fclose($f);

                    //$f_list[] = $root_dir.$path."/db_dump.sql";
                    $f_list[] = "db_dump.sql";
                }

                if ($file !== "." && $file !== ".." && $file != "docs" && $file != $d . ".zip" && $file != "tmp_passwords.php" && $file != "db_dump.sql")
                    $f_list[] = $file; //$f_list[] = $root_dir.$path."/".$file;
            }
            closedir($handle);
        }

        $fname = "d:/fafi/tmp_" . $d . ".zip";
        $archive = new PclZip($fname);
        chdir($root_dir . $path . "/");
        $archive->create($f_list);
        chdir(".");
        copy($fname, $root_dir . $path . "/" . $d . ".zip");
        unlink($fname);
        copy($root_dir . $path . "/tmp_passwords.php", $root_dir . $path . "/passwords.php");
        unlink($root_dir . $path . "/tmp_passwords.php");
        unlink($root_dir . $path . "/db_dump.sql");
        ?>
        <form name="formtocopy" action="">
            <textarea name="texttocopy"><?= "http://83.218.201.35/" . $path . "/" . $d . ".zip"; ?></textarea>
            <br>
        </form>
        <script language="JavaScript">

            function copy(inElement) {
                if (inElement.createTextRange) {
                    var range = inElement.createTextRange();
                    if (range)
                        range.execCommand('Copy');
                } else {
                    var flashcopier = 'flashcopier';
                    if (!document.getElementById(flashcopier)) {
                        var divholder = document.createElement('div');
                        divholder.id = flashcopier;
                        document.body.appendChild(divholder);
                    }
                    document.getElementById(flashcopier).innerHTML = '';
                    var divinfo = '<embed src="__stuff/_clipboard.swf" FlashVars="clipboard=' + escape(inElement.value) + '" width="0" height="0" type="application/x-shockwave-flash"></embed>';
                    document.getElementById(flashcopier).innerHTML = divinfo;
                }
            }
            copy(document.formtocopy.texttocopy);
            document.formtocopy.texttocopy.style.display = "none";
            alert("There was created zip file and file URL was copied to clipboard.");
            location.href =<?php echo"'";
        echo$_SERVER['HTTP_REFERER'];
        echo"'"; ?>;
        </script>
        <?php
    //                chdir(".");
    //                $data = date("Ymd_His");
    //                $fname = "d:/fafi/__common/".$d.".zip";
    //                $archive = new PclZip($fname);
    //                $archive->create(array($d), '', '');
    //                $fp = tmpfile();
    //                fclose($fp);
    //                header("Content-type: application/x-gzip");
    //                header("Content-Disposition: filename=".$d.".zip");
    //                header("Content-Transfer-Encoding: binary");
    //                header("Content-Length: ".filesize($fname));
    //                readfile($fname);
    //                unlink($fname);
        die();
    }*/
    /* ???
    if ($v = $_GET['v']) {
        //echo $v; die;
        $fp = fopen($v, 'r');
        $a = fread($fp, filesize($v));
        fclose($fp);
        $pi = pathinfo($v);
        $ext = strtolower($pi['extension']);
        if (strtolower($ext) == 'php') {
            echo "<nobr>";
            highlight_string($a);
            die;
        }
        echo htmlentities($a);
        die();
    }
    */
    require ABSPATH . '/template/index.php';
}

function create_project()
{
    create_project_folders();
    create_vhost();
}

function create_project_folders()
{
    global $app_config;

    $project_slug = $_POST['project_slug']; // @TODO: filter
    $project_folder_name = str_replace('%project-slug%', $project_slug, $app_config['project_folder_name']);
    $project_path = ABSPATH . '/' . trim($app_config['home_path'], '/') . '/' . $project_folder_name;

    create_folder($project_path);
    create_folder($project_path . '/docs');
    create_folder($project_path . '/' . $project_slug);
}

function create_folder($path, $message = true)
{
    if (file_exists($path)) {
        return;
    }
    if (!mkdir($path, 0770, false) && !is_dir($path)) {
        throw new \RuntimeException(sprintf('Directory "%s" was NOT created', $path));
    }
    echo sprintf('Directory "%s" was created', $path) . "\n\r";
}

function create_vhost()
{
    echo 'vhost';
    die;
}

function sqldumptable($table, $sql_d)
{
    $tabledump = "DROP TABLE IF EXISTS `$table`;\n";
    $tabledump .= "CREATE TABLE `$table` (\n";
    $firstfield = 1;
    $champs = mysql_query("SHOW FIELDS FROM `$table`");
    while ($champ = mysql_fetch_array($champs)) {
        if (!$firstfield) {
            $tabledump .= ",\n";
        } else {
            $firstfield = 0;
        }
        $tabledump .= "   `$champ[Field]` $champ[Type]";
        if ($champ['Null'] != "YES") {
            $tabledump .= " NOT NULL";
        }
        if (!empty($champ['Default'])) {
            $tabledump .= " default '$champ[Default]'";
        }
        if ($champ['Extra'] != "") {
            $tabledump .= " $champ[Extra]";
        }
    }
    @mysql_free_result($champs);
    $keys = mysql_query("SHOW KEYS FROM `$table`");
    while ($key = mysql_fetch_array($keys)) {
        $kname = $key['Key_name'];
        if ($kname != "PRIMARY" and $key['Non_unique'] == 0) {
            $kname = "UNIQUE|`$kname`";
        }
        if (!is_array($index[$kname])) {
            $index[$kname] = array();
        }
        $index[$kname][] = $key['Column_name'];
    }
    @mysql_free_result($keys);
    while (list($kname, $columns) = @each($index)) {
        $tabledump .= ",\n";
        $colnames = implode($columns, ",");
        if ($kname == "PRIMARY") {
            $tabledump .= "   PRIMARY KEY (`" . eregi_replace(',', '`,`', $colnames) . "`)";
        } else {
            if (substr($kname, 0, 6) == "UNIQUE") {
                $kname = substr($kname, 7);
            }
            $tabledump .= "   KEY $kname (`" . eregi_replace(',', '`,`', $colnames) . "`)";
        }
    }
    $tabledump .= "\n);\n\n";

    if ($sql_d == 1) {
        $rows = mysql_query("SELECT * FROM `$table`");
        $numfields = mysql_num_fields($rows);
        while ($row = mysql_fetch_array($rows)) {
            $tabledump .= "INSERT INTO `$table` VALUES(";
            $cptchamp = -1;
            $firstfield = 1;
            while (++$cptchamp < $numfields) {
                if (!$firstfield) {
                    $tabledump .= ",";
                } else {
                    $firstfield = 0;
                }
                if (!isset($row[$cptchamp])) {
                    $tabledump .= "NULL";
                } else {
                    $tabledump .= "'" . mysql_escape_string($row[$cptchamp]) . "'";
                }
            }
            $tabledump .= ");\n";
        }
        @mysql_free_result($rows);
    }

    //$fff = fopen("dump.sql","w+"); fwrite($fff,$tabledump);

    return $tabledump;
}

// Returns folder/file size
function get_size($name)
{
    // If it's a file then return it's size
    if (is_file($name)) {
        return filesize($name);
    } // If it's a folder then we should calculate files & folders in it
    elseif (is_dir($name)) {
        return 0;
        $handle = opendir($name);
        while (false !== ($file = readdir($handle))) {
//			if( is_dir($name . '/' . $file) && $file != '..' && $file != '.' ) $size_sum += get_size($name . '/' . $file);
//			else
            if (is_file($name . '/' . $file)) {
                $size_sum += filesize($name . '/' . $file);
            }
        }
        closedir($handle);

        return $size_sum;
    }

    return 0;
}

// Get folder/file size and returns formated string
function format_size($name)
{
    $size = get_size($name);
    if ($size) {
        if ($size < 1024) {
            return $size .= " b";
        } elseif ($size >= 1024 && $size < 1024 * 1024) {
            return $size = number_format($size / 1024, 2) . " Kb";
        } elseif ($size >= 1024 * 1024 && $size < 1024 * 1024 * 1024) {
            return $size = number_format($size / (1024 * 1024), 2) . " Mb";
        } else {
            return $size = number_format($size / (1024 * 1024 * 1024), 2) . " Gb";
        }
    } else {
        return "";
    }
}
