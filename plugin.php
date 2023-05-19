<?php

/**
 * Plugin Name: One CLick Login
 * Description: One CLick Login
 * Versio: 1.0
 * author: Harsh Mahawar
 * Author URI: https://harshmahawar.com
 */

class OneCLickLogin
{
    function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'script_that_requires_jquery'));
        }
        add_action('admin_menu', array($this, 'ourPlugin_setting_links'));
        add_action('admin_init', array($this, 'ourPlugin_setting_links_init'));
        
    }
    function send_email($to, $from, $subject, $message){
        $headers = [
            'From' => "testsite <$from>",
            'Cc' => "testsite <$from>",
            'X-Sender' => "testsite <$from>",
            'X-Mailer' => 'PHP/' . phpversion(),
            'X-Priority' => '1',
            'Return-Path' => '$from',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=iso-8859-1'
        ];
        mail($to, $subject, $message, $headers);
    }
    function generate_one_time_login_link() {
        // Generate a unique token
        $token = str_replace(['&','#'],['and','hash'],wp_generate_password(30));
    $user_id = get_current_user_id();
        // Store the token in the user meta
        update_user_meta($user_id, 'one_time_login_token', $token);
    
        // Generate the login link
        $login_link = add_query_arg(array(
            'user_id' => $user_id,
            'token' => $token,
        ), wp_login_url());
    
        // Add the 'action' parameter to the login link to indicate a custom action
        // $login_link = add_query_arg('action', 'onetime', $login_link);
    
        return $login_link;
    }
    
    function script_that_requires_jquery()
    {
        wp_enqueue_script('my_custom_script', plugins_url('asset/js/script.js', __FILE__), array('jquery'), '3.4.0', true);
    }
    function ourPlugin_setting_links_init()
    {
        add_settings_section('ocl_first_section', null, null, 'one-click-login-setting');
        add_settings_field("ocl_email", "Your Email ID", array($this, 'emailHtml'), 'one-click-login-setting', 'ocl_first_section');
        register_setting("one_click_login_plugin", 'ocl_email', array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
    }
    function ourPlugin_setting_links()
    {
        add_options_page("One Click Login Settting", "One Click Login", "manage_options", "one-click-login-setting", array($this, 'one_click_login_setting_html'));
    }
    function emailHtml()
    {
?>
        <input type="text" name="ocl_email" value="<?php echo get_option('ocl_email'); ?>" style="width:250px"><button type="button" id="ocl_send_mail" style="margin-left:15px">Send Login Link</button>
    <?php
    }
    function one_click_login_setting_html()
    {
    ?>
        <div class="wrap">
            <h1>One Click Login Settting</h1>

            <form action="options.php" method="POST">
                <?php
                settings_fields('one_click_login_plugin');
                do_settings_sections('one-click-login-setting');
                submit_button(); ?>

            </form>
        </div>

<?php
    }
}
$one_click_login = new OneCLickLogin;
// Register the AJAX action for logged-in users
add_action('wp_ajax_ocl_send_email', 'ocl_send_email');
add_action('wp_ajax_nopriv_ocl_send_email', 'ocl_send_email');

function ocl_send_email()
{
    $data = $_POST['data'];
    $login = new OneCLickLogin;
    $login->send_email('thestagingwebsit@thestagingwebsites.com','thestagingwebsit@thestagingwebsites.com','One Click Login',"<a href='{$login->generate_one_time_login_link()}'>Open Dashboard</a>");

    wp_die();
}
add_action('login_init', 'ocl_login');
    function ocl_login(){
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $token = isset($_GET['token']) ? $_GET['token'] : '';

        // Verify the token
        $stored_token = get_user_meta($user_id, 'one_time_login_token', true);

        if ($token == $stored_token) {
            // Log the user in and redirect to the admin dashboard
			// $token = str_replace(['&','#'],['and','hash'],wp_generate_password(30));
            // update_user_meta($user_id, 'one_time_login_token', $token);
            $login = new OneCLickLogin;
            $login->send_email('thestagingwebsit@thestagingwebsites.com','thestagingwebsit@thestagingwebsites.com','One Click Login',"<a href='{$login->generate_one_time_login_link()}'>Open Dashboard</a>");
            $user = get_user_by('id', $user_id);
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user->user_login, $user);
            wp_redirect(admin_url());
            exit;
        }
}