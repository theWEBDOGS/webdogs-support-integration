<?php
/**
 * A unique identifier is defined to store the options in the database.
 *
 */

function optionsframework_option_name() {

	$name = 'WEBDOGS';
	$name = preg_replace("/\W/", "_", strtolower($name) );

	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $name;
	update_option('optionsframework', $optionsframework_settings);
}


/**
 * Register the required plugins for this theme.
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function wds_register_base_activation() {

	/* Array of plugin arrays. Required keys are name and slug.
	 */
	$plugins = wds_base_plugins();

	/* Array of themes arrays. Required keys are name and slug.
	 */
	$themes = wds_bundled_themes();

	/* Load has_plugin_notices option from framework.
	 * Show admin notices or not.
	 */
	$has_notices = Options_Framework_Utils::validate_bool( of_get_option( 'has_plugin_notices', true));

	/* Load has_forced_activation option from framework.
	 * Automatically activate plugins after installation or not.
	 */
	$is_automatic = Options_Framework_Utils::validate_bool( of_get_option( 'has_forced_activation', true));

	/* Array of strings used throughout the admin screens.
	 */
	$strings = wds_base_strings();

	/* Array of configuration settings. Amend each line as needed.
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 */
	$config = array(
		'id'           => 'optionsframework',       // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'optionsframework-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',           // Parent menu slug.
		'capability'   => 'manage_options',        // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => $has_notices,            // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => $is_automatic,           // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => $strings                 // Array of strings used throughout the admin screens.
	);

	Options_Framework_Register_Plugins( $plugins, $themes, $config );
}

add_action( 'optionsframework_register', 'wds_register_base_activation', 10 );


/**
 *
 * Return an array of recommended plugins
 * and plugins marked for deletion.
 *
 */
function wds_base_plugins(){

	return array(

		array(
			'name'      => 'WEBDOGS Support + Maintenance',
			'slug'      => 'webdogs-support-integration',
			'source'    => 'https://github.com/theWEBDOGS/webdogs-support-integration/archive/master.zip',
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => WEBDOGS_LATEST_VERSION, // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => 'https://github.com/theWEBDOGS/webdogs-support-integration',
		),
		array(
			'name'      => 'WATCHDOG',
			'slug'      => 'watchdog',
			'source'    => WEBDOGS_SUPPORT_DIR. '/watchdog/watchdog.zip',
			'file_path' => WPMU_PLUGIN_DIR . '/watchdog',
			'must_use'           => true, // If false, the plugin is only 'recommended' instead of required.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => 'https://github.com/theWEBDOGS/watchdog',
		),
		array(
			'name'      => 'Simple History',
			'slug'      => 'simple-history',
		),
		array(
			'name'      => 'Antispam Bee',
			'slug'      => 'antispam-bee',
		),
		array(
			'name'      => 'Google Analytics by MonsterInsights',
			'slug'      => 'google-analytics-for-wordpress',
		),
		array(
			'name'      => 'Yoast SEO',
			'slug'      => 'wordpress-seo',
		),
		array(
			'name'      => 'Gravity Forms',
			'slug'      => 'gravityforms',
			'source'    => 'https://github.com/wp-premium/gravityforms/archive/master.zip'
		),
		array(
			'name'      => 'Advanced Custom Fields',
			'slug'      => 'advanced-custom-fields',
		),
		array(
			'name'      => 'Format Media Titles',
			'slug'      => 'format-media-titles',
		),
		array(
			'name'      => 'Members',
			'slug'      => 'members',
		),
		array(
			'name'      => 'Admin Menu Editor',
			'slug'      => 'admin-menu-editor',
		),
		array(
			'name'      => 'Admin Columns',
			'slug'      => 'codepress-admin-columns',
		),
		array(
			'name'      => 'SVG Support',
			'slug'      => 'svg-support',
		),
		array(
			'name'      => 'Redirection',
			'slug'      => 'redirection',
		),
		array(
			'name'      => 'WEBDOGS Support',
			'slug'      => 'webdogs-support-dashboard-widget',
			'file_path' => 'webdogs-support-dashboard-widget/webdogs-support-dashboard-widget.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Akismet',
			'slug'      => 'akismet',
			'file_path' => 'akismet/akismet.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Hello Dolly',
			'slug'      => 'hello-dolly',
			'file_path' => 'hello.php',
			'force_deletion' => true, 
		)
	);
}


/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */

