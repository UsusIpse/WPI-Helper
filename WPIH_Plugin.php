<?php


include_once('WPIH_LifeCycle.php');

class WPIH_Plugin extends WPIH_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            '_version' => array('Installed Version'), 
            'media_selector_attachment_id' => array(__('Choose your Logo', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
			'LikeCookies' => array(__('I like these cookies', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'WPI-Helper';
    }

    protected function getMainPluginFileName() {
        return 'wpi-helper.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {
		
		//Check to see if WP-Invoice is installed
		//add_action('admin_init', array($this, 'WPIH_PreAction') );
		add_action('init', array($this, 'WPIH_Action') );
		include_once('WPIH_ShowImageShortCode.php');
			$isc = new WPIH_ShowImageShortCode();
			$isc->register('wpih-main-image');
        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
		// \/ moved to WPIH_action \/
        //add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

    }
	function wpih_display_user_selection( $file_path, $screen, $path ) {
		global $wpdb;
		$new_file_path = dirname(__FILE__) . '/ui/wp-invoice_page_wpi_page_manage_invoice.php';
		$ucan = current_user_can('activate_plugins');
		


		if ( $screen != 'wp-invoice_page_wpi_page_manage_invoice' ) {
			return $file_path;
		}
		if ( empty( $_REQUEST[ 'wpi' ] ) && $ucan) {
			return $path . '/user_selection_form.php';
		}
		if( empty( $_REQUEST[ 'wpi' ] ) && !$ucan){
			wp_redirect( get_site_url() . '/');
		}
		if ( \UsabilityDynamics\Utility::is_older_wp_version( '3.4' ) ) {
			return $path = $path . '/wp-invoice_page_wpi_page_manage_invoice_legacy.php';
		}

		return $new_file_path;
	}
	function WPIH_Action() {
		global $wpi_settings, $WPI_UI;
		  if ( class_exists('WPI_Core') ) {
			
			//add_filter('wpi_invoice_pre_save', array($this, 'wpih_pre_save'), 10, 2 );
			add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
			add_action('admin_enqueue_scripts', array(&$this, 'register_wpih_scripts') );
			add_action('wp_ajax_wpih_action', array(&$this, 'wpih_ajax_action'));

			
			remove_filter( 'wpi_page_loader_path', array( 'WPI_UI', "wpi_display_user_selection" ), 0, 3 );
			add_filter( 'wpi_page_loader_path', array( $this, "wpih_display_user_selection" ), 1, 3 );
			
		
			wp_enqueue_script('wpi-gateways');
			//require_once(__DIR__ . '/lib/wpih_class_invoice.php');
			//require_once(__DIR__ . '/lib/wpih_class_list_table.php');
			
			
			//add_action('wpi_invoice_saved', array($this, 'wpih_new_invoice_notification'), 10, 2);
			add_action ( 'admin_enqueue_scripts', function () {
				if (is_admin ())
					wp_enqueue_media ();
			} );

			
		  } else {
			add_action('admin_notices', array($this, 'wpih_not_loaded'));
		  }
	}

	function wpih_ajax_action() {
		global $wpdb;
		$whatever = intval($_POST['whatever']);
		$whatever += 10;
		echo $whatever;
		wp_die();
	}
	function register_wpih_scripts ($hook) {
//		if('index.php' != $hook){
//			return;
//		}
		wp_register_script( 'wpih-ajax-script', plugins_url( '/js/wpih.js', __FILE__ ), array('jquery'), null, true );

		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		wp_localize_script( 'wpih-ajax-script', 'ajax_object',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
	}
	function WPIH_PreAction() {
		global $wpi_settings, $WPI_UI;
		
		if ( class_exists('WPI_Core') ) {
			
		}else {
			add_action('admin_notices', array($this, 'wpih_not_loaded'));
		}
		
	}
	function wpih_loaded() {
		
		printf(
		  '<div class="error"><p>%s</p></div>',
		  __('WPIH main Loaded')
		);
		
	}
	function wpih_not_loaded() {
		printf(
		  '<div class="error"><p>%s</p></div>',
		  __('Sorry WPI_UI is not loaded. Please activate WP-Invoice')
		);
	}

}
