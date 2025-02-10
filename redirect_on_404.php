<?php
/**
 * Redirect on 404
 *
 * Redirects the browser to the correct image or album if a matching item is found in the database
 *
 * @author Marcus Wong (wongm)
 * @package plugins
 */

$plugin_description = gettext("Redirects the browser to the correct image or album if a matching item is found in the database. Requires all gallery items to have unique album folder names and unique image filenames.");
$plugin_author = "Marcus Wong (wongm)";
$plugin_version = '1.0.0'; 
$plugin_URL = "https://github.com/wongm/zenphoto-404-helpers/";

function redirectOn404() {	
	redirectToAlbumImageOn404();
	redirectToCachedImageOn404();
	redirectToImagePageOn404();
	redirectToAlbumOn404();
}

/*
 * trying to access album image but it doesn't exist
 * eg: /album/buses/E100_48230.jpg
 * search other albums for this image
 */
function redirectToAlbumImageOn404() {
	// load global variables from Zenphoto's index.php
	global $_GET, $_zp_db;
	
	if (strtolower(substr($_GET['album'], 0, 6)) == 'albums') {
		
		$requestBits = explode('/', $_GET['album']);
		// make sure we are accessing an image
		if (sizeof($requestBits) >= 3) {
			$imageFilename = $requestBits[sizeof($requestBits)-1];
			// query the DB to find any images with the EXACT same filename as requested
			$searchSql = "SELECT folder, filename FROM " . $_zp_db->prefix('images') . " i INNER JOIN " . 
					$_zp_db->prefix('albums') . " a ON i.albumid = a.id WHERE i.filename = " . 
					$_zp_db->quote($imageFilename) . "";

			$searchResult = $_zp_db->queryFullArray($searchSql);
			
			// if single result is returned, then we have a match!
			if (sizeof($searchResult) == 1) {

				// fix for some "query_full_array()" differences (on some Zenphoto versions?)
				if (sizeof($searchResult[0]) > 1) {
					$searchResult = $searchResult[0];
				}
				
				// build up the URL to redirect to
				$location = "/albums/" . $searchResult["folder"] . "/" . $searchResult["filename"];
				// redirect the browser
				status_header(301);
				header("Location: $location");
				die();
			}
		}
	}
}

/*
 * trying to access cached image but it doesn't exist
 * eg: /cache/buses/E100_4823_500.jpg
 * suffering from an empty cache file?
 * so redirect to i.php to regenerate it
 */
function redirectToCachedImageOn404() {
	// load global variables from Zenphoto's index.php
	global $_GET;
	
	if (strtolower(substr($_GET['album'], 0, 5)) == 'cache') {
		
		// convert Zenphoto params into something useful!
		$filenameToCheck = str_replace(dirname($_GET['album']), "", basename($_GET['album']));
		
		// check for '_thumb' at the end of the URL - take note, then remove
		$isThumb = (strtolower(substr($filenameToCheck, (strlen(stripSuffix($filenameToCheck)) - 6), 6)) == '_thumb');
		
		// clean up album URL
		$_GET['album'] = str_replace("cache/", "", dirname($_GET['album']));
		
		// try to find the image size
		$imagesize = null;
		$imageparams = explode("_", $filenameToCheck);
		
		// separate the image size from file extension
		$imageparams = explode(".", $imageparams[sizeof($imageparams) - 1]);
		if (array_key_exists('image', $_GET) && in_array(getSuffix($_GET['image']), array('jpg','jpeg','gif','png'))) {
			$imagesize = $imageparams[0];
			// cleanup filename to get base image
			$filenameToCheck = str_replace("_" . $imagesize, "", $filenameToCheck);
		}
		
		// we have an image size
		if ((is_numeric($imagesize) && $imagesize > 0) || $isThumb) {
			if ($isThumb) {
				$imagesize = 'thumb';
			}
				
			if (strlen($_GET['album']) > 0 && strlen($filenameToCheck) > 0) {
				$location = sprintf(FULLWEBPATH . "/zp-core/i.php?a=%s&i=%s&s=%s", $_GET['album'], $filenameToCheck, $imagesize);
				status_header(302);
				header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
				header("Pragma: no-cache"); //HTTP 1.0
				header("Location: $location");
				die();
			}
		}
	}
}

