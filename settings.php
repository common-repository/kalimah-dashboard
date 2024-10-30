<?php
require "inc/lessify.php";
require "inc/phpColors.php";

use Mexitek\PHPColors\Color;

class kalimah_admin_settings
{
    /* Save plugins dir */
    var $plugin_dir;
    var $options;
    var $lessify;

    /**
     * kalimah_settings::__construct()
     * 
     * @return
     */
    public function __construct()
    {

        $this->lessify = new lessc;
        $this->plugin_dir = plugin_dir_path(__file__);
        $this->options = get_option('kalimah_admin_settings');

        add_action('admin_menu', array($this, 'kaliamh_admin_add_admin_menu'));

        add_action('admin_init', array($this, 'kaliamh_admin_settings_init'));

        add_action('updated_option', array($this, 'kalimah_admin_update_LESS'), 10, 3);

        add_filter('admin_footer_text', array($this, 'kalimah_admin_remove_footer_admin'));
        add_filter('update_footer', array($this, 'kalimah_admin_remove_wordpress_footer_version'),
            999999);

        if (KALIMAH_ADMIN_SHOW_PLUGIN == false)
            add_filter('all_plugins', array($this, 'kalimah_admin_hide_plugin_from_list'));

        // Remove admin color scheme so it does not conflict with the theme
        remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');

        add_action('wp_dashboard_setup', array($this, 'kalimah_admin_remove_dashboard_widgets'));


        $this->kalimah_admin_check_conf();
    }

    /**
     * kalimah_settings::kalimah_admin_remove_dashboard_widgets()
     * 
     * @return
     */
    function kalimah_admin_remove_dashboard_widgets()
    {
        global $wp_meta_boxes;

        // Remove stat and WordPress news widgets
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);

