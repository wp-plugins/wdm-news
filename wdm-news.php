<?php
/*
Plugin Name: WDM News
Plugin URI: http://www.manosdepiedra.com
Description: WDM News show your news on sidebar. When you activate it, you can move widget on left or right sidebar for show it. For adding or remove news you can use a submenu on plugin.
Version: 1.7
Author: Walter Dal Mut
Author URI: walter@manosdepiedra.com
*/
add_action('init', 'wdmnews_init');
register_activation_hook( __FILE__, 'wdmnews_install' );

function widget_wdmnews_init() {


	if ( !function_exists('register_sidebar_widget') )
		return;

	function show_news()
	{
		global $table_prefix, $wpdb, $user_level;
		echo '<li class="sideitem"><h2 class="widgettitle">News</h2>';
		echo '<ul>';
			$query = "SELECT news, link, source, data FROM " . $table_prefix . "wdmnews ORDER BY data desc LIMIT 0, ".get_option( "wdmnews_show_max_news" );
			$news = $wpdb->get_results($query);
			foreach ($news as $new) {
				$data = $new->data;
				if( get_option( "wdmnews_showtime" ) == "false" )
				{
					$data = substr( $data, 0, strpos( $data, " ") );
				}
				echo '<li><a target="_blank" href="'.$new->link.'"><b>'.$data.'</b><br />'.$new->news."</a><br />$source</li>";
			}
		echo '</ul></li>';
	}
	register_sidebar_widget('WDM News', 'show_news');
}
add_action('plugins_loaded', 'widget_wdmnews_init');


function wdmnews_init()
{	
 	add_action('admin_menu', 'wdmnews_config_page');
}

function wdmnews_installed()
{
	global $wpdb, $table_prefix;
	$query = "SHOW TABLES LIKE '".$table_prefix."wdmnews'";
	$install = $wpdb->get_var( $query );
	
	if( $install === NULL )
		return false;
	else
		return true;
}

function wdmnews_install()
{
	global $table_prefix, $wpdb, $user_level;
	get_currentuserinfo();
    if ($user_level < 8) { return; }

	//check if is installed.
	if( !wdmnews_installed() ) //Compatibilities with previous version.
	{
		$table_name = $table_prefix . "wdmnews";
		
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
	 	     news_id mediumint(9) NOT NULL AUTO_INCREMENT,
	 	     news text NOT NULL,
	 	     data datetime NOT NULL,
	 	     UNIQUE KEY news_id (news_id)
	 	   );";
	    
	    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	    dbDelta($sql);  
	    
	    $sql = "ALTER TABLE ".$table_name." ADD link VARCHAR( 255 ) NOT NULL AFTER news";
	    $wpdb->query($sql);
	    
		$sql = "ALTER TABLE ".$table_prefix."wdmnews ADD source VARCHAR( 255 ) NOT NULL AFTER link";
		$wpdb->query( $sql );
		
		//Upgrading
		$version = get_option( "wdmnews_version");	

		if( $version === FALSE )
			$version = "1.5";
		else
		{
			if( $version == "1.5" )
				$version = "1.6";
		}
		
		switch( $version )
		{
			case "1.5":
				add_option( "wdmnews_version", "1.5" );
				add_option( "wdmnews_show_max_news", "5" );
			case "1.6":
				add_option( "wdmnews_showtime", "true" );
		}
	}
}

function wdmnews_config_page() {
	if ( function_exists('add_submenu_page') )
	{
		add_menu_page( 'WDM News', 'WDM News', 8, __FILE__, 'wdmnews_conf' );
		add_submenu_page(__FILE__, __('WDM News Settings'), __('WDM News Settings'), 'manage_options', 'wdmnews-key-settings', 'wdmnews_config');
		add_submenu_page(__FILE__, __('WDM News Uninstall'), __('WDM News Uninstall'), 'manage_options', 'wdmnews-key-uninstall', 'wdmnews_uninstall');
	}
}

