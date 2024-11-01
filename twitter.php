<?php
/*
Plugin Name: WP My Twitter
Plugin URI: http://www.infinetsoftware.com/blog/40-wp-my-twitter-plugin/
Description: WP My Twitter
Author: WP My Twitter
Version: 1.1
Author URI: http://www.wpsponsored.com
*/

if (function_exists('add_action')) {
	// Initialize Plugin
	add_action('plugins_loaded', 'LoadWPTwitter');
	add_action('wptwitter_import', array($objWPTwitter, 'LoadLive'));
}

function LoadWPTwitter() {
	global $objWPTwitter;
	$objWPTwitter = new WPTwitter();

	// Install If Needed
	register_activation_hook(__FILE__,'WPTwitterInstall');

	// Add Admin Tab
	add_action('admin_menu', 'WPTwitterAdmin');

	// Register Widget
	register_sidebar_widget('WP My Twitter', 'WPTwitterSidebar');
}


class WPTwitter {

	var $strService;
	var $strUser;
	var $strCached;
	var $strResponse;
	var $strShowPowered;

	function WPTwitter() {
		if (function_exists('add_shortcode')) {
			add_shortcode('WPMyTwitter', array(&$this, 'GetTweets'));
		}
	}
	
	function LoadOpts() {
		$this->strService = 'http://twitter.com/statuses/user_timeline.rss';
		$this->strUser = get_option('WPTwitter_Auth');
		$this->strCache = get_option('WPTwitter_Cache');
		$this->strShowPowered = get_option('WPTwitter_ShowPowered');
	}
	
	function GetTweets($arrParams = '') {
		// PHP 5 XML Libraries Required
		if (!function_exists('simplexml_load_string')) {
			return false;
		}

		// Load Options at Runtime
		$this->LoadOpts();
		if ($this->strUser) {
			$arrUser = explode(':', $this->strUser);
			$strUsername = $arrUser[0];
		}

		// Shortcode Params
		if (function_exists('shortcode_atts')) {
			extract(shortcode_atts(array('Count' => 5), $arrParams));
		}
		if (!$Count) {
			$Count = 5;
		}
		$this->strService;
		

		// Load from Cache
		$this->strResponse = $this->strCache;

		if (empty($this->strResponse)) {
			return false;
		}
		
		$objXML = simplexml_load_string($this->strResponse);
		
		if (!$objXML) {
			return false;
		}

		$objChannel = $objXML->channel;
		$strLink = $objChannel->link;

		$strOut = '<ul>';
		
		$control = 0;
		
		if (!$objChannel->item) {
			return false;
		}

		foreach ($objChannel->item as $objThisItem) {

			$strThisTitle = $objThisItem->title;
			$strThisDesc = $objThisItem->description;
			$strThisDate = $objThisItem->pubDate;
			$strThisGUID = $objThisItem->guid;
			$strThisLink = $objThisItem->link;

			$strThisTitle = str_replace($strUsername . ': ', '', $strThisTitle);
			
			$strOut .= '<li><a href="' . $strThisLink . '" title="' . htmlspecialchars($strThisTitle) . '">' . htmlspecialchars($strThisTitle) . '</a></li>';
			
			$control++;
			
			if ($control >= $Count) {
				break;
			}
		}

		$strOut .= '</ul>';
		
		if ($this->strShowPowered == 'yes') {
			$strOut .= '<p><small>Plugin by <a href="http://www.wpsponsored.com/">WP Sponsored Posts</a></small></p>';
		}
		
		return $strOut;
	}

	function LoadLive() {
		$this->LoadOpts();
		if (empty($this->strUser)) {
			return false;
		}
		
		$strResponse = $this->CurlIt();
		$strNow = date('Y-m-d h:i:sa');
		
		if (!empty($strResponse)) {
			$this->strResponse = $strResponse;
			update_option('WPTwitter_Cache', $strResponse);
		}
		else {
			$this->strResponse = '';
		}
	}

	function CurlIt() {
		if (!function_exists('curl_exec')) {
			return false;
		}
		$ch = curl_init();   
		curl_setopt($ch, CURLOPT_URL, $this->strService);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $this->strUser);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		$strResult = curl_exec($ch);
		
		curl_close($ch);
		
		return $strResult;
	}
}

function WPTwitterInstall() {
	add_option('WPTwitter_Auth', '');
	add_option('WPTwitter_Cache', '');
	add_option('WPTwitter_ShowPowered', 'yes');
	
	wp_schedule_event(time(), 'hourly', 'wptwitter_import');
}

function WPTwitterAdmin() {
	global $objWPTwitter;
	add_options_page('WP My Twitter', 'WP My Twitter', 8, __FILE__, 'WriteAdminTab');
}

function WriteAdminTab() {
	global $objWPTwitter;
	if (strtoupper($_POST['Action']) == 'UPDATE') {
		$strUsernameVal = $_POST['Username'];
		$strPasswordVal = $_POST['Password'];
		$strShowPoweredVal = $_POST['ShowPowered'];
		
		if ($strShowPoweredVal != 'yes') {
			$strShowPoweredVal = 'no';
		}
		
		update_option('WPTwitter_Auth', $strUsernameVal . ':' . $strPasswordVal);
		update_option('WPTwitter_ShowPowered', $strShowPoweredVal);
		
		$objWPTwitter->LoadLive();
	}
	$objWPTwitter->LoadOpts();
	if ($objWPTwitter->strUser) {
		$arrAuth = explode(':', $objWPTwitter->strUser);
		if (is_array($arrAuth)) {
			$strUsernameVal = $arrAuth[0];
			$strPasswordVal = $arrAuth[1];
		}
	}
	$strShowPowered = $objWPTwitter->strShowPowered;
?>
<h2>WP My Twitter</h2>

<form method="post" action="">

<table class="form-table">
  <tr valign="top">
    <th scope="row"><label for="Username">Twitter Username:</label></th>
    <td><input type="text" name="Username" id="Username" value="<?php echo $strUsernameVal; ?>"/></td>
  </tr>
  <tr valign="top">
    <th scope="row"><label for="Password">Password:</label></th>
    <td><input type="password" name="Password" id="Password" value="<?php echo $strPasswordVal; ?>"/></td>
  </tr>
  <tr>
    <th scope="row">&nbsp;</th>
	<td><input type="checkbox" name="ShowPowered" id="ShowPowered" value="yes" <?php if (empty($strShowPowered) || $strShowPowered == 'yes') { echo 'checked="checked"'; } ?>/> <label for="ShowPowered">Link Back</label></td>
  </tr>
</table>

<p align="center"><input type="submit" name="Action" value="Update" class="button-primary"/></p>

</form>

<h2>Tweets</h2>

<?php
WPTwitter_GetTweets();
?>

<?php
		
}

function WPTwitterSidebar($arrParams) {
	extract($arrParams);
	echo $before_widget;
	echo $before_title;
	echo 'Twitter Feed';
	echo $after_title;
	WPTwitter_GetTweets();
	echo $after_widget;

}

function WPTwitter_GetTweets($intInCount = 5) {
	global $objWPTwitter;
	echo $objWPTwitter->GetTweets(array('Count' => $intInCount));
}

?>