function optionsframework_options() {

	/////////////////////////
	//				       //
	// SETUP OPTION VALUES //
	//                     //
	/////////////////////////

	// Retrieve a list of all 
	// installed plugins (WP cached).
	$installed_plugins = get_plugins(); 

	$plugins = wds_base_plugins();
	 $themes = wds_bundled_themes(); 

	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
	$login_logo = $image[0];

	$login_logo_height_array = array(
		'100' => __('100px', 'options_check'),
		'200' => __('200px', 'options_check'),
		'300' => __('300px', 'options_check'));

	$service_array = array(
		'1' => __('Active', 'options_check'),
		'0' => __('On-Demand', 'options_check'),
	);

	$frequency_array = array(
		'1' => __('Monthly', 'options_check'),
		'4' => __('Quarterly', 'options_check'),
		'6' => __('Biannually', 'options_check'),
	);
	
	$day_offset = array();
		for ($i=1; $i < 29; $i++) { 
			$day_offset[$i] = $i;
		}

	$boolean_active = array(
		'yes' => __('Active', 'options_check'),
		'no' => __('Hidden', 'options_check'),
	);

	$boolean_radio = array(
		'yes' => __('Yes', 'options_check'),
		'no' => __('No', 'options_check'),
	);

	/////////////////////////////////
	//				               //
	// SETUP DYNAMIC DESCRIPTIONS  //
	//                             //
	/////////////////////////////////

	$active_deletion_notice = false;

	$delete_plugins = array();
	foreach ( $plugins as $slug => $plugin ) {
		if ( true === $plugin['force_deletion'] && ! empty( $installed_plugins[ $plugin['file_path'] ] ) ) {
			if ( is_plugin_active( $plugin['file_path'] ) ) {
				$active_deletion_notice = true;
				$delete_plugins[] = $plugin['name'] . '<span style="position: absolute;"><sup>*</sup></span>' ;
			} else { 
				$delete_plugins[] = $plugin['name'];
			}
		}
	}

	$recommend_plugins = array();
	foreach ( $plugins as $slug => $plugin ) {
		if ( true === $plugin['force_deletion'] ) continue;
		$recommend_plugins[] = $plugin['name'];
	}

	$delete_themes = array();
	foreach ( $themes as $slug => $theme ) { 
		if ( true === $theme['force_deletion'] ) {

			if ( true === $theme['active'] ) {
				$active_deletion_notice = true;
				$delete_themes[] = $theme['name'] . '<span style="position: absolute;"><sup>*</sup></span>' ;
			} else { 
				$delete_themes[] = $theme['name'];
			}
		}
	}

	$delete_base = "";

	if ( ! empty($delete_plugins) ) $delete_base .= '<strong>' . __('Plugins: ', 'options_check') . '</strong>' . implode(', ', $delete_plugins ) . "<br/><br/>";

	if ( ! empty($delete_themes)  ) $delete_base .= '<strong>' . __('Themes: ' , 'options_check') . '</strong>' . implode(', ', $delete_themes  );
	
	if ( ! empty($delete_base)    ) $delete_base .= '</p><p class="explain"><span><sup>*</sup></span><small>' . __(' Excludes active themes &amp; plugins' , 'options_check') . '</small>';

	$delete_base = empty($delete_base) ? "Nothing to cleanup." : $delete_base ;

	$exclude_domain = stripos( of_get_option( 'exclude_domain' ), site_url() ) !== false ? '<strong>Current domain:</strong> Excluded' : '<strong>Current domain:</strong> Not&nbsp;excluded';



	///////////////////////////
	//				         //
	// SETUP TABS AND FIELDS //
	//                       //
	///////////////////////////

	  $options = array();

	/*
	 *  NOTIFICATIONS | TAB 1
	 */

	$options[] = array(
		'name' => __('Notifications', 'options_check'),
		'capability'   => 'manage_options',
		'type' => 'heading');

	$options[] = array(
		'name' => __('Maintenance Service', 'options_check'),
		'id' => 'active_maintenance_customer',
		'std' => '0',
		'type' => 'select',
		'class' => 'small alignleft mini',
		'options' => $service_array); 

	$options[] = array(
		'name' => __('Notification Frequency', 'options_check'),
		'id' => 'maintenance_notification_frequency',
		'std' => '1',
		'type' => 'radio',
		'class' => 'alignleft inline', 
		'options' => $frequency_array);

	$options[] = array(
		'name' => __('Delivery Offset', 'options_check'),
		'desc' => __('Day of the month', 'options_check'),
		'id' => 'maintenance_notification_offset',
		'std' => '1',
		'type' => 'select',
		'class' => 'mini alignleft', 
		'options' => $day_offset);

	$options[] = array(
		'name' => __('Primary Customer Email Address', 'options_check'),
		'desc' => __('Include multiple recipients, separeted by comma.', 'options_check'),
		'id' => 'on_demand_email',
		'std' => '',
		'class' => 'clear bottom-pad top-border inset',
		'type' => 'text',
		'rule' => array(
			'id' => 'active_maintenance_customer',
			'on' => 'change',
			'set' => array(
				'slideDown' => '0',
				'slideUp' => '1')));

	$options[] = array(
		'id' => 'maintenance_notes_wrap',
		'type' => 'info',
		'wrap' => array( 
			'start' => true, 
			'class' => 'clear top-border'));

	$options[] = array(
		'name' => __('Maintenance Instructions', 'options_check'),
		'id' => 'maintenance_notes',
		'class' => 'clear alignleft',
		'type' => 'textarea');

	$options[] = array(
		'name' => __('Exclusionary Keyword', 'options_check'),
		'desc' => $exclude_domain,
		'id' => 'exclude_domain',
		'std' => 'staging',
		'class' => 'alignleft mini', 
		'type' => 'text');

	$options[] = array(
		'type' => 'info',
		'wrap' => array( 
			'end' => true)
		);



	/*
	 *  SETTINGS | TAB 2
	 */

	$options[] = array(
		'name' => __('Settings', 'options_check'),
		'capability'   => 'manage_options',
		'type' => 'heading');

	$options[] = array(
		'name' => 'Recommended Plugins',
		'desc' => implode(', ', $recommend_plugins ),
		'type' => 'info',
		'class' => 'small alignleft bottom-pad');

	$options[] = array(
		'name' => __('Display Plugin Recommendation Notice', 'options_check'),
		'id' => 'has_plugin_notices',
		'type' => 'radio',
		'std' => 'yes',
		'class' => 'inline alignleft',
		'options' => $boolean_active);

	$options[] = array(
		'name' => __('Automatically Activate Plugin After Installation', 'options_check'),
		'id' => 'has_forced_activation',
		'type' => 'radio',
		'std' => 'yes',
		'class' => 'alignleft inline',
		'options' => $boolean_radio);
	
	$options[] = array(
		'name' => 'Cleanup Core Bundles',
		'type' => 'info',		
		'desc' => $delete_base,
		'class' => 'alignleft small',
		'wrap' => array( 
			'start' => true, 
			'class' => 'clear top-border inset bottom-pad',));

	$options[] = array(
		'name' => __('Remove Bundled Plugins', 'options_check'),
		'id' => 'has_forced_deletion',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Remove Bundled Themes', 'options_check'),
		'id' => 'has_theme_deletion',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'wrap' => array( 
			'end' => true));



	/*
	 *  PLUGINS | TAB 3
	 */

	/*$options[] = array(
		'name' => __('Plugins', 'options_check'),
		'capability'  => 'manage_options',
		'type' => 'heading',
		'class' => 'inset bottom-pad',
		'function' => 'Options_Framework_Install_Plugins_Page' );*/ 
		


	/*
	 *  LOGO OPTIONS | TAB 3
	 */

	$options[] = array(
		'name' => __('Logo Options', 'options_check'),
		'capability' => 'manage_options',
		'type' => 'heading');

	$background_defaults = array(
		'color' => '',
		'image' => $login_logo,
		'repeat' => 'no-repeat',
		'position' => 'bottom center',
		'attachment' => 'scroll' );

	$options[] = array(
		'name' => __('Login Logo and Background', 'options_check'),
		'id' => 'login_logo_css',
		'std' => $background_defaults,
		'type' => 'background');

	$options[] = array(
		'desc' => __('Logo Height', 'options_check'),
		'id' => 'login_logo_height',
		'std' => '100',
		'type' => 'select',
		'class' => 'mini alignright inline', 
		'options' => $login_logo_height_array);

	$options[] = array(
		'type' => 'info',
		'class' => 'clear');


	return $options;
}

