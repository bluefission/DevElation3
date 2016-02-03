<?php
//dev_file_system.php
//created: 2/07/05
//last modified: 11/17/05
///////
//contains file management and image transfer functions

//uploads an image (or any file really) to a server from a form 'file' element
//$document represents the array object of the file to be uploaded as retrieved from the $_FILES global variable
//$overwrite is a flag that takes 1 or 0 (true or false) and determines whether or not files by the same name
//   get over written if uploaded
//$dir is a variable that is the full path you want to upload your file to
//$type is a string that allows you to filter upload file types. image, documenet, file, or custom
//$name is any file name you want to use to replace the name of the file that was uploaded
function dev_upload_file($document, $overwrite = 0, $dir = '',  $type = 'file', $name = '') {
	$status = '';
	
	$pattern = '';
	if (is_array($type)) {
		$pattern = "/\\" . implode('$|\\', $type) . "$/i";
		$type = 'custom';
	}  
	
	if ($document['name'] != '') {
		switch ($type) {
		case 'image':
			$extensions = "/\.gif$|\.jpeg$|\.tif$|\.jpg$|\.tif$|\.png$|\.bmp$/i";
		  	break;
	  	case 'document':
	  		$extensions = "/\.pdf$|\.doc$|\.txt$/i";
	  		break;
	  	default:
	  	case 'file':
	  		$extensions = '//';
	  		break;
		case 'custom':
			$extensions = $pattern;
			break;
		}
		if (preg_match($extensions, $document['name'])) {
			$location = $dir . (($name == '') ? basename($document['name']) : $name);
			if ($document['size'] > 1) {
				if (is_uploaded_file($document['tmp_name'])) {
					if (!file_exists( $location ) || $overwrite) {
						if (move_uploaded_file( $document['tmp_name'], $location )) {
							$status = 'Upload Completed Successfully' . "\n";
						} else {
							$status = 'Transfer aborted for file ' . basename($document['name']) . '. Could not copy file' . "\n";
						}
					} else {
						$status = 'Transfer aborted for file ' . basename($document['name']) . '. Cannot be overwritten' . "\n"; 
					}
				} else {
					$status = 'Transfer aborted for file ' . basename($document['name']) . '. Not a valid file' . "\n";
				}
			} else {
				$status = 'Upload of file ' . basename($document['name']) . ' Unsuccessful' . "\n";
			}
		} else {
			$status = 'File "' . basename($document['name']) . '" is not an appropriate file type. Expecting '.$type.'. Upload failed.';
		}
	}
	return $status;
}

//opens up a directory box to open files
//$name can be 'choose_page' or 'choose_template' 
//$varname is the name of the variable that will be passed via http through POST or GET
//$type is used to determine the type of document being open and which types are filtered out
function dev_view_dir($varname = 'file', $type = '', $dir = '') {
	$output = '';
	
	if ($dir == '') {
		$dir = getcwd();
	}
	
	$pattern = '';
	if (is_array($type)) {
		$pattern = "/\\" . implode('$|\\', $type) . "$/i";
		$type = 'custom';
	}
	
	switch ($type) { //choose what type of documents we will be opening
	case 'template':
		$extensions = "/\.emc$/";
		break;
	case 'image':
		$extensions = "/\.gif$|\.jpeg$|\.tif$|\.jpg$|\.tif$|\.png$|\.bmp$/i";
	  	break;
  	case 'document':
  		$extensions = "/\.pdf$|\.doc$|\.txt$/i";
  		break;
	default:
	case 'file':
		$extensions = '//';
		break;
	case 'web':
		$extensions = "/\..$|\.htm$|\.html$|\.pl$|\.txt$/i";
		break;
	case 'custom':
		$extensions = $pattern;
		break;
	}


	//'scandir()' function for PHP 5 (not compatible with PHP4)
	//$files = scandir($dir);
	
	//PHP 4 Alternative to 'scandir()'
	$dh  = opendir($dir);
	$files = array();
	while (($filename = readdir($dh)) !== false) {
		if(preg_match($extensions, $filename)) $files[$filename] = $dir . $filename;
	}
	//sort($files);
 
	$output .= dev_draw_form_field('select', $varname, 'File System', $files, '', '', '', 'size="10"');	

	closedir($dh);
	
	return $output;
}