        // Add sys info widget
        wp_add_dashboard_widget('kalimah_admin_sys_info', 'System Info', array($this,
                'kalimah_admin_sys_info'));
    }

   
    /**
     * kalimah_settings::kalimah_admin_sys_info()
     * 
     * @return
     */
    function kalimah_admin_sys_info()
    {
        $theme = wp_get_theme();

        $widget = "<div class='kalimah_system_info'>";
        $widget .= "<li><span>WordPress Version: </span><span>" . get_bloginfo('version') .
            "</span></li>";
        $widget .= "<li><span>PHP Version: </span><span>" . PHP_VERSION . "</span></li>";

        $widget .= "<li><span>Web Server: </span><span>" . $_SERVER['SERVER_SOFTWARE'] .
            "</span></li>";
        $widget .= "<li><span>Multi-Site Active: </span><span>";
        $widget .= (is_multisite()) ? __('Yes', 'sysinfo') : __('No', 'sysinfo');
        $widget .= "</span></li>";

        $widget .= "<li><span>cURL: </span><span>";
        $widget .= (function_exists('curl_init')) ? __('Yes', 'sysinfo') . "\n" : __('No',
            'sysinfo');
        $widget .= "</span></li>";

        $widget .= "<li><span>GD: </span><span>";
        $widget .= (function_exists('gd_info')) ? __('Yes', 'sysinfo') . "\n" : __('No',
            'sysinfo');
        $widget .= "</span></li>";

        $widget .= "<li><span>PHP Memory Limit: </span><span>" . ini_get('memory_limit') .
            "</span></li>";
        $widget .= "<li><span>Memory Useage: </span><span>" . round(memory_get_usage() /
            1024 / 1024, 2) . "</span></li>";
        $widget .= "<li><span>Active Theme: </span><span><a href='" . $theme->get('ThemeURI') .
            "'>" . $theme->get('Name') . "</a>, version: " . $theme->get('Version') .
            "</span></li>";


        $widget .= "</div>";

        echo $widget;
    }

    /**
     * kalimah_settings::kalimah_admin_remove_footer_admin()
     * 
     * @param mixed $text
     * @return
     */
    function kalimah_admin_remove_footer_admin($text)
    {
        if ($this->kalimah_admin_get_setting("kalimah_admin_footer_text") != '')
            echo $this->kalimah_admin_get_setting("kalimah_admin_footer_text");
        else
            echo $text;
    }

    /**
     * kalimah_settings::kalimah_admin_remove_wordpress_footer_version()
     * 
     * @param mixed $text
     * @return
     */
    function kalimah_admin_remove_wordpress_footer_version($text)
    {
        echo "";
    }


    /**
     * kalimah_settings::kalimah_admin_hide_plugin_from_list()
     * 
     * @param mixed $plugins
     * @return
     */
    function kalimah_admin_hide_plugin_from_list($plugins)
    {
        if (in_array('kalimah-dashboard/index.php', array_keys($plugins))) {
            unset($plugins['kalimah-dashboard/index.php']);
        }
        return $plugins;
    }


    /**
     * kalimah_settings::kalimah_admin_generate_colors()
     * 
     * @return
     */
    function kalimah_admin_generate_colors()
    {
        // Flat Colors
        $colors['first_color'] = '#16a085';
        $colors['second_color'] = '#2980b9';
        $colors['third_color'] = '#8e44ad';
        $colors['fourth_color'] = '#263849';
        $colors['fifth_color'] = '#da751c';


        foreach ($colors as $name => $color) {
            // Initialize my color
            $new_color = new Color($color);

            $this->flat_colors[$name]['firstBgColor'] = $color;
            $this->flat_colors[$name]['secondBgColor'] = "#" . $new_color->lighten();
            $this->flat_colors[$name]['thirdBgColor'] = "#" . $new_color->lighten("20%");
            $this->flat_colors[$name]['firstTextColor'] = "#" . $new_color->darken("40%");
            $this->flat_colors[$name]['secondTextColor'] = '#fff';
            $this->flat_colors[$name]['thirdTextColor'] = '#fff';
        }

        // Material Colors
        $m_colors['first_color'] = '#607d8b';
        $m_colors['second_color'] = '#ff9800';
        $m_colors['third_color'] = '#009688';
        $m_colors['fourth_color'] = '#CDDC39';
        $m_colors['fifth_color'] = '#673AB7';
        $m_colors['sixth_color'] = '#795548';


        foreach ($m_colors as $name => $color) {
            // Initialize my color
            $new_color = new Color($color);

            $this->material_colors[$name]['firstBgColor'] = $color;
            $this->material_colors[$name]['secondBgColor'] = "#" . $new_color->lighten();
            $this->material_colors[$name]['thirdBgColor'] = "#" . $new_color->lighten("20%");
            $this->material_colors[$name]['firstTextColor'] = "#" . $new_color->darken("30%");
            $this->material_colors[$name]['secondTextColor'] = "#" . $new_color->darken("40%");
            $this->material_colors[$name]['thirdTextColor'] = '#fff';
        }
    }

    /**
     * kalimah_settings::kalimah_admin_get_color_part()
     * 
     * @param mixed $color_part
     * @param mixed $color_set
     * @return
     */
    function kalimah_admin_get_color_part($color_part, $color_set)
    {
        if ($this->kalimah_admin_get_setting('kalimah_admin_theme_type') == "flat")
            return $this->flat_colors[$color_set][$color_part];
        else
            return $this->material_colors[$color_set][$color_part];
    }

    /* Update LESS files each time we save settings */
    /**
     * kalimah_settings::kalimah_admin_update_LESS()
     * 
     * @return
     */
    function kalimah_admin_update_LESS()
    {
        // generate colors
        $this->kalimah_admin_generate_colors();

        $this->lessify->setPreserveComments(true);

        $this->kalimah_admin_update_options();

        $theme_color = ($this->kalimah_admin_get_setting('kalimah_admin_theme_type') == "flat") ? $this->
            kalimah_admin_get_setting('kalimah_admin_flat_colors') : $this->kalimah_admin_get_setting('kalimah_admin_material_colors');

        $this->lessify->setVariables(array(
            "bg_url" => "'" . $this->kalimah_admin_get_setting('kalimah_admin_upload_wallpaper') . "'",
            "logo" => "'" . $this->kalimah_admin_get_setting('kalimah_admin_upload_logo') . "'",
            "firstBgColor" => $this->kalimah_admin_get_color_part('firstBgColor', $theme_color),
            "secondBgColor" => $this->kalimah_admin_get_color_part('secondBgColor', $theme_color),
            "thirdBgColor" => $this->kalimah_admin_get_color_part('thirdBgColor', $theme_color),
            "firstTextColor" => $this->kalimah_admin_get_color_part('firstTextColor', $theme_color),
            "secondTextColor" => $this->kalimah_admin_get_color_part('secondTextColor', $theme_color),
            "thirdTextColor" => $this->kalimah_admin_get_color_part('thirdTextColor', $theme_color)));

        $this->kalimah_admin_compile_filed(array(
            "css/style-material",
            "css/login-style-material",
            "css/style-flat",
            "css/login-style-flat",
            "css/settings"));
    }

    /**
     * kalimah_settings::kalimah_admin_compile_filed()
     * 
     * @param mixed $files
     * @return
     */
    function kalimah_admin_compile_filed($files)
    {
        foreach ($files as $path) {
            $content = $this->lessify->compileFile($this->plugin_dir . $path . ".less");
            file_put_contents($this->plugin_dir . $path . ".css", $content);
        }
    }

    /* Check if conf.php have been updated */
    /**
     * kalimah_settings::kalimah_admin_check_conf()
     * 
     * @return
     */
    function kalimah_admin_check_conf()
    {
        $last_modified = filemtime($this->plugin_dir . "conf.php");
        $last_stored_modified = get_option("kalimah_admin_last_modified_conf");

        if ($last_modified != $last_stored_modified) {
            update_option("kalimah_admin_last_modified_conf", $last_modified);

            // update CSS files
            $this->kalimah_admin_get_color_part();
        }
    }

    /* Add options page */
    /**
     * kalimah_settings::kaliamh_admin_add_admin_menu()
     * 
     * @return
     */
    function kaliamh_admin_add_admin_menu()
    {
        // Show only if user set it to shwo
        if (KALIMAH_ADMIN_SHOW_PLUGIN_LINK == true) {
            add_menu_page('Kalimah Admin', 'Kalimah Admin', 'manage_options',
                'kalimah_admin', array($this, 'kalimah_admin_options_page'));
        }
        wp_enqueue_media();
    }

    /* Get setting value from the source that the user specified */
    /**
     * kalimah_settings::kalimah_admin_get_setting()
     * 
     * @param mixed $setting
     * @return
     */
    function kalimah_admin_get_setting($setting)
    {
        return (KALIMAH_ADMIN_SETTINGS_SOURCE == "database") ? $this->options[$setting] : constant($setting);
    }

    /* Update options to get fresh set of values*/
    /**
     * kalimah_settings::kalimah_admin_update_options()
     * 
     * @return
     */
    function kalimah_admin_update_options()
    {
        $this->options = get_option('kalimah_admin_settings');
    }

    // Initiate settings
    /* Settings in the dashboard interface will always take their values from database
    Other settings will take their values from the source that users set in conf.php 
    */
    /**
     * kalimah_settings::kaliamh_admin_settings_init()
     * 
     * @return
     */
    function kaliamh_admin_settings_init()
    {
        register_setting('kalimah_admin', 'kalimah_admin_settings');
        add_settings_section('kalimah_admin_pluginPage_section', __('Settings',
            'kalimah_admin'), '', 'kalimah_admin');

        add_settings_field('kalimah_admin_theme_type', __('Admin theme', 'kalimah_admin'),
            function ()
        {
            $form = "<select name='kalimah_admin_settings[kalimah_admin_theme_type]' class='kalimah_admin_theme'>";
                $form .= "<option value='flat'" . selected($this->options['kalimah_admin_theme_type'],
                "flat", false) . ">Flat</option>"; $form .= "<option value='material'" .
                selected($this->options['kalimah_admin_theme_type'], "material", false) .
                ">Material</option>"; $form .= "</select>"; echo $form; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');

        add_settings_field('kalimah_admin_flat_colors', __('Colors', 'kalimah_admin'),
            function ()
        {
            $colors = array(
                "first_color",
                "second_color",
                "third_color",
                "fourth_color",
                "fifth_color"); $form .= "<div class='flat_colors'>"; foreach ($colors as $color) {
                $form .= "<input type='radio' id='flat_colors_$color' name='kalimah_admin_settings[kalimah_admin_flat_colors]'" .
                    checked($this->options['kalimah_admin_flat_colors'], $color, false) . " value='{$color}'>";
                    $form .= "<label class='{$color}' for='flat_colors_$color'></label>"; }
            $form .= "</div>"; echo $form; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');

        add_settings_field('kalimah_admin_material_colors', __('Colors', 'kalimah_admin'),
            function ()
        {
            $colors = array(
                "first_color",
                "second_color",
                "third_color",
                "fourth_color",
                "fifth_color",
                "sixth_color"); $form .= "<div class='material_colors'>"; foreach ($colors as $color) {
                $form .= "<input type='radio' id='material_colors_$color' name='kalimah_admin_settings[kalimah_admin_material_colors]'" .
                    checked($this->options['kalimah_admin_material_colors'], $color, false) .
                    " value='{$color}'>"; $form .= "<label class='{$color}' for='material_colors_$color'></label>"; }
            $form .= "</div>"; echo $form; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');


        add_settings_field('kalimah_admin_upload_logo', __('Upload Logo',
            'kalimah_admin'), function ()
        {
            echo "<div class='upload_image'>
			<input type='hidden' class='image_url' name='kalimah_admin_settings[kalimah_admin_upload_logo]' value='" .
                esc_url($this->options['kalimah_admin_upload_logo']) . "' />
			<div class='kalimah_admin_image_wrapper'><span>Image</span><img src='" . $this->
                options['kalimah_admin_upload_logo'] . "' id='kalimah_admin_image_url'></div>
			<div class='kalimah_admin_upload_actions'>
				<div class='kalimah_admin_upload_action'></div>
				<div class='kalimah_admin_delete_action'></div>
			</div>
			</div>"; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');

        add_settings_field('kalimah_admin_upload_wallpaper', __('Login Wallpaper',
            'kalimah_admin'), function ()
        {
            echo "<div class='upload_image'>
			<input type='hidden' class='image_url' name='kalimah_admin_settings[kalimah_admin_upload_wallpaper]' value='" .
                esc_url($this->options['kalimah_admin_upload_wallpaper']) . "' />
			<div class='kalimah_admin_image_wrapper'><span>Image</span><img src='" . $this->
                options['kalimah_admin_upload_wallpaper'] .
                "' id='kalimah_admin_image_url'></div>
			<div class='kalimah_admin_upload_actions'>
				<div class='kalimah_admin_upload_action'></div>
				<div class='kalimah_admin_delete_action'></div>
			</div>
			</div>"; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');


        add_settings_field('kalimah_admin_custom_css', __('Custom CSS', 'kalimah_admin'),
            function ()
        {

            $form .= "<textarea cols='40' rows='5' name='kalimah_admin_settings[kalimah_admin_custom_css]'>";
                $form .= $this->options['kalimah_admin_custom_css']; $form .= "</textarea>";
                echo $form; }

        , 'kalimah_admin', 'kalimah_admin_pluginPage_section');

        /*** Footer Section */
        add_settings_section('kalimah_admin_plugin_footer_section', __('Footer',
            'kalimah_admin'), '', 'kalimah_admin');
        add_settings_field('kalimah_admin_footer_text', __('Custom Footer Text',
            'kalimah_admin'), function ()
        {
            $settings = array(
                'textarea_name' => 'kalimah_admin_settings[kalimah_admin_footer_text]',
                'media_buttons' => false,
                'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'),
                'textarea_rows' => '5',
                "teeny" => true,
                'tinymce' => false); wp_editor($this->options['kalimah_admin_footer_text'],
                "kalimah_admin_footer_text", $settings); }
        , 'kalimah_admin', 'kalimah_admin_plugin_footer_section');

    }

    /**
     * kalimah_settings::kalimah_admin_options_page()
     * 
     * @return
     */
    function kalimah_admin_options_page()
    {

        echo "<div class='wrap' id='kalimah-admin-settings'>";
        echo "<h1>Kalimah Admin</h1>";
        echo "<form action='options.php' method='post'>";

        settings_fields('kalimah_admin');
        do_settings_sections('kalimah_admin');
        submit_button();

        echo "</form></div>";
    }
}


/**
 * This plugin offers a starting point for replacing the WordPress dashboard.  If you are familiar with object oriented
 * programming, just subclass and overwrite the set_title() and page_content() methods. Otherwise, just alter the
 * set_title() and page_content() functions as needed.
 *
 * Customize which users are redirected to the custom dashboard by changing the capability property.
 *
 * If you don't want this plugin to be deactivated, just drop this file in the mu-plugins folder in the wp-content
 * directory.  If you don't have an mu-plugins folder, just create one.
 */
class Replace_WP_Dashboard
{
    protected $capability = 'read';
    protected $title;
    /**
     * Replace_WP_Dashboard::__construct()
     * 
     * @return
     */
    final public function __construct()
    {
        if (is_admin()) {
            add_action('init', array($this, 'init'));
        }
    }
    /**
     * Replace_WP_Dashboard::init()
     * 
     * @return
     */
    final public function init()
    {
        if (current_user_can($this->capability)) {
            $this->set_title();
            add_filter('admin_title', array($this, 'admin_title'), 10, 2);
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('current_screen', array($this, 'current_screen'));
        }
    }
    /**
     * Sets the page title for your custom dashboard
     */
    /**
     * Replace_WP_Dashboard::set_title()
     * 
     * @return
     */
    function set_title()
    {
        if (!isset($this->title)) {
            $this->title = __('Dashboard');
        }
    }
    /**
     * Output the content for your custom dashboard
     */
    /**
     * Replace_WP_Dashboard::page_content()
     * 
     * @return
     */
    function page_content()
    {
        $content = __('Welcome to your new dashboard!');
        echo <<< HTML
<div class="wrap">
    <h2>{$this->title}</h2>
    <p>{$content}</p>
</div>
HTML;
    }
    /**
     * Fixes the page title in the browser.
     *
     * @param string $admin_title
     * @param string $title
     * @return string $admin_title
     */
    /**
     * Replace_WP_Dashboard::admin_title()
     * 
     * @param mixed $admin_title
     * @param mixed $title
     * @return
     */
    final public function admin_title($admin_title, $title)
    {
        global $pagenow;
        if ('admin.php' == $pagenow && isset($_GET['page']) && 'custom-page' == $_GET['page']) {
            $admin_title = $this->title . $admin_title;
        }
        return $admin_title;
    }

    /**
     * Replace_WP_Dashboard::admin_menu()
     * 
     * @return
     */
    final public function admin_menu()
    {
        /**
         * Adds a custom page to WordPress
         */
        add_menu_page($this->title, '', 'manage_options', 'custom-page', array($this,
                'page_content'));
        /**
         * Remove the custom page from the admin menu
         */
        remove_menu_page('custom-page');
        /**
         * Make dashboard menu item the active item
         */
        global $parent_file, $submenu_file;
        $parent_file = 'index.php';
        $submenu_file = 'index.php';
        /**
         * Rename the dashboard menu item
         */
        global $menu;
        $menu[2][0] = $this->title;
        /**
         * Rename the dashboard submenu item
         */
        global $submenu;
        $submenu['index.php'][0][0] = $this->title;
    }
    /**
     * Redirect users from the normal dashboard to your custom dashboard
     */
    /**
     * Replace_WP_Dashboard::current_screen()
     * 
     * @param mixed $screen
     * @return
     */
    final public function current_screen($screen)
    {
        if ('dashboard' == $screen->id) {
            wp_safe_redirect(admin_url('admin.php?page=custom-page'));
            exit;
        }
    }
}
//new Replace_WP_Dashboard();


/**
 * st_welcome_panel()
 * 
 * @return
 */
function st_welcome_panel()
{
    $panel = "<div class='top-panel-stat'>";

    update_user_meta(get_current_user_id(), 'show_welcome_panel', 1);

    foreach (array('post', 'page') as $post_type) {
        $num_posts = wp_count_posts($post_type);

        $link = '';
        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object && current_user_can($post_type_object->cap->edit_posts)) {
            $link = "edit.php?post_type=$post_type";
        }


        $panel .= "<div class='{$post_type}'>";

        if ($link != '')
            $panel .= "<a href='$link'>";

        $panel .= "<span class='panel_box_title'>";
        $panel .= $post_type;
        $panel .= "</span>";
        $panel .= "<span class='publish'>Publish: ";
        $panel .= wp_count_posts($post_type)->publish;
        $panel .= "</span>";
        $panel .= "<span class='draft'>Draft: ";
        $panel .= wp_count_posts($post_type)->draft;
        $panel .= "</span>";

        if ($link != '')
            $panel .= "</a>";

        $panel .= "</div>";
    }


    $num_comm = wp_count_comments();
    if ($num_comm && ($num_comm->approved || $num_comm->moderated)) {
        $panel .= "<div class='comments'>";
        $panel .= "<a href='edit-comments.php?comment_status=moderated'>";
        $panel .= "<span class='panel_box_title'>";
        $panel .= "Commentes";
        $panel .= "</span>";
        $panel .= "<span class='approved'>Approved: ";
        $panel .= number_format_i18n($num_comm->approved);
        $panel .= "</span>";
        $panel .= "<span class='moderated'>Moderated: ";
        $panel .= number_format_i18n($num_comm->moderated);
        $panel .= "</span>";
        $panel .= "</a>";
        $panel .= "</div>";
    }

    $users = count_users();

    $panel .= "<div class='users'>";
    $panel .= "<a href='users.php'>";
    $panel .= "<span class='panel_box_title'>";
    $panel .= "Users";
    $panel .= "</span>";

    $panel .= "<span class='total'>Total: ";
    $panel .= $users['total_users'];
    $panel .= "</span>";

    foreach ($users['avail_roles'] as $role => $count) {
        //$panel .= "<span class='{$role}'>$role: $count </span>";
    }
    $panel .= "</a>";
    $panel .= "</div>";


    echo $panel;
}

remove_action('welcome_panel', 'wp_welcome_panel');
add_action('welcome_panel', 'st_welcome_panel');
