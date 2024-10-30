<?php
/*
* Plugin Name: Kalimah Dashboard
* Plugin URI: http://kalimah-apps.com/
* Description: Improve WordPress dashboard style and functions
* Version: 1.0.3
* Author: Kalimah Apps
* Text Domain: kalimah-dashboard
* Author URI: NONE
* License: GPLv2 or later
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*/

require "conf.php";
require "settings.php";


class kalimah_admin
{
    /* Save plugins dir */
    var $plugin_dir;
    var $lessify;
    var $options;
    var $flat_colors;
    var $material_colors;
    var $settings;

    /**
     * kalimah_admin::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->options = get_option('kalimah_admin_settings');

        $this->lessify = new lessc;
        $this->settings = new kalimah_admin_settings();

        add_action('admin_enqueue_scripts', array($this, 'kalimah_admin_enque_styles'));


        // Add Filter Hook
        add_filter('post_mime_types', array($this, 'kalimah_admin_add_post_mime_types'));

        add_action('admin_bar_menu', array($this, 'wp_admin_bar_my_custom_account_menu'),
            11);

        // remove WP logo from adminbar
        add_action('wp_before_admin_bar_render', array($this, 'kalimah_admin_render_admin_bar'));

        // Add custom styles to our login page
        add_action('login_enqueue_scripts', array($this, 'kalimah_admin_login_style'));


        // add an extra heading
        if ($this->settings->kalimah_admin_get_setting("kalimah_admin_theme_type") == "material")
            add_action('in_admin_header', array($this, 'kalimah_admin_add_heading'));
        else
            add_action('admin_menu', array($this, 'kalimah_admin_add_menu_item'));

        // Add initial values after plugin activation
        register_activation_hook(__file__, array($this, 'kalimah_admin_update_plugin_default_options'));
    }


    /**
     * kalimah_admin::kalimah_admin_update_plugin_default_options()
     * 
     * @return
     */
    function kalimah_admin_update_plugin_default_options()
    {
        $defaults["kalimah_admin_theme_type"] = "material";
        $defaults["kalimah_admin_flat_colors"] = "fourth_color";
        $defaults["kalimah_admin_material_colors"] = "first_color";

        update_option('kalimah_admin_settings', $defaults);
    }


    /**
     * kalimah_admin::kalimah_admin_add_menu_item()
     * 
     * @return
     */
    function kalimah_admin_add_menu_item()
    {
        $logo = $this->settings->kalimah_admin_get_setting('kalimah_admin_upload_logo');
        add_menu_page('', "<a href='" . admin_url() .
            "'><img class='dahsboard-site-brand' src='{$logo}'></a><span class='toggle-sidemenu'>",
            'add_users', 'admin_menu_logo', '', 'none', -9999);
    }

    /**
     * Add post media types to media manager
     * 
     * @param mixed $post_mime_types
     * @return
     */
    function kalimah_admin_add_post_mime_types($post_mime_types)
    {
        $post_mime_types['application/pdf'] = array(
            'PDF',
            'Manage PDFs',
            _n_noop('PDF <span class="count">(%s)</span>',
                'PDFs <span class="count">(%s)</span>'));
        $post_mime_types['application/msword'] = array(
            'Word Docs',
            'Manage Word Docs',
            _n_noop('Word DOC <span class="count">(%s)</span>',
                'Word Docs <span class="count">(%s)</span>'));
		$post_mime_types['application/zip'] = array(
            'ZIP Files',
            'Manage ZIP Files',
            _n_noop('ZIP File <span class="count">(%s)</span>',
                'ZIP Files <span class="count">(%s)</span>'));
        return $post_mime_types;
    }


    /**
     * Enqeue styles and javascript files
     * 
     * @return
     */
    function kalimah_admin_enque_styles()
    {
        wp_enqueue_style('admin-settings', plugins_url('css/settings.css', __file__),
            array(), '', 'all');
        wp_enqueue_style('font-awesome', plugins_url('css/font-awesome.css', __file__),
            array(), '', 'all');

        wp_enqueue_script('kalimah-js', plugins_url('js/kalimah-js.js', __file__), array
            ('jquery'), '', 'all');


        if ($this->settings->kalimah_admin_get_setting("kalimah_admin_theme_type") == 'material')
            wp_enqueue_style('kalimah-dashboard', plugins_url('css/style-material.css', __file__),
                array(), '', 'all');
        else
            wp_enqueue_style('kalimah-dashboard', plugins_url('css/style-flat.css', __file__),
                array(), '', 'all');

        // Add custom css
        wp_add_inline_style('kalimah-dashboard', $this->options['kalimah_admin_custom_css']);
    }


