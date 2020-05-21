<?php

header("Content-Type: text/html; charset=utf-8");

function display_filesize($filesize) {
	if (is_numeric($filesize)) {
		$decr = 128;
		$step = 0;
		$prefix = array('Byte','KB','MB','GB','TB','PB');

		while(($filesize / $decr) > 0.9){
			$filesize = $filesize / $decr;
			$step++;
		}

		return round($filesize,2).' '.$prefix[$step];
	} else {
		return 'NaN';
	}
}

// configuration options
$overwrite = true;  // allow overwriting with the same name
$img_ext = array('.jpg','.gif','.png', '.jpeg');
$forbidden_filenames = array('index.html', 'index.htm', 'index.php');

$max_filesize = 1342177280;

$upload_path = 'temp/';    // include trailing slash
$nr_files = 5;

// get the filename of this upload script
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1];

$POST_MAX_SIZE = ini_get('post_max_size');
$mul = substr($POST_MAX_SIZE, -1);
$mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
$max_post_size = $mul*(int)$POST_MAX_SIZE;

$UPLOAD_MAX_SIZE = ini_get('upload_max_filesize');
$mul = substr($UPLOAD_MAX_SIZE, -1);
$mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
$max_upload_size = $mul*(int)$UPLOAD_MAX_SIZE;

if ($max_post_size < $max_filesize) $max_filesize = $max_post_size;
if ($max_upload_size < $max_filesize) $max_filesize = $max_upload_size;

if (!file_exists($upload_path))
	die('Configuration error: this script cannot find the upload path <code>' . $upload_path . '</code>');
elseif (!is_writable($upload_path))
	die('Configuration error: this script does not have write access to <code>' . $upload_path . '</code> (chmod it).');
else {
	// print the upload form
?>

<form action="<?php echo $pfile; ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<h2>http file uploader</h2>
<p>&nbsp;</p>
<?php
for ($i = 1; $i <= $nr_files; ++$i) {
	echo '<p><label for="file'. $i . '">Select file ' . $i . ':</label> <input type="file" name="file'.$i.'" id="file'.$i.'" style="width:40%"></p>';
}
?>
</p>
<p>&nbsp;</p>
<input type="submit" style="margin-left: 15%; width:20%; height:60px" value="UPLOAD" />
<p>&nbsp;</p>
<p>File size limited to <?php echo display_filesize($max_filesize); ?>.</p>
<?php
if (file_exists($upload_path . "index.html") || file_exists($upload_path . "index.htm") || file_exists($upload_path . "index.php")) {
	echo '<p>Uploads are not indexed. Do not lose the URL!</p>';
}
else {
	echo '<p>Uploads can be found at <a href="' . $upload_path . '">' . $upload_path . '</a></p>';
}
?>
</form>

<?php
} // end upload form

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	for ($i = 1; $i <= $nr_files; ++$i) {
		$filename = $_FILES['file'.$i]['name'];
		$filename = stripslashes($filename);
		if ($filename == "") continue;

		// check for use of reserved filenames
		if (in_array($filename, $forbidden_filenames)) {
			echo '<font color=red>Illegal filename: <font face="Courier New, fixed">' . $filename . '</font></font>';
			continue;
		}

		// check if file exists
		if (!$overwrite && file_exists($upload_path . $filename)) {
			echo '<font color=red>File exists: <font face="Courier New, fixed">' . $filename . '</font>. Please use a different name.</font>';
			continue;
		}

		$ext = substr($filename, strpos($filename,'.'), strlen($filename)-1);

		echo '<hr>';

		// Check if the filetype is allowed, if not DIE and inform the user.
		//if (!in_array($ext, $allowed_filetypes))
		//   die('The file type you attempted to upload is not allowed.');

		if (filesize($_FILES['file'.$i]['tmp_name']) > $max_filesize)
			echo '<p><font color=red>File '.$i.' is too large (' . display_filesize(filesize($_FILES['file'.$i]['tmp_name'])) . ').</font> The maximum filesize is '.display_filesize($max_filesize).' bytes.</p>';
		else {
			if (move_uploaded_file($_FILES['file'.$i]['tmp_name'], $upload_path . $filename)) {
				echo '<p><font color=green>File '.$i.' upload was successful</font></p>';
				echo '<p>The URL is: <a href="' . $upload_path . rawurlencode($filename) . '">' . $upload_path . rawurlencode($filename) . '</p>';
				if (in_array($ext, $img_ext)) echo '<img src="' . $upload_path . urlencode($filename) . '" width=200 height=200 border=0>';
				echo '</a>';
			}
			else {
				echo '<font color=red>There was an error during the upload of file ' . $i . '.  Please try again.</font>';
			}
		}
	}
}

?>