function wdmnews_config()
{
	if( isset( $_POST["wdmnews_submit"]) AND $_POST["wdmnews_submit"] == "Set" )
	{
		$wdmnews_max_news = (int)$_POST["wdmnews_max_news"];
		if( $wdmnews_max_news > 0)
			update_option( "wdmnews_show_max_news", $_POST["wdmnews_max_news"] );
		else
			$wdmnews_error = true;
		
		//Change show time pubblications.
		if( isset($_POST["wdmnews_showtime"]) )
			update_option( "wdmnews_showtime", "true" );
		else
			update_option( "wdmnews_showtime", "false" );
	}
	
	//Control if you want show time of publication.
	$checked = get_option( "wdmnews_showtime" );
	if( $checked != "false" )
		$checked = " checked ";
	
	?>
	<div class="wrap">
		<h2><?php _e('WDM News Settings'); ?></h2>
		<?php if( $wdmnews_error == true ) echo "<p>What are you doing?</p>"?>
		<form method="POST" action="" >
			<p>Show max news: <input type="text" name="wdmnews_max_news" size="3" value="<?php echo get_option("wdmnews_show_max_news"); ?>" /></p>
			<p>Show time of publication: <input type="checkbox" name="wdmnews_showtime" value="true" <?php echo $checked; ?>/></p>
			<p><input type="submit" name="wdmnews_submit" value="Set" /></p>
		</form>
	</div>
	<?php
}

function wdmnews_uninstall()
{
	global $table_prefix, $wpdb, $user_level;
	
	$table_name = $table_prefix . "wdmnews";
	?>
	<div class="wrap">
	<h2><?php _e('WDM News Uninstall'); ?></h2>
	<p>If you want completely remove this pluging, you push this link:</p>
	<div class="narrow">
		<p><a href="?page=wdmnews-key-uninstall&delete=true">Uninstall</a></p>
		<?php
			if( $_GET["delete"] )
			{
			 	$sql = "DROP TABLE IF EXISTS ".$table_name;
			 	get_currentuserinfo();
	    		if ($user_level < 8) { return; }
				
				$wpdb->query($sql);
				
				echo '<div id="message" class="updated fade">Uninstall successful.</div>';
				echo '<p>You must deactivate plugin from plugins page for complete uninstall.</p>';
			}
			
			delete_option( "wdmnews_version");
			delete_option( "wdmnews_show_max_news" );
			delete_option( "wdmnews_showtime" );
		?>
	</div>
	</div>
	<?php 	
}

function wdmnews_conf() 
{
	global $table_prefix, $wpdb, $user_level;
	
	if( isset( $_POST["submit"]) ) :
		wdmnews_add( );		
		?>
		<div id="message" class="updated fade"><p><strong><?php _e('News: \''.$_POST["news"].'\' saved') ?></strong></p></div>
		
<?php 
	endif;
	
	if( isset($_POST["delnews"]) AND $_POST["delnews"] == "Delete checked" )
	{
		if(isset($_POST["news"]))	
			foreach($_POST["news"] as $news_id)
			{
				$ok = $wpdb->query('DELETE FROM '.$table_prefix."wdmnews".' WHERE news_id = '.$news_id);
				if( $ok == FALSE )
					echo '<div id="error" class="updated fade"><p>Impossibile to delete news</p></div>';
				else
					echo '<div id="message" class="error"><p>News deleted</p></div>';
			}
	}
?>	
	<div class="wrap">
	<h2><?php _e('WDM News Adding'); ?></h2>
	<div class="narrow">
		<form method="POST" action="">
			<p><b>Insert a news:</b><br /><textarea rows="5" cols="70" name="news"></textarea></p>
			<p><b>Source: </b><input type="text" name="wdmnews_source" value="" /></p>
			<p><b>Link this news:<br />http://</b></span><input type="text" name="link" value="" size="40" /></p>
			<p align="right"><input type="submit" name="submit" value="Send News" /></p>		
		</form>
		<form method="POST" action="">
		<?php
			//Show news...
			$query = "SELECT news_id, news, link, data FROM " . $table_prefix . "wdmnews ORDER BY data desc";
			
			$news = $wpdb->get_results($query);
			foreach ($news as $new) {
				echo '<p><input type="checkbox" name="news[]" value="'.$new->news_id.'" />'.$new->news." (".$new->link.")</p>";
			}
			
			echo '<p align="right"><input type="submit" name="delnews" value="Delete checked" /></p>';
		?>
		</form>
	</div>
	</div>
<?php	
}

function wdmnews_add()
{
	global $table_prefix, $wpdb, $user_level;
	
	$news = htmlentities($_POST["news"], ENT_QUOTES, "UTF-8");
	$link = htmlentities($_POST["link"], ENT_QUOTES, "UTF-8");
	$source = htmlentities($_POST["wdmnews_source"], ENT_QUOTES, "UTF-8");
 	$query = "INSERT INTO ".$table_prefix . "wdmnews"." ( news, link, source, data ) VALUES ( '$news', 'http://$link', '$source',NOW() )";
 	$wpdb->query($query);
}
?>