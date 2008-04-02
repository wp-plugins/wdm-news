<?php
/*
Plugin Name: WDM News
Plugin URI: http://www.manosdepiedra.com
Description: WDM News show your news on sidebar. When you activate it, you can move widget on left or right sidebar for show it. For adding or remove news you can use a submenu on plugin.
Version: 1.1
Author: Walter Dal Mut
Author URI: http://www.manosdepiedra.com
*/

add_action('init', 'wdmnews_init');

function widget_wdmnews_init() {


	if ( !function_exists('register_sidebar_widget') )
		return;

	function show_news()
	{
		global $table_prefix, $wpdb, $user_level;
		echo '<h2 class="widgettitle">News</h2>';
		echo '<ul>';
			$query = "SELECT news, data FROM " . $table_prefix . "wdmnews ORDER BY data desc";
			
			$news = $wpdb->get_results($query);
			foreach ($news as $new) {
				echo '<li><b>'.$new->data.'</b><br />'.$new->news."</li>";
			}
		echo '</ul>';
	}
	register_sidebar_widget('WDM News', 'show_news');
}
add_action('plugins_loaded', 'widget_wdmnews_init');


function wdmnews_init()
{
	wdmnews_install();	 
	
 	add_action('admin_menu', 'wdmnews_config_page');
}

function wdmnews_install()
{
	global $table_prefix, $wpdb, $user_level;
	
	$table_name = $table_prefix . "wdmnews";
	
	$sql = "CREATE TABLE IF NOT EXISTS '".$table_name."' (
 	     'news_id' mediumint(9) NOT NULL AUTO_INCREMENT,
 	     'news' text NOT NULL,
 	     'data' datetime NOT NULL,
 	     PRIMARY KEY  ('news_id')
 	   );";
	
	get_currentuserinfo();
    if ($user_level < 8) { return; }
    
    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    dbDelta($sql);  
}

function wdmnews_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('post.php', __('WDM News Adding'), __('WDM News Adding'), 'manage_options', 'wdmnews-key-config', 'wdmnews_conf');
	
}

function wdmnews_conf() 
{
	global $table_prefix, $wpdb, $user_level;
	
	if( isset( $_POST["submit"]) ) :
		wdmnews_add( $_POST["news"]);		
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
					echo '<div id="message" class="updated fade"><p>Impossibile delete news</p></div>';
				else
					echo '<div id="message" class="error"><p>News deleted</p></div>';
			}
	}
?>	
	<div class="wrap">
	<h2><?php _e('WDM News Adding'); ?></h2>
	<div class="narrow">
		<form method="POST" action="">
			<p><b>Insert a news:</b><input type="text" name="news" value="" size="50"/></p>
			<p align="right"><input type="submit" name="submit" value="Send News" /></p>		
		</form>
		<form method="POST" action="">
		<?php
			//Show news...
			$query = "SELECT news_id, news, data FROM " . $table_prefix . "wdmnews";
			
			$news = $wpdb->get_results($query);
			foreach ($news as $new) {
				echo '<p><input type="checkbox" name="news[]" value="'.$new->news_id.'" />'.$new->news."</p>";
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
 	$query = "INSERT INTO ".$table_prefix . "wdmnews"." ( news, data ) VALUES ( '$news', NOW() )";
 	$wpdb->query($query);
}
?>