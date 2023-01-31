<?php
/**
 * Plugin Update Checker Library 1.2
 */

if ( !class_exists('WL_MIM_UpdateChecker') ):
	
/**
 * A custom Plugin update checker. 
 * 
 * @author Janis Elsts
 * @copyright 2012
 * @version 1.2
 * @access public
 */
class WL_MIM_UpdateChecker {
	public $product_name = '';       //The Plugin associated with this update checker instance.
	public $metadataUrl = '';       //The URL of the Plugin's metadata file.	
	protected static $filterPrefix = 'tuc_request_update_';	                                 
	/**
	 * Class constructor.
	 *
	 * @param string $Plugin Plugin slug, e.g. "twentyten".
	 * @param string $metadataUrl The URL of the Plugin metadata file.
	 */
	public function __construct($product_name, $metadataUrl){
		$this->metadataUrl = $metadataUrl;		
		$this->product_name = $product_name;	
		$this->WL_MIM_installHooks();		
	}
	
	/**
	 * Install the hooks required to run periodic update checks and inject update info 
	 * into WP data structures.
	 * 
	 * @return void
	 */
	public function WL_MIM_installHooks(){		
		//Add custom Plugin update menu at Dashboard update menu section.
		add_action('admin_menu', array( $this, 'WL_MIM_my_Plugin_menu'));	
		//Add custom Plugin update available notice with link menu at top menu section.
		add_action('admin_bar_menu', array( $this, 'WL_MIM_Plugin_update_link'), 99999999);
		//Insert our update css style by this function at footer.
		add_action('admin_footer', array( $this, 'WL_MIM_my_admin_Plugin_update_function'));		
	}
	
	public function WL_MIM_getNewVersion() {
		$details_url = 'https://weblizar.com/plugins/multi-institute-management/';
		$download_url = 'https://weblizar.com/amember/login';
		$updation_detail = unserialize(get_option('wl-multi-institute-updation-detail'));
		$new_version = new stdClass();

		// Next date check from server
		if( isset( $updation_detail['next_check'] ) && ( strtotime(date('Y-m-d')) >= strtotime($updation_detail['next_check']) ) ) {
			$update_version = $this->WL_MIM_requestUpdate();
			if($update_version) {
				$new_version->version = $update_version->version;
			} else {
				$new_version->version = $this->WL_MIM_getInstalledVersion();
			}
		// Same date check from database
		} elseif( isset( $updation_detail['check_again'] ) && $updation_detail['check_again'] ) {
			$new_version->version = $updation_detail['new_version'];
		// First time check from server
		} elseif( ! isset( $updation_detail['next_check'] ) ) {
			$update_version = $this->WL_MIM_requestUpdate();
			if($update_version) {
				$new_version->version = $update_version->version;
			} else {
				$new_version->version = $this->WL_MIM_getInstalledVersion();
			}
		// Already checked for today
		} else {
			$new_version->version = $this->WL_MIM_getInstalledVersion();
		}
		$new_version->details_url = $details_url;
		$new_version->download_url = $download_url;
		return $new_version;
	}

	public function WL_MIM_Plugin_update_link($wp_admin_bar) 
	{
		$new_version = $this->WL_MIM_getNewVersion();		
		$old_version = $this->WL_MIM_getInstalledVersion();
		if ( is_object($new_version) && version_compare($new_version->version, $old_version, '>') )
		{
		$args = array(
				'id' => 'WL_MIM_Plugin_update_menu',
				'title' => 'Multi Institute Management <span style="color:#fff;font-weight:bold;">Update Available !</span>', 
				'href' => get_admin_url().'index.php?page=WL_MIM_new_updates',  
				'meta' => array(
					'class' => 'WL_MIM_update-link',
					'title' => 'WL_MIM_Update Available'
					)
			);
			$wp_admin_bar->add_node($args);
	    }
	}
	