add_filter( 'of_options', 'optionsframework_options');


/**
 *
 * Determine which bundled themes are 
 * installed and mMark them for deletion.
 *
 */
function wds_bundled_themes(){

	$themes = wp_prepare_themes_for_js();

	$marked_themes = array();

	foreach ($themes as $theme) {
	    if( 'the WordPress team' !== $theme['author'] ) { continue; }
	    
	    $marked_themes[] = array(
			'name'           => $theme['name'],
			'slug'           => $theme['id'],
			'active'         => $theme['active'],
			'force_deletion' => true,
		);
	}
	return $marked_themes;
}

/**
 *
 * Determine which bundled themes are 
 * installed and mMark them for deletion.
 *
 */
function wds_base_strings(){

	return  array(
		'page_title'                      => __( 'Install Recommended Plugins', 'webdogs-support' ),
		'menu_title'                      => __( 'Install Plugins', 'webdogs-support' ),
		'installing'                      => __( 'Installing Plugin: %s', 'webdogs-support' ), // %s = plugin name.
		'oops'                            => __( 'Something went wrong with the plugin API.', 'webdogs-support' ),
		'notice_can_install_required'     => _n_noop(
			'Required plugin: %1$s.',
			'Required plugins: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_install_recommended'  => _n_noop(
			'Recommended plugin: %1$s.',
			'Recommended plugins: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_install'           => _n_noop(
			'Sorry, but you do not have the correct permissions to install the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to install the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_ask_to_update'            => _n_noop(
			'The following plugin needs to be updated to its latest version to ensure maximum compatibility: %1$s.',
			'The following plugins need to be updated to their latest version to ensure maximum compatibility: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_ask_to_update_maybe'      => _n_noop(
			'Update available for: %1$s.',
			'Updates available for: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_update'            => _n_noop(
			'Sorry, but you do not have the correct permissions to update the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to update the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_activate_required'    => _n_noop(
			'The following required plugin is currently inactive: %1$s.',
			'The following required plugins are currently inactive: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_activate_recommended' => _n_noop(
			'The following recommended plugin is currently inactive: %1$s.',
			'The following recommended plugins are currently inactive: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_activate'          => _n_noop(
			'Sorry, but you do not have the correct permissions to activate the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to activate the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'install_link'                    => _n_noop(
			'Install plugin',
			'Install plugins',
			'webdogs-support'
		),
		'update_link' 					  => _n_noop(
			'Update plugin',
			'Update plugins',
			'webdogs-support'
		),
		'activate_link'                   => _n_noop(
			'Activate plugin',
			'Activate plugins',
			'webdogs-support'
		),
		'plugin_deletion'                 => _n_noop( 
			'The following plugin has been removed: %1$s.', 
			'The following plugins have been removed: %1$s.', 
			'webdogs-support' 
		),
		'theme_deletion'      => _n_noop( 
			'The following theme has been removed: %s1$.', 
			'The following themes have been removed: %1$s.', 
			'webdogs-support' 
		),
		'return'                          => __( 'Go back to Base Plugins Installer', 'webdogs-support' ),
		'plugin_activated'                => __( 'Plugin activated successfully.', 'webdogs-support' ),
		'activated_successfully'          => __( 'The following plugin was activated successfully:', 'webdogs-support' ),
		'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'webdogs-support' ),  // %1$s = plugin name(s).
		'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed. Please update the plugin.', 'webdogs-support' ),  // %1$s = plugin name(s).
		'complete'                        => __( 'All plugins and themes have been installed and activated successfully. %1$s', 'webdogs-support' ), // %s = dashboard link.
		'contact_admin'                   => __( 'Please contact WEBDOGS for support.', 'webdogs-support' ),

		'nag_type'                        => 'webdogs-nag', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
	);
}

/**
 * Filter fields and tabs by capability.
 *
 */

function wds_filter_options_capability($options=array()) {
    
    if(empty($options)) return $options;

    $capability = array();
       $counter = 0;

         $clean = array();

    foreach ( $options as $value ) {

        $cap = false;
        $prev_cap = ( isset( $capability[ $counter ] ) && ! empty( $capability[ $counter ] ) ) ? $capability[ $counter ] : false ;
        
        if ( $value['type'] === "heading" ) {
            ++$counter;
        }
        
        if ( isset( $value['capability'] ) 
        && ! empty( $value['capability'] ) ) {

            $cap = $value['capability'];

            if ( $value['type'] = "heading" ) {
                $capability[ $counter ] = $cap;
            }
        }
        if ( isset( $capability[ $counter ] ) 
        && ! empty( $capability[ $counter ] ) ) {
            $cap = $capability[ $counter ];
        }

        // Check capability. Continue if user not incapable
        if( $cap && ! current_user_can( $cap ) ) {
            continue;
            // unset( $options[ $key ] );
        }
        $clean[]=$value;
    }
    return $clean;

}

add_filter( 'of_options', 'wds_filter_options_capability', 20, 1 );