//opens up a content table to open files
//$name can be 'choose_page' or 'choose_template' 
//$varname is the name of the variable that will be passed via http through POST or GET
//$type is used to determine the type of document being open and which types are filtered out
function dev_list_dir($href = '', $type = '', $dir = '', $query_r = '', $show_table = true) {
	$output = '';
	
	$href = dev_href($href, false);
	
	if ($dir == '') {
		$dir = getcwd() . '/';
	}
	
	$pattern = '';
	if (is_array($type)) {
		$pattern = "/\\" . implode('$|\\', $type) . "$/i";
		$type = 'custom';
	}
	
	switch ($type) { //choose what type of documents we will be opening
	case 'template':
		$extensions = "/\.emc$/";
		break;
	case 'image':
		$extensions = "/\.gif$|\.jpeg$|\.tif$|\.jpg$|\.tif$|\.png$|\.bmp$/i";
	  	break;
  	case 'document':
  		$extensions = "/\.pdf$|\.doc$|\.txt$/i";
  		break;
	default:
	case 'file':
		$extensions = '//';
		break;
	case 'web':
		$extensions = "/\..$|\.htm$|\.html$|\.pl$|\.txt$/i";
		break;
	case 'custom':
		$extensions = $pattern;
		break;
	}


	//'scandir()' function for PHP 5 (not compatible with PHP4)
	//$files = scandir($dir);
	
	//PHP 4 Alternative to 'scandir()'
	$dh  = opendir($dir);
	$files = array();
	while (($filename = readdir($dh)) !== false) {
		if(preg_match($extensions, $filename)) {
			$filesize = filesize($dir . $filename);
			$files[] = array(
				'f' => $filename,
				'filename' => $filename,
				'open' => '<a href="?dir='.$dir.'&file='.$filename.'&action=open">open</a>',
				'edit' => '<a href="?dir='.$dir.'&file='.$filename.'&action=edit">edit</a>',
				'delete' => '<a href="?dir='.$dir.'&file='.$filename.'&action=delete">delete</a>',
				'filesize' => $filesize
				);
				//'open' => dev_link_file($filename, $dir)
		}
	}
	sort($files);
 
	if ($show_table) $output .= dev_content_box($files, '', $href, $query_r, '#c0c0c0', '', 1, 1, $dir, $dir);
	else $output = $files;

	closedir($dh);
	
	return $output;
}

function dev_view_file($file = '') {
	$output = '';
	if ($file != '' && file_exists($file)) {
		$doc = file_get_contents($file);
		$output = "$doc";
	} else {
		$output .= "No such file. File does not exist\n";
	}
	
	return $output;
}

function dev_stream_file($file = '', &$msg) {
	$status = false;
	//if ($file != '' && file_exists($file)) {
	if ($file != '') {
		if (!$stream = fopen($file, 'r')) {
			$msg = "Error: File cannot be opened\n";
			$status = false;
		} else {
			$msg = "File opened successfully\n";
			$status = $stream;
		}
	} else {
		$msg .= "No such file. File does not exist\n";
		$status = false;
	}
	
	return $status;
}

function dev_edit_file($file = '', $mode = '') {
	if ($file != '' && file_exists($file)) {
		$doc = file_get_contents($file);
		if ($mode == '') $mode = 'textarea';
		$output .= dev_draw_form_field('hidden', 'file', '', $file);
		$output .= dev_draw_form_field($mode, 'data', 'Editing "' . basename($file) . '"', $doc, '', 'data');
		$output .= dev_draw_form_field('submit', 'Submit', 'Save Changes', 'Save');
	} else {
		$output .= "No such file. File does not exist\n";
	}
	
	return $output;
}

function dev_save_file($file, $data, $mode = 'w') {
	$status = false;
	if ($file != '') {
		if (!file_exists($file)) $status .= "File '$file' does not exist. Creating.\n";
		if (is_writable($file)) {
			if (!$handle = fopen($file, $mode)) {
				$status .= "Cannot open file ($file)\n";
				exit;
			}
			
			if (fwrite($handle, stripslashes($data)) === false) {
				$status .= "Cannot write to file ($file)\n";
				//exit;
			} else {	
				$status .= "Successfully wrote to file '$file'\n";
				fclose($handle);
			}
		
		} else {
			$status .= "The file '$file' is not writable\n";
		}
	} else {
		$status .= "No file specified for edit\n";
	}
	
	return $status;
}

function dev_copy_file($file = '', $dest = '', $overwrite = false, $remove_orig = false) {
	$status = false;
	if ($file != '') {
		if ($dest != '' && is_dir($dest)) {
			if (file_exists($file)) {
				if (!file_exists( $dest ) || $overwrite) {
					//copy process here
					if ($success) {
						$status .= "Successfully copied file\n";
						if ($remove_orig) {
							$status .= dev_delete_file($file, $remove_orig);
						}
					} else {
						$status .= "Copy failed: file could not be moved\n";
					}
				} else {
					$status .= "Copy aborted. File cannot be overwritten\n";
				}
			} else {
				$status .= "File '$file' does not exist\n";
			}
		} else {
			$status .= "No file destination specified or destination does not exist\n";
		}
	} else {
		$status .= "No file specified for deletion\n";
	}
	
	return $status;
	
}

function dev_delete_file($file, $confirm = false) {
	$status = false;
	if ($file != '') {
		if ($confirm === true) {
			if (file_exists($file)) {
				if (is_writable($file)) {
					if (unlink($file) === false) {
						$status .= "Cannot delete file ($file)\n";
					} else {
						$status .= "Successfully deleted file '$file'\n";
					}	
				} else {
					$status .= "The file '$file' is not editable\n";
				}
			} else {
				$status .= "File '$file' does not exist\n";
			}
		} else {
			$status .= "Must confirm action before file deletion\n";		
		}
	} else {
		$status .= "No file specified for deletion\n";
	}
	
	return $status;
}

function dev_list_zip_dir() {

}

function dev_pack_file($file = '', $dest = '') {
	$status = false;
	return $status;
}

function dev_unpack_file($file = '', $dest = '') {
	$status = false;
	return $status;
}


?>