	public function WL_MIM_my_Plugin_menu() {
		$new_version = $this->WL_MIM_getNewVersion();		
		$old_version = $this->WL_MIM_getInstalledVersion();
		if ( is_object($new_version) && version_compare($new_version->version, $old_version, '>') )
		{
			add_dashboard_page('Multi Institute Management Update', 'Multi Institute Management Update', 'read', 'WL_MIM_new_updates', array( $this, 'WL_MIM_my_Plugin_function'));
		}
	}
	public function WL_MIM_my_Plugin_function() { 
		$new_version = $this->WL_MIM_getNewVersion();		
		$old_version = $this->WL_MIM_getInstalledVersion();
		if ( is_object($new_version) && version_compare($new_version->version, $old_version, '>') )
		{
		$msg = __( 'Weblizar WordPress Plugin ( %s ) ', $this->product_name );
		$msg = sprintf( $msg, $this->product_name );
			?>
			<div class="weblizar_new_updates">
				<h1 class="weblizar_new_updates_heading">	
				<a class="navbar-brand" href="https://weblizar.com/"><img style="    width: 32%;height: 20%;" src="https://weblizar.com/wp-content/uploads/2016/04/logo.png" class="img-responsive-update" alt="weblizar-logo"></a>
				</br></br></br>
				<a target="_blank" href="<?php echo WL_MIM_WEBLIZAR_PLUGIN_URL; ?>" title="Installing Weblizar WordPress Plugin"><?php _e($this->product_name);?></a>
				</h1>
				<div class="weblizar_new_updates_details">
					<h3 class="weblizar_new_updates_Plugin_details">
						<?php echo $msg; 
						echo '</br><p style="font-size:17px;">New Version : '; _e($this->product_name." - ".$new_version->version);
						?>
					</p></h3>
					<div class="weblizar_new_updates_details_block">
					<h2 class="weblizar_new_updates_details_main_headings" ><?php _e('Weblizar Plugin Update Process',WL_MIM_DOMAIN);?></h2>			
						<h4><?php _e('You can see the manual Update steps at our weblizar site',WL_MIM_DOMAIN);?>
						<?php _e(': <a target="_blank" href="'.$new_version->details_url.'">',WL_MIM_DOMAIN);?>			
							<?php _e( "Weblizar WordPress Plugin ", $new_version->details_url);  ?>				
						</a>
						</h4>
						<p><?php _e('1. First, Please download the new version of Weblizar Premium Plugin Package from your weblizar account. Then Login with your user name and Password.',WL_MIM_DOMAIN);?></p>
						<h2>
							<a target="_blank" href="<?php echo $new_version->download_url; ?>">
								<?php _e( 'Login For Download New Version', $this->product_name ); ?>							
							</a>
						</h2>	
						<p><?php _e('After Login you find this page',WL_MIM_DOMAIN);?></p>
						<p><img style="margin: 20px 0; width: 750px;" src="<?php echo WL_MIM_PLUGIN_URL .'assets/images/institute-pro.png'; ?>" alt="Multi Institute Management"></p>
						<p><?php _e('2. Upload the downloaded Plugin package in your site ( server ) using wordpress Plugin Uploader ( Go to Admin Dashboard =&gt; Plugins=&gt; Add New =&gt; Upload Plugin =&gt; Choose File and click on install button ).',WL_MIM_DOMAIN);?></p>
						<p><?php _e('Otherwise you can use any FTP ( filezilla or bitwise ) and upload the Plugin zip package or unzip the Plugin package. After that go to Admin Dashboard =&gt; Plugins and click on active.',WL_MIM_DOMAIN);?></p>
						<p><img style="margin: 20px 0; width: 750px;" src="<?php echo WL_MIM_PLUGIN_URL .'assets/images/install-institute-pro.png'; ?>" alt="Install Multi Institute Management"></p>
						<p><?php _e('Your Weblizar pro Plugin Activated Now',WL_MIM_DOMAIN);?></p>
						<p><?php _e('For more details to follow the Plugin documentation link : <a target="_blank" href='.WL_MIM_WEBLIZAR_PLUGIN_URL.'>'.WL_MIM_WEBLIZAR_PLUGIN_URL.'</a>',WL_MIM_DOMAIN);?></p>
						<h2 class="blog_full_title"><?php _e('Thanks for purchasing our Premium Plugin',WL_MIM_DOMAIN);?></h2>
				</div>
				</div>
			</div>
	<?php
			}
		}
		function WL_MIM_my_admin_Plugin_update_function() { ?>	
			<style>
				.weblizar_new_updates {background-color:#31A3DD;width:90%;padding:30px;margin:30px;}
				.weblizar_new_updates_details{background-color:#fff;width:90%;padding:30px;margin:30px;border-radius: 10px;}
				h1.weblizar_new_updates_heading {text-decoration:none;width: 50%; margin: 0px auto; padding: 40px; margin-bottom: 40px;background-color: #fff;border-radius: 10px;text-align: center;}
				h1.weblizar_new_updates_heading a{text-align:center;color:#31A3DD!important;text-decoration: none;font-size: 30px;}
				h3.weblizar_new_updates_Plugin_details{text-align: center;font-size: 30px;margin-bottom: 56px;color: #31A3DD;}
				h1.weblizar_new_updates_heading a img{text-align:center;color:#31A3DD!important;}
				#wp-admin-bar-WL_MIM_Plugin_update_menu.WL_MIM_update-link{background-color:#31A3DD !Important;border-radius:5px!important;}
				#wp-admin-bar-WL_MIM_Plugin_update_menu.WL_MIM_update-link a{color:#fff !Important;}
				#wp-admin-bar-WL_MIM_Plugin_update_menu{margin-right:5px !Important;}				
				h2.blog_full_title{color:#31A3DD;text-align:center;font-size:30px;margin-top:50px;margin-bottom:50px;}
			</style>
	<?php }
 /**
	 * Retrieve update info from the configured metadata URL.
	 * 
	 * Returns either an instance of PluginUpdate, or NULL if there is 
	 * no newer version available or if there's an error.
	 * 
	 * @uses wp_remote_get()
	 * 
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return PluginUpdate 
	 */
	public function WL_MIM_requestUpdate($queryArgs = array()){
		//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
		$queryArgs['installed_version'] = $this->WL_MIM_getInstalledVersion(); 
		$queryArgs = apply_filters(self::$filterPrefix.'query_args-'.$this->product_name, $queryArgs);
		
		//Various options for the wp_remote_get() call. Plugins can filter these, too.
		$options = array(
			'timeout' => 10, //seconds
		);
		$options = apply_filters(self::$filterPrefix.'options-'.$this->product_name, $options);
		
		$url = $this->metadataUrl; 
		if ( !empty($queryArgs) ){
			$url = add_query_arg($queryArgs, $url);
		}
		
		//Send the request.
		$result = wp_remote_get($url, $options);
		
		//Try to parse the response
		$PluginUpdate = NULL;
		$code = wp_remote_retrieve_response_code($result);
		$body = wp_remote_retrieve_body($result);
		if ( ($code == 200) && !empty($body) ){
			$PluginUpdate = PluginUpdate::fromJson($body);
			
			if ($PluginUpdate != null) {
				$updationDetail = serialize(array(
							'current_version' => $this->WL_MIM_getInstalledVersion(),
							'new_version' => $PluginUpdate->version,
							'checked_on' => date('Y-m-d'),
							'check_again' => version_compare($PluginUpdate->version, $this->WL_MIM_getInstalledVersion(), '>') ? true : false, 
							'next_check' => date('Y-m-d', strtotime("+1 day"))
						));
				update_option('wl-multi-institute-updation-detail', $updationDetail);
			}

			//The update should be newer than the currently installed version.
			if ( ($PluginUpdate != null) && version_compare($PluginUpdate->version, $this->WL_MIM_getInstalledVersion(), '<') ){
				$PluginUpdate = NULL;
			}
		}
		
		$PluginUpdate = apply_filters(self::$filterPrefix.'result-'.$this->product_name, $PluginUpdate, $result);
		return $PluginUpdate;
	}
	
	
	/**
	 * Get the currently installed version of our Plugin.
	 * 
	 * @return string Version number.
	 */
	public function WL_MIM_getInstalledVersion(){
		return WL_MIM_VERSION;
	}
}
	
endif;

if ( !class_exists('PluginUpdate') ):

/**
 * A simple container class for holding information about an available update.
 * 
 * @author Janis Elsts
 * @copyright 2012
 * @version 1.0
 * @access public
 */
class PluginUpdate {
	public $version;      //Version number.
	public $details_url;  //The URL where the user can learn more about this version. 
	public $download_url; //The download URL for this version of the Plugin. Optional.
	
	/**
	 * Create a new instance of PluginUpdate from its JSON-encoded representation.
	 * 
	 * @param string $json Valid JSON string representing a Plugin information object. 
	 * @return PluginUpdate New instance of PluginUpdate, or NULL on error.
	 */
	public static function fromJson($json){
		$apiResponse = json_decode($json);
		if ( empty($apiResponse) || !is_object($apiResponse) ){
			return NULL;
		}
		
		//Very, very basic validation.
		$valid = isset($apiResponse->version) && !empty($apiResponse->version) && isset($apiResponse->details_url) && !empty($apiResponse->details_url);
		if ( !$valid ){
			return NULL;
		}
		
		$update = new self();
		foreach(get_object_vars($apiResponse) as $key => $value){
			$update->$key = $value;
		}
		
		return $update;
	}
	
	/**
	 * Transform the update into the format expected by the WordPress core.
	 * 
	 * @return array
	 */
	public function toWpFormat(){
		$update = array(
			'new_version' => $this->version,
			'url' => $this->details_url,
		);
		
		if ( !empty($this->download_url) ){
			$update['package'] = $this->download_url;
		}
		
		return $update;
	}
}	
endif;
$weblizar_update_checker = new WL_MIM_UpdateChecker('Multi Institute Management','https://weblizar.com/updates/plugins/multi-institute-management.json');