function redirectToImagePageOn404() {
	// load global variables from Zenphoto's index.php
	global $_GET, $_zp_db;

	// make sure we are accessing an image
	if (array_key_exists('image', $_GET)) {
		if ($_GET['image'] != '') {
			// query the DB to find any images with the EXACT same filename as requested
			$searchSql = "SELECT folder, filename FROM " . $_zp_db->prefix('images') . " i INNER JOIN " . 
					$_zp_db->prefix('albums') . " a ON i.albumid = a.id WHERE i.filename = " . 
					$_zp_db->quote($_GET['image']) . "";
			
			$searchResult = $_zp_db->queryFullArray($searchSql);
			
			// if single result is returned, then we have a match!
			if (sizeof($searchResult) == 1) {
				// fix for some "query_full_array()" differences (on some Zenphoto versions?)
				if (sizeof($searchResult[0]) > 1) {
					$searchResult = $searchResult[0];
				}
				
				// build up the URL to redirect to
				$location = rewrite_path("/" . $searchResult["folder"] . "/" . $searchResult["filename"] . IM_SUFFIX, "/index.php?album=" . $searchResult["folder"] . "&image=" . $searchResult["filename"]);
				
				// redirect the browser
				status_header(301);
				header("Location: $location");
				die();
			}
		}
	}
}

function redirectToAlbumOn404()
{
	global $_GET, $_zp_db;	
	$album = '';
	
	// default Zenphoto behaviour does not return bottom level folder
	if (getOption('mod_rewrite') AND array_key_exists('album', $_GET)) {
		$album = urldecode(sanitize($_GET['album'], 0));
		//strip trailing slashes
		if (substr($_GET['album'], -1, 1) == '/') {
			$album = substr($_GET['album'], 0, strlen($_GET['album'])-1);
		}
	// load global variables from Zenphoto's index.php
	} else {
		$album = $_GET['album'];
	}
	
	$albumbits = explode('/', $album);
	
	// get the bottom folder in the hierarchy
	if (sizeof($albumbits) > 0) {
		$albumFolderToSearchFor = $albumbits[sizeof($albumbits) - 1];
		
		// query the DB to find any albums with same folder name
		// NOTE: albums contain the entire folder path 
		// we only want to look at the bottom level of the hierarchy
		$searchSql = "SELECT folder FROM " . $_zp_db->prefix('albums') . " a WHERE a.folder LIKE " . 
				$_zp_db->quote('%'.$albumFolderToSearchFor) . " OR a.folder = " . 
				$_zp_db->quote($albumFolderToSearchFor) . "";
		
		$searchResult = $_zp_db->queryFullArray($searchSql);
		
		// if single result is returned, then we have a match!
		if (sizeof($searchResult) == 1)  {
			// fix for some "query_full_array()" differences (on some Zenphoto versions?)
			if (sizeof($searchResult[0]) > 0) {
				$searchResult = $searchResult[0];
			}
			
			// build up the URL to redirect to
			$location = rewrite_path("/" . $searchResult["folder"] . "/", "/index.php?album=" . $searchResult["folder"]);
			
			// redirect the browser
			status_header(301);
			header("Location: $location");
			die();
		}
	}
}

function status_header( $header ) {
	if ( 200 == $header )
		$text = 'OK';
	elseif ( 301 == $header )
		$text = 'Moved Permanently';
	elseif ( 302 == $header )
		$text = 'Moved Temporarily';
	elseif ( 304 == $header )
		$text = 'Not Modified';
	elseif ( 404 == $header )
		$text = 'Not Found';
	elseif ( 410 == $header )
		$text = 'Gone';

	@header("HTTP/1.1 $header $text");
	@header("Status: $header $text");
}
?>