    /**
     * Add login style sheets and JS
     * 
     * @return
     */
    function kalimah_admin_login_style()
    {
        if ($this->settings->kalimah_admin_get_setting("kalimah_admin_theme_type") == 'material')
            wp_enqueue_style('kalimah-login-style', plugins_url('css/login-style-material.css',
                __file__), array(), '', 'all');
        else
            wp_enqueue_style('kalimah-login-style', plugins_url('css/login-style-flat.css',
                __file__), array(), '', 'all');

        wp_enqueue_script('kalimah-login-js', plugins_url('js/login.js', __file__),
            array('jquery'), '', 'all');
    }


    /**
     * This will change the plugin meta description to add some html elements
     * 
	 * // Not used in this version //
     * @param mixed $meta_data
     * @param mixed $file
     * @return
     */
    function kalimah_admin_custom_plugin_row_meta($meta_data, $file)
    {

        $meta_data[0] = "<i class='fa fa-code' aria-hidden='true' title='" . __("Version",
            "kalimah-admin") . "'></i>" . str_replace("Version", "", $meta_data[0]);
        $meta_data[1] = "<i class='fa fa-user' aria-hidden='true' title='" . __("Author",
            "kalimah-admin") . "'></i>" . str_replace("By", "", $meta_data[1]);
        $meta_data[2] = "<i class='fa fa-external-link' aria-hidden='true' title='" . __("Link",
            "kalimah-admin") . "'></i>" . $meta_data[2];

        return $meta_data;
    }


    /**
     * kalimah_admin::kalimah_admin_add_heading()
     * 
     * @return
     */
    function kalimah_admin_add_heading()
    {
        $logo = $this->settings->kalimah_admin_get_setting('kalimah_admin_upload_logo');
        echo "<div class='top_box'><a href='" . admin_url() .
            "'><img class='dahsboard-site-brand' src='{$logo}'></a><span class='toggle-sidemenu'></span></div>";
    }

    /**
     * Add a menu to WP Admin Bar (Profile Image, edit .. etc)
     * 
     * @param mixed $wp_admin_bar
     * @return
     */
    function wp_admin_bar_my_custom_account_menu($wp_admin_bar)
    {
        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        if (0 != $user_id) {
            $avatar = get_avatar($user_id);
            $alt_text = sprintf(__('Welcome, %1$s'), $user->display_name);
            $class = empty($avatar) ? '' : 'with-avatar';

            $wp_admin_bar->add_menu(array(
                'id' => 'my-account',
                'parent' => 'top-secondary',
                'title' => $avatar . $alt_text,
                'href' => get_edit_profile_url($user_id),
                'meta' => array('class' => $class),
                ));

        }
    }


    /**
     * Make changes to the WordPress Admin Bar.
     * 
     * @return
     */
    public function kalimah_admin_render_admin_bar()
    {

        // If it does not go to the backend, does even show
        if (!is_admin())
            return;

        // Get global var admin bar
        global $wp_admin_bar;

        // Remove logo
        $wp_admin_bar->remove_menu('wp-logo');
		
		// Remove user actions menu
        $wp_admin_bar->remove_menu('user-actions');

        // Settings
        $settings_node = array(
            'id' => 'kalimah-settings',
            'title' => '<i class="kalimah-adminbar-settings"></i>',
            'href' => admin_url('options-general.php'),
            'parent' => 'top-secondary',
            'meta' => array('title' => __('Settings', 'kalimah-dashboard')));

        // Logout
        $logout_node = array(
            'id' => 'kalimah-logout',
            'title' => '<i class="kalimah-adminbar-logout"></i>',
            'href' => wp_logout_url(),
            'parent' => 'top-secondary',
            'meta' => array('title' => __('Logout', 'kalimah-dashboard')));

        // Add settings
        $wp_admin_bar->add_node($settings_node);
        $wp_admin_bar->add_node($logout_node);
    }
}
new kalimah_admin();