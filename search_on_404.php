<?php
/**
 * Redirect on 404
 *
 * For use on 404 'file not found' pages. Searches for possibly related images or albums based on the current URL
 *
 * @author Marcus Wong (wongm)
 * @package plugins
 */

$plugin_description = gettext("For use on 404 'file not found' pages. Searches for possibly related images or albums based on the current URL.");
$plugin_author = "Marcus Wong (wongm)";
$plugin_version = '1.0.0'; 
$plugin_URL = "https://github.com/wongm/zenphoto-404-helpers/";

function searchOn404() {
	global $_zp_current_search, $_zp_current_album;
	
	// load the search in controller.php
	$_zp_current_search = new SearchEngine();
	
	// reset the search values with what was entered in the URL
	$_zp_current_search->setSearchParams("s=" . getSearchTermFrom404());
	
	// reset album 
	// so we look at images in search, not in the (invalid) album
	$_zp_current_album = null;	
	add_context(ZP_SEARCH);	
}

function wasLookingForImage() {
	global $_GET;
	
	if (array_key_exists('image', $_GET) AND $_GET['image'] != '') 
	{
		return true;
	}
	
	return false;
}

function getSearchTermFrom404() {
	global $_GET;
	
	$term = '';
	
	// get the image and album values from index.php
	if (array_key_exists('image', $_GET) AND $_GET['image'] != '') 
	{
		$term = $_GET['image'];
		
		// remove common file extensions
		$extensions = array('.html', '.htm', '.php', '.jpg', '.jpeg', '.gif', '.png');
		foreach ($extensions as $extension)
		{
			$term  = str_replace($extension, '', strtolower($term));
		}
		
		// split the search terms on "." (full stop)
		// just in case an incomplete file extension exists
		// will cause search to look at all terms
		$term  = str_replace(".", ',', strtolower($term));
	}
	else if (array_key_exists('album', $_GET)) 
	{
		$term = $_GET['album'];
	}
	
	return $term;
}
?>