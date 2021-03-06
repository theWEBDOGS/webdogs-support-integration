<?php

defined( 'WPINC' ) or die;
/**
 * @package   Webdogs
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

class Webdogs_Admin {

	/**
     * Page hook for the options screen
     *
     * @since 1.7.0
     * @type string
     */
    protected $options_screen = null;

    /**
     * Hook in the scripts and styles
     *
     * @since 1.7.0
     */
    public function init() {

		// Gets options to load
    	$options = &Webdogs_Options::_wds_options();

		// Checks if options are available
    	if ( $options ) {

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_custom_options_page' ) );

			// Add the required scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Settings need to be registered after admin_init
			add_action( 'admin_init', array( $this, 'settings_init' ) );

			// Adds options menu to the admin bar
			add_action( 'wp_before_admin_bar_render', array( $this, 'wds_admin_bar' ) );

			add_action( 'wp_before_admin_bar_render', array( $this, 'add_adminbar_sitename_logo' ) );

		} else {
			// Display a notice if options aren't present in the theme
			add_action( 'admin_notices', array( $this, 'options_notice' ) );
			add_action( 'admin_init', array( $this, 'options_notice_ignore' ) );
		}

    }

	/**
     * Let's the user know that options aren't available for their theme
     */
    public function options_notice() {
		global $pagenow;
        if ( !is_multisite() && ( $pagenow == 'plugins.php' || $pagenow == 'themes.php' ) ) {
			global $current_user ;
			$user_id = $current_user->ID;
			if ( ! get_user_meta($user_id, 'wds_ignore_notice') ) {
				echo '<div class="updated wds_setup_nag"><p>';
				printf( __('Your current theme does not have support for the Options Framework plugin.  <a href="%1$s" target="_blank">Learn More</a> | <a href="%2$s">Hide Notice</a>', 'webdogs-support' ), 'http://wptheming.com/options-framework-plugin', '?wds_nag_ignore=0');
				echo "</p></div>";
			}
        }
	}

	/**
     * Allows the user to hide the options notice
     */
	public function options_notice_ignore() {
		global $current_user;
		$user_id = $current_user->ID;
		if ( isset( $_GET['wds_nag_ignore'] ) && '0' == $_GET['wds_nag_ignore'] ) {
			add_user_meta( $user_id, 'wds_ignore_notice', 'true', true );
		}
	}

	/**
     * Registers the settings
     *
     * @since 1.7.0
     */
    public function settings_init() {

    	// Load Options Framework Settings
        $wds_settings = get_option( 'webdogs_support' );

        // Update options name for compatibility
        $previous_options = get_option( 'webdogs', false );
        
        // If there are options here, 
        // save them with the new name 
        // and delete the previous options.
        if( $previous_options ) {
        	update_option( $wds_settings['id'], $previous_options );
        	delete_option( 'webdogs' );
        }

		// Registers the settings fields and callback
		register_setting( 'webdogs-support', $wds_settings['id'],  array( $this, 'validate_options' ) );

		// Displays notice after options save
		add_action( 'wds_after_validate', array( $this, 'save_options_notice' ) );

		if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) {
			add_action( 'current_screen', 'wds_maybe_clear_cache', 10, 1 );
		}

    }

	/*
	 * Define menu options (still limited to appearance section)
	 *
	 * Examples usage:
	 *
	 * add_filter( 'wds_menu', function( $menu ) {
	 *     $menu['page_title'] = 'The Options';
	 *	   $menu['menu_title'] = 'The Options';
	 *     return $menu;
	 * });
	 *
	 * @since 1.7.0
	 *
	 */
	static function menu_settings() {

		$menu = array(

			// Modes: submenu, menu
            'mode' => 'menu',

            // Submenu default settings
            'page_title' => __('Support', 'webdogs-support'),
			'menu_title' => __('Support', 'webdogs-support'),
			'capability' => 'manage_support',
			'menu_slug' => 'webdogs-support',
            'parent_slug' => 'admin.php',

            // Menu default settings
            'icon_url' => wd_get_icon_logo( '#FFFFFF', true, true ),
            'dashicon' => 'dashicons-sos',
            'position' => '61'

		);

		return apply_filters( 'wds_menu', $menu );
	}

	/**
     * Add a subpage called "Theme Options" to the appearance menu.
     *
     * @since 1.7.0
     */
	public function add_custom_options_page() {

		$menu = Self::menu_settings();

        switch( $menu['mode'] ) {

            case 'menu':
            	// http://codex.wordpress.org/Function_Reference/add_menu_page
                $this->options_screen = add_menu_page(
                	$menu['page_title'],
                	$menu['menu_title'],
                	$menu['capability'],
                	$menu['menu_slug'],
                	// null,
                	array( $this, 'options_page' ),
                	$menu['icon_url'],
                	$menu['position']
                );
                break;

            default:
            	// http://codex.wordpress.org/Function_Reference/add_submenu_page
                $this->options_screen = add_submenu_page(
                	$menu['parent_slug'],
                	$menu['page_title'],
                	$menu['menu_title'],
                	$menu['capability'],
                	$menu['menu_slug'],
                	array( $this, 'options_page' ) );
                break;
        }
	}

	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function wds_tabs() {
		$counter = 0;
		$options = & Webdogs_Options::_wds_options();
		$options = apply_filters( 'wds_options', $options );
		$menu = array();

		$indexes = array();

		foreach (array_values( $options ) as $option) {
			if( empty( $option['order'] ) ) 
				continue;
			else
				$indexes[] = absint( $option['order'] );
		}

		foreach ( $options as $value ) {
			// Heading for Navigation
			if ( $value['type'] == "heading" ) {

				$counter++;

				if( isset( $value['order'] ) ) {
					$index = $value['order'];

				} elseif( !in_array( $counter, $indexes ) ) {
					$index = $counter;
					$indexes[] = $counter;

				} else {
					while ( in_array( $counter, $indexes )) {
						$counter++; }
					$index = $counter;
					$indexes[] = $counter;
				}
		
				$class = '';
				$class = $value['name'];
				$class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) );
				$menu[ $index ] = array_merge( $value, array('menu_slug' => $class, 'tab' => '#'. $class ."-section" ) );
			}
		}
		// sort numeric 
		// for custom ordering. 
		// from disordered source.
		ksort( $menu, SORT_NUMERIC );

		return $menu;
	}


	public function get_logo_icon() {

	    $custom_logo_icon = wds_get_option( 'logo_icon', false );

	    // return $custom_logo_icon;
	    $color = 'currentColor';

	    if( $custom_logo_icon ) {
	    	// not an svg
	    	// use image
	    	if( stripos( $custom_logo_icon, '.svg') === false ) {

	    		$image = $custom_logo_icon;

    		//eles use SVG
	    	} else {

		    	$data = file_get_contents( $custom_logo_icon );
		    	// return $data;`

		    	if( ! $data ) { return false; }

				// return $data;
				// replace `fill` attributes
				$data = preg_replace( '/fill=".+?"/', 'fill="' . $color . '"', $data );

				// return $data;
				// replace `style` attributes
				$data = preg_replace( '/style=".+?"/', 'style="fill:' . $color . '"', $data );

				// return $data;
				// replace `fill` properties in `<style>` tags
				$data = preg_replace( '/fill:.*?;/', 'fill: ' . $color . ';', $data );

		    	// $custom_image = "<span class=\"ab-icon svg\">{$data}</span>";

				// return $custom_image;

	            $image = 'data:image/svg+xml;base64,' . base64_encode( $data );
	        }

	    	// $custom_image = "<img src=\"{$data_image}\" height=\"26\" width=\"20\">";
	    	$custom_image = "<span class=\"ab-item ab-icon svg\" style=\"background-image:url({$image}) !important;\"></span>";
	    	// $custom_image = "<div class='wp-menu-image svg' style=\"background-image:url({$data_image}) !important; width:20px; background-repeat:no-repeat; height:26px; background-position:center !important;\"></div>";

	    	return $custom_image;
	    }
	    return false;
	}

	/*
	 * Change the WP Logo Icon within the My Sites Menu to any icon you want
	 * Update the NEW-ICON-HERE.png name to match the proper file name.
	 */
	public function add_adminbar_sitename_logo() {

		global $wp_admin_bar;

		add_filter('wds_adminbar_sitename', function( $sitename ) { 
			$flags = ( wds_is_staging_site() ) ? ' | staging' : '';
				return $sitename . $flags ; }, 12, 1 );


	    // Don't show for logged out users.
	    if ( ! is_user_logged_in() )
	        return;
	 
	    // Show only when the user is a member of this site, or they're a super admin.
	    if ( ! is_user_member_of_blog() && ! is_super_admin() )
	        return;
	 
	    $blogname = get_bloginfo('name');
	 
	    if ( ! $blogname ) {
	        $blogname = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
	    }
	 
	    if ( is_network_admin() ) {
	        $blogname = sprintf( __('Network Admin: %s'), esc_html( get_current_site()->site_name ) );
	    } elseif ( is_user_admin() ) {
	        $blogname = sprintf( __('User Dashboard: %s'), esc_html( get_current_site()->site_name ) );
	    }
	 
	    $title = "";	    
	    $title .= wp_html_excerpt( $blogname, 40, '&hellip;' );
	 
	    $wp_admin_bar->add_menu( array(
	        'id'    => 'site-name',
	        'title' => apply_filters( 'wds_adminbar_sitename', $title ),
	        'href'  => ( is_admin() || ! current_user_can( 'read' ) ) ? home_url( '/' ) : admin_url(),
	    ) );
	 
	    // Create submenu items.
	 
	    if ( is_admin() ) {
	        // Add an option to visit the site.
	        $wp_admin_bar->add_menu( array(
	            'parent' => 'site-name',
	            'id'     => 'view-site',
	            'title'  => __( 'Visit Site' ),
	            'href'   => home_url( '/' ),
	        ) );
	 
	        if ( is_blog_admin() && is_multisite() && current_user_can( 'manage_sites' ) ) {
	            $wp_admin_bar->add_menu( array(
	                'parent' => 'site-name',
	                'id'     => 'edit-site',
	                'title'  => __( 'Edit Site' ),
	                'href'   => network_admin_url( 'site-info.php?id=' . get_current_blog_id() ),
	            ) );
	        }
	 
	    } else if ( current_user_can( 'read' ) ) {
	        // We're on the front end, link to the Dashboard.
	        $wp_admin_bar->add_menu( array(
	            'parent' => 'site-name',
	            'id'     => 'dashboard',
	            'title'  => __( 'Dashboard' ),
	            'href'   => admin_url(),
	        ) );
	 
	        // Add the appearance submenu items.
	        wp_admin_bar_appearance_menu( $wp_admin_bar );
	    }
	}


	/**
     * Loads the required stylesheets
     *
     * @since 1.7.0
     */
	function enqueue_admin_styles( $hook ) {  ?>


		<style type="text/css">
			body {
				background-color: transparent !important;
			}
			#wpadminbar .ab-top-menu>.menupop>.ab-sub-wrapper {
			    min-width: initial !important;
			}
			#adminmenu #toplevel_page_webdogs-support div.wp-menu-image.svg {
			    -webkit-background-size: 26px 26px;
			    background-size: 26px 26px;
			    height: 34px;
			}
			#wpadminbar .ab-top-secondary #wp-admin-bar-wds_theme_options.menupop .ab-sub-wrapper {
			    right: auto;
			    left: -75%;
			}
			#wpadminbar #wp-admin-bar-wds_theme_options .ab-icon:before {
				top:2px;
			}
			#toplevel_page_webdogs-support .wp-menu-image.dashicons-before img {
				height: 28px;
			    width: 28px;
			    padding-top: 2px;
			    margin-left: -3px;
			}
			#update-nag, .update-nag {
				display: block !important;
			}
			
		</style>


		<?php

		if ( $this->options_screen != $hook ) return;


		wp_enqueue_style( 'login' );
		wp_enqueue_style( 'forms' );
		// wp_enqueue_style( 'webdogs-support', plugin_dir_url( dirname(__FILE__) ) . 'admmin/css/optionsframework.css', array(),  Webdogs_Options::VERSION );
		wp_enqueue_style( 'wp-color-picker' );
	}


	public function get_current_tab(){
		$tabs = Self::wds_tabs();

        foreach ( $tabs as $tab ) {

        	if( ! empty( $tab['active_tab'] ) && function_exists( $tab['active_tab'] ) ) {
        		
        		add_filter( 'wds_filter_active_tab', $tab['active_tab'], 10, 1 );
				
				$is_current = apply_filters( 'wds_filter_active_tab', false );
	    	
	    		if( $is_current ) {
	    			return $tab['tab'];
	    		}

        	}


		}
		return false;
	}

	/**
     * localize_script
     *
     * @since 2.3.4
     */
	public function localize_script( $localize_script = array() ) {
		if( $this->get_current_tab() ) {
			$current = $this->get_current_tab();
			return $localize_script + array( 'tab' => $current );
		}
		return $localize_script;
	}

	/**
     * Loads the required javascript
     *
     * @since 1.7.0
     */
	public function enqueue_admin_scripts( $hook ) {

		if ( $this->options_screen != $hook )
	        return;

		wp_enqueue_script( WEBDOGS_SUPPORT_ID . '-svg-icon-font', plugin_dir_url( dirname(__FILE__) ) . 'admin/js/svgiconfont.js', array(), Webdogs_Options::VERSION, true );

		// wp_enqueue_script( 'jquery-parallaxify', plugin_dir_url( dirname(__FILE__) ) . 'js/jquery.parallaxify.min.js', array( 'jquery' ), Webdogs_Options::VERSION, false );
		wp_enqueue_script( WEBDOGS_SUPPORT_ID . '-sass', plugin_dir_url( dirname(__FILE__) ) . 'admin/js/sass.js', array(), Webdogs_Options::VERSION, false );
		
		// Enqueue custom option panel JS
		wp_enqueue_script( WEBDOGS_SUPPORT_ID . '-options-custom', plugin_dir_url( dirname(__FILE__) ) . 'admin/js/options-custom.js', array( 'jquery','wp-color-picker' ), Webdogs_Options::VERSION, true );

		wp_enqueue_script( 'admin-color-schemes', plugin_dir_url( dirname(__FILE__) ) . 'admin/js/admin-color-schemes.js', array( 'jquery', 'wp-color-picker' ), Webdogs_Options::VERSION, true );
		
		// Inline scripts from options-interface.php
		add_action( 'admin_head', array( $this, 'wds_admin_head' ) );


	}

	public function wds_admin_head() {
		// Hook to add custom scripts
		do_action( 'wds_custom_scripts' );
	}



	/**
	 * Display settings errors and remove those which have been displayed to avoid duplicate messages showing
	 *
	 * @since 2.5.0
	 */
	protected function display_settings_errors() {
		global $wp_settings_errors;

		settings_errors( 'webdogs-support' );

		foreach ( (array) $wp_settings_errors as $key => $details ) {
			if ( 'webdogs-support' === $details['setting'] ) {
				unset( $wp_settings_errors[ $key ] );
				break;
			}
		}
	}

	/**
     * Builds out the options panel.
     *
	 * If we were using the Settings API as it was intended we would use
	 * do_settings_sections here.  But as we don't want the settings wrapped in a table,
	 * we'll call our own custom wds_fields.  See options-interface.php
	 * for specifics on how each individual field is generated.
	 *
	 * Nonces are provided using the settings_fields()
	 *
     * @since 1.7.0
     */
	public function options_page() { ?>

	<div id="wds-wrap" class="wrap">
		
		<?php //Webdogs_Support_Admin_Color_Schemes::get_Sass_JS(); ?>

		<?php $menu = Self::menu_settings(); ?>

		<h1><?php echo esc_html( $menu['page_title'] ); ?> <span class="subtitle alignright">v<?php print WEBDOGS_VERSION; ?></span></h1>

	    <?php $this->display_settings_errors(); ?>

	    <h2 class="nav-tab-wrapper">
	        <?php echo Webdogs_Interface::wds_tabs(); ?>
	    </h2>
	    <div id="wds-metabox" class="metabox-holder">
		    <div id="optionsframework" class="postbox">
				<?php /*settings_fields( 'webdogs-support' );*/ ?>
				<?php Webdogs_Interface::wds_fields(); /* Settings */ ?>
				
				
			</div> <!-- / #container -->
		</div>
		<?php do_action( 'wds_after' ); ?>
	</div> <!-- / .wrap -->

	<?php
	}

	/**
	 * Validate Options.
	 *
	 * This runs after the submit/reset button has been clicked and
	 * validates the inputs.
	 *
	 * @uses $_POST['reset'] to restore default options
	 */
	public function validate_options( $input ) {

		/*
		 * Restore Defaults.
		 *
		 * In the event that the user clicked the "Restore Defaults"
		 * button, the options defined in the theme's options.php
		 * file will be added to the option for the active theme.
		 */

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'webdogs-support', 'restore_defaults', __( 'Default options restored.', 'webdogs-support' ), 'updated fade' );
			return $this->get_default_values();
		}

		/*
		 * Update Settings
		 *
		 * This used to check for $_POST['update'], but has been updated
		 * to be compatible with the theme customizer introduced in WordPress 3.4
		 */


		$config = get_option( 'webdogs_support' );
		$clean  = isset( $config['id'] ) ? get_option( $config['id'] ) : array() ;
		$options = & Webdogs_Options::_wds_options();
		foreach ( $options as $option ) {

			if ( ! isset( $option['id'] ) ) {
				continue;
			}

			if ( ! isset( $option['type'] ) ) {
				continue;
			}

			$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
				$input[$id] = false;
			}

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'scheme' == $option['type'] && ! isset( $input[$id]['must_use'] ) ) {
				$input[$id]['must_use'] = false;
			}

			// Set each item in the multicheck to false if it wasn't sent in the $_POST
			if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
				foreach ( $option['options'] as $key => $value ) {
					$input[$id][$key] = false;
				}
			}

			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'wds_sanitize_' . $option['type'] ) ) {
				$clean[$id] = apply_filters( 'wds_sanitize_' . $option['type'], $input[$id], $option );
			}
		}

		// Hook to run after validation
		do_action( 'wds_after_validate', $clean );

		return $clean;
	}


    // if ( ! isset( $config['id'] ) ) {
    //     return $default;
    // }

    // $options = get_option( $config['id'] );

    // if ( isset( $options[$name] ) ) {
    //     return $options[$name];
    // }

    // return $default;

	/**
	 * Display message when options have been saved
	 */

	public function save_options_notice() {
		add_settings_error( 'webdogs-support', 'save_options', __( 'Options saved.', 'webdogs-support' ), 'updated fade' );
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */

	public function get_default_values() {
		$output = array();
		$config = & Webdogs_Options::_wds_options();
		foreach ( (array) $config as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
			if ( ! isset( $option['std'] ) ) {
				continue;
			}
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
			if ( has_filter( 'wds_sanitize_' . $option['type'] ) ) {
				$output[$option['id']] = apply_filters( 'wds_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
		return $output;
	}

	/**
	 * Add options menu item to admin bar
	 */

	public function wds_admin_bar() {

		// Don't show for logged out users.
	    if ( ! is_user_logged_in() )
	        return;
	 
	    // Show only when the user is a member of this site, or they're a super admin.
	    if ( ( ! is_user_member_of_blog() && ! is_super_admin() ) || ! current_user_can( 'manage_support' ) )
	        return;

		$menu = Self::menu_settings();

		global $wp_admin_bar;

		if ( 'menu' == $menu['mode'] ) {
			$href = admin_url(  'admin.php?page=' . $menu['menu_slug'] );
		} else {
			$href = admin_url( 'themes.php?page=' . $menu['menu_slug'] );
		}

		$icon_format = '<span class="ab-icon %s"></span><span class="ab-label">%s</span>';

		$menu_title = ( !empty( $menu['dashicon'] ) ) ? sprintf($icon_format, $menu['dashicon'], $menu['menu_title']) : $menu['menu_title'];

		$args = array(
			'parent' => 'top-secondary',
			'id' => 'wds_theme_options',
			'title' => $menu_title,
			'href' => $href
		);

		$wp_admin_bar->add_menu( apply_filters( 'wds_admin_bar', $args ) );
		

		global $wpengine_platform_config;

		if( ! class_exists('WPE_Environment_Switch') && class_exists( 'WpeCommon' ) && ( $wpengine_platform_config['all_domains'][0] || ( defined('PWP_NAME') && PWP_NAME ) ) ) {
		    
		    // Format string for sprintf( 'Go to %1$s', $environment )
		    $meun_title      = apply_filters( 'wds_quicklink_title', 'Go to %1$s' );

			$wpecommon       = WpeCommon::instance();
			$snapshot_info   = $wpecommon->get_staging_status();

			if( empty( $wpengine_platform_config['all_domains'][0] ) && defined('PWP_NAME') && PWP_NAME ) {
				$production_url = sprintf( 'http://%1$s.wpengine.com', PWP_NAME );
			} else {
				$production_url = 'http://' . $wpengine_platform_config['all_domains'][0];
			}

			$staging_url     = @$snapshot_info['staging_url'];

			$request_uri     = "$_SERVER[REQUEST_URI]";

			$args            = array();

			if( ! is_wpe_snapshot() && $snapshot_info['have_snapshot'] && $snapshot_info['is_ready'] && $staging_url ) {
				
				$args = array(
					'parent' => 'wds_theme_options',
					'id' => 'wpe_environment',
					'title' => "View Page on Staging",
					'href' => $this->maybe_ssl( "$staging_url$request_uri" ),
					'meta'=>array('target' => '_blank')
				);
			}
			elseif ( is_wpe_snapshot() ) {

				$args = array(
					'parent' => 'wds_theme_options',
					'id' => 'wpe_environment',
					'title' => "Open page on Production",
					'href' => $this->maybe_ssl( "$production_url$request_uri" ),
					'meta'=>array('target' => '_blank')
				);
			}

			if( !empty( $args ) ) {

				$wp_admin_bar->add_menu( apply_filters( 'wds_admin_bar_environment_submenu', $args ) );

			}
		}


		$args = array(
			'id'     => 'maintenance_notification',
			'parent' => 'wds_theme_options',
			'meta'   => array( 'class' => 'first-toolbar-group' )
		);
		$wp_admin_bar->add_group( $args );
		
		$args = array(
			'parent' => 'maintenance_notification',
			'id' => 'maintenance_notification_test',
			'title' => 'Test Maintenance Notification',
			'href' => add_query_arg( 'wds_send_maintenance_notification', 'test', $href )
		);

		$wp_admin_bar->add_menu( apply_filters( 'wds_admin_bar_maintenance_notification_test_submenu', $args ) );

		$args = array(
			'parent' => 'maintenance_notification',
			'id' => 'maintenance_notification_force',
			'title' => 'Test Maintenance Notification Email',
			'href' => add_query_arg( array( 'wds_send_maintenance_notification'=>'test', 'force_send'=>'force'), $href )
		);

		$wp_admin_bar->add_menu( apply_filters( 'wds_admin_bar_maintenance_notification_force_submenu', $args ) );




		$args = array(
			'id'     => 'plugin_recomendation',
			'parent' => 'wds_theme_options',
			'meta'   => array( 'class' => 'second-toolbar-group' )
		);
		$wp_admin_bar->add_group( $args );

		$plugin_activation = Webdogs_Plugin_Activation::get_instance();

		if( empty( $plugin_activation->strings['menu_title'] ) ) {
			wds_register_base_activation();
		}

		$href = $plugin_activation->get_wds_url();
		
		$args = array(
			'parent' => 'plugin_recomendation',
			'id' => $plugin_activation->menu,
			'title' => $plugin_activation->strings['menu_title'],
			'href' => $href
		);

		$wp_admin_bar->add_menu( apply_filters( 'wds_admin_bar_plugin_activation_submenu', $args ) );
			
	}

	private function maybe_ssl( $url ) {
		if ( is_ssl() )
			$url = preg_replace( '#^http://#', 'https://', $url );
		return $url;
	}

}
