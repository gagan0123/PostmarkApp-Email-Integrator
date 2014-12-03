<?php
/*
  Plugin Name: Postmarkapp Email Integrator
  Plugin URI: https://wordpress.org/plugins/postmarkapp-email-integrator/
  Description: Overwrites wp_mail to send emails through Postmark. This plugin is a bug fixed edition of the official Postmarkapp plugin
  Author: Gagan Deep Singh
  Version: 2.4
  Author URI: https://gagan.pro
 */

// Define
define('POSTMARKAPP_ENDPOINT', 'http://api.postmarkapp.com/email');

// Admin Functionality
add_action('admin_menu', 'pma_admin_menu'); // Add Postmark to Settings

/*
 * Imports the settings of the official postmark plugin to this plugin.
 * * */

function pma_import_settings() {
    $options = array(
        'postmarkapp_api_key' => 'postmark_api_key',
        'postmarkapp_sender_address' => 'postmark_sender_address',
        'postmarkapp_force_html' => 'postmark_force_html',
        'postmarkapp_trackopens' => 'postmark_trackopens'
    );
    foreach ($options as $here => $there) {
        update_option($here, get_option($there));
    }
}

function pma_plugin_activate() {
    if (get_option('postmarkapp_api_key') === false) {
        pma_import_settings();
    }
}

register_activation_hook(__FILE__, 'pma_plugin_activate');

function pma_admin_menu() {
    add_options_page('Postmarkapp', 'Postmarkapp', 'manage_options', 'pma_admin', 'pma_admin_options');
}

function pma_admin_action_links($links, $file) {
    static $pma_plugin;
    if (!$pma_plugin) {
        $pma_plugin = plugin_basename(__FILE__);
    }
    if ($file == $pma_plugin) {
        $settings_link = '<a href="options-general.php?page=pma_admin">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

add_filter('plugin_action_links', 'pma_admin_action_links', 10, 2);

function pma_admin_options() {
    if (isset($_POST['submit']) && $_POST['submit'] == "Save") {

        if (isset($_POST['pma_enabled']) && $_POST['pma_enabled']) {
            $pma_enabled = 1;
        } else {
            $pma_enabled = 0;
        }

        $api_key = $_POST['pma_api_key'];
        $sender_email = $_POST['pma_sender_address'];

        if (isset($_POST['pma_forcehtml']) && $_POST['pma_forcehtml']) {
            $pma_forcehtml = 1;
        } else {
            $pma_forcehtml = 0;
        }

        if (isset($_POST['pma_poweredby']) && $_POST['pma_poweredby']) {
            $pma_poweredby = 1;
        } else {
            $pma_poweredby = 0;
        }

        if (isset($_POST['pma_trackopens']) && $_POST['pma_trackopens']) {
            $pma_trackopens = 1;
            $pma_forcehtml = 1;
        } else {
            $pma_trackopens = 0;
        }

        update_option('postmarkapp_enabled', $pma_enabled);
        update_option('postmarkapp_api_key', $api_key);
        update_option('postmarkapp_sender_address', $sender_email);
        update_option('postmarkapp_force_html', $pma_forcehtml);
        update_option('postmarkapp_trackopens', $pma_trackopens);

        $msg_updated = "Postmarkapp settings have been saved.";
    }
    ?>

    <script type="text/javascript" >
        jQuery(document).ready(function ($) {

            $("#test-form").submit(function (e) {
                e.preventDefault();
                var $this = $(this);
                var send_to = $('#pma_test_address').val();

                $("#test-form .button-primary").val("Sending...");
                $.post(ajaxurl, {email: send_to, action: $this.attr("action")}, function (data) {
                    $("#test-form .button-primary").val(data);
                });
            });
            $('#pma_import_button').click(function () {
                $.post(ajaxurl, {action: 'pma_import_settings'}, function (data) {
                    $("#test-form .button-secondary").val(data);
                    if (data == 'Settings Imported') {
                        location.reload();
                    }
                });
            });

        });
    </script>

    <div class="wrap">

        <?php if (isset($msg_updated)): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
        <?php if (isset($msg_error)): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>

        <div id="icon-tools" class="icon32"></div>
        <h2>Postmarkapp Settings</h2>
        <h3>What is Postmark?</h3>
        <p>This plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance. </p>
        <p>If you don't already have a free Postmark account, <a href="https://postmarkapp.com/sign_up">you can get one in minutes</a>. Every account comes with thousands of free sends.</p>

        <br />

        <h3>Your Postmark Settings</h3>
        <form method="post" action="options-general.php?page=pma_admin">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="pma_enabled">Send using Postmark</label></th>
                        <td><input name="pma_enabled" id="" type="checkbox" value="1"<?php if (get_option('postmarkapp_enabled') == 1): echo ' checked="checked"';
    endif; ?>/> <span style="font-size:11px;">Sends emails sent using wp_mail via Postmark.</span></td>
                    </tr>
                    <tr>
                        <th><label for="pma_api_key">Postmark API Key</label></th>
                        <td><input name="pma_api_key" id="" type="text" value="<?php echo get_option('postmarkapp_api_key'); ?>" class="regular-text"/> <br/><span style="font-size:11px;">Your API key is available in the <strong>credentials</strong> screen of your Postmark server. <a href="https://postmarkapp.com/servers/">Create a new server in Postmark</a>.</span></td>
                    </tr>
                    <tr>
                        <th><label for="pma_sender_address">Sender Email Address</label></th>
                        <td><input name="pma_sender_address" id="" type="text" value="<?php echo get_option('postmarkapp_sender_address'); ?>" class="regular-text"/> <br/><span style="font-size:11px;">This email needs to be one of your <strong>verified sender signatures</strong>. <br/>It will appear as the "from" email on all outbound messages. <a href="https://postmarkapp.com/signatures">Set one up in Postmark</a>.</span></td>
                    </tr>
                    <tr>
                        <th><label for="pma_forcehtml">Force HTML</label></th>
                        <td><input name="pma_forcehtml" id="" type="checkbox" value="1"<?php if (get_option('postmarkapp_force_html') == 1): echo ' checked="checked"';
    endif; ?>/> <span style="font-size:11px;">Force all emails to be sent as HTML.</span></td>
                    </tr>
                    <tr>
                        <th><label for="pma_trackopens">Track Opens</label></th>
                        <td><input name="pma_trackopens" id="" type="checkbox" value="1"<?php if (get_option('postmarkapp_trackopens') == 1): echo ' checked="checked"';
    endif; ?>/> <span style="font-size:11px;">Use Postmark's Open Tracking feature to capture open events. (Forces Html option to be turned on)</span></td>
                    </tr>
                </tbody>
            </table>
            <div class="submit">
                <input type="submit" name="submit" value="Save" class="button-primary" />
            </div>
        </form>

        <br />

        <h3>Test Postmark Sending</h3>
        <form method="post" id="test-form" action="pma_admin_test">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="pma_test_address">Send a Test Email To</label></th>
                        <td> <input name="pma_test_address" id="pma_test_address" type="text" value="<?php echo get_option('postmarkapp_sender_address'); ?>" class="regular-text"/></td>
                    </tr>
                </tbody>
            </table>
            <div class="submit">
                <input type="submit" name="submit" value="Send Test Email" class="button-primary" />
            </div>
            <div class="submit">
                <input id="pma_import_button" type="button" value="Import Settings" class="button-secondary" />
            </div>
        </form>

    </div>

    <?php
}

add_action('wp_ajax_pma_admin_test', 'pma_admin_test_ajax');

function pma_admin_test_ajax() {
    $response = pma_send_test();

    echo $response;

    die();
}

// End Admin Functionality
// Override wp_mail() if postmark enabled
if (get_option('postmarkapp_enabled') == 1) {
    if (!function_exists("wp_mail")) {

        function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {

            // Compact the input, apply the filters, and extract them back out
            extract(apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments')));

            $recognized_headers = pma_parse_headers($headers);
            
            //Adding the filter thats executed in the wp_mail function so that plugins using those filters will not clash
            //Did not used the filters wp_mail_from_name, wp_mail_from(both are defined in the postmarkapp settings) and wp_mail_charset(postmarkapp does not accept charset)
            $recognized_headers['Content-Type'] = apply_filters( 'wp_mail_content_type', $recognized_headers['Content-Type'] );

            if (isset($recognized_headers['Content-Type']) && stripos($recognized_headers['Content-Type'], 'text/html') !== false) {
                $current_email_type = 'HTML';
            } else {
                $current_email_type = 'PLAINTEXT';
            }

            // Define Headers
            $postmark_headers = array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => get_option('postmarkapp_api_key')
            );

            // Send Email
            if (is_array($to)) {
                $recipients = implode(",", $to);
            } else {
                $recipients = $to;
            }

            //@todo need to add a count for receipients(including Cc and Bcc so that they may not go beyond 20
            // Construct Message
            $email = array();
            $email['To'] = $recipients;
            $email['From'] = get_option('postmarkapp_sender_address');
            $email['Subject'] = $subject;
            $email['TextBody'] = $message;

            if (isset($recognized_headers['Cc']) && !empty($recognized_headers['Cc'])) {
                $email['Cc'] = $recognized_headers['Cc'];
            }

            if (isset($recognized_headers['Bcc']) && !empty($recognized_headers['Bcc'])) {
                $email['Bcc'] = $recognized_headers['Bcc'];
            }

            if (isset($recognized_headers['Reply-To']) && !empty($recognized_headers['Reply-To'])) {
                $email['ReplyTo'] = $recognized_headers['Reply-To'];
            }

            if ($current_email_type == 'HTML') {
                $email['HtmlBody'] = $message;
            } else if (get_option('postmarkapp_force_html') == 1 || get_option('postmarkapp_trackopens') == 1) {
                $email['HtmlBody'] = pma_convert_plaintext_to_html($message);
            }

            if (get_option('postmarkapp_trackopens') == 1) {
                $email['TrackOpens'] = "true";
            }

            $response = pma_send_mail($postmark_headers, $email);

            if (is_wp_error($response)) {
                return false;
            }
            return true;
        }

    }
}

function pma_convert_plaintext_to_html($message) {
    $message = nl2br(htmlspecialchars($message));
    return $message;
}

/*
 * Parses the $headers string or array and create a recognizable headers array
 */

function pma_parse_headers($headers) {
    if (!is_array($headers)) {
        if (stripos($headers, "\r\n") !== false) {
            $headers = explode("\r\n", $headers);
        } else {
            $headers = explode("\n", $headers);
        }
    }
    $recognized_headers = array();
    $headers_list = array(
        'Content-Type' => array(),
        'Bcc' => array(),
        'Cc' => array(),
        'Reply-To' => array()
    );
    $headers_list_lowercase = array_change_key_case($headers_list, CASE_LOWER);
    if (!empty($headers)) {
	    foreach ($headers as $key => $header) {
                    $key = strtolower($key);
		    if (array_key_exists($key, $headers_list_lowercase)) {
			    $header_key = $key;
                            $header_val = $header;
                            $segments = explode(':', $header);
                            if (count($segments) === 2) {
				    if (array_key_exists(strtolower($segments[0]), $headers_list_lowercase)) {
					    list($header_key, $header_val) = $segments;
                                            $header_key = strtolower($header_key);
				    }
			    }
		    }
		    else {
			    $segments = explode(':', $header);
			    if (count($segments) === 2) {
				    if (array_key_exists(strtolower($segments[0]), $headers_list_lowercase)) {
					    list($header_key, $header_val) = $segments;
                                            $header_key = strtolower($header_key);
				    }
			    }
		    }
		    if (isset($header_key) && isset($header_val)) {
			    if (stripos($header_val, ',') === false) {
				    $headers_list_lowercase[$header_key][] = trim($header_val);
			    }
			    else {
				    $vals = explode(',', $header_val);
				    foreach ($vals as $val) {
					    $headers_list_lowercase[$header_key][] = trim($val);
				    }
			    }
			    unset($header_key);
			    unset($header_val);
		    }
	    }

	    foreach ($headers_list as $key => $value) {
                    $value = $headers_list_lowercase[strtolower($key)];
		    if (count($value) > 0) {
			    $recognized_headers[$key] = implode(', ', $value);
		    }
	    }
    }
    return $recognized_headers;
}

function pma_send_test() {
    $email_address = $_POST['email'];

    // Define Headers
    $postmark_headers = array(
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'X-Postmark-Server-Token' => get_option('postmarkapp_api_key')
    );

    $message = 'This is a test email sent via Postmark from ' . get_bloginfo('name') . '.';
    $html_message = 'This is a test email sent via <strong>Postmark</strong> from ' . get_bloginfo('name') . '.';

    $email = array();
    $email['To'] = $email_address;
    $email['From'] = get_option('postmarkapp_sender_address');
    $email['Subject'] = get_bloginfo('name') . ' Postmark Test';
    $email['TextBody'] = $message;

    if (get_option('postmarkapp_force_html') == 1) {
        $email['HtmlBody'] = $html_message;
    }

    if (get_option('postmarkapp_trackopens') == 1) {
        $email['TrackOpens'] = "true";
    }

    $response = pma_send_mail($postmark_headers, $email);

    if (is_wp_error($response)) {
        return 'Test Failed with Error "' . $response->get_error_message() . '"';
    } else {
        return "Test Sent";
    }

    die();
}

function pma_send_mail($headers, $email) {
    $args = array(
        'headers' => $headers,
        'body' => json_encode($email)
    );
    do_action('before_wp_mail');

    $response = wp_remote_post(POSTMARKAPP_ENDPOINT, apply_filters('pma_mail_args', $args));

    do_action('after_wp_mail');

    if (is_wp_error($response)) {
        return new WP_Error('CONNECTION_TIMEOUT', 'Connection Timeout');
    } else if (isset($response['response']['code'])) {
        if ($response['response']['code'] == 200) {
            return true;
        } else {
            $failure_message = '';
            if (isset($response['body'])) {
                $error = json_decode($response['body'], true);
                if (isset($error['ErrorCode'])) {
                    $error_code = $error['ErrorCode'];
                } else {
                    $error_code = '000';
                }
                if (isset($error['Message'])) {
                    $error_message = $error['Message'];
                } else {
                    $error_message = 'Unknown Error';
                }
                return new WP_Error($error_code, $error_message);
            }
        }
    } else {
        return new WP_Error('NO_RESPONSE', 'No Response from the PostMark Server');
    }
    wp_mail();
}

/*
 * Changes the default http request timeout of wordpress from 5 seconds to 60
 * seconds so that the request the postmark api can be successfully executed.
 */

function pma_filter_http_request_timeout($current_timeout) {
    if (intval($current_timeout) < 60) {
        return intval(60);
    } else {
        return intval($current_timeout);
    }
}

/*
 * Adds timeout filter so that mail function can get enough time to contact the
 * postmark api servers
 */

function pma_add_timeout_filter() {
    add_filter('http_request_timeout', 'pma_filter_http_request_timeout');
}

add_action('before_wp_mail', 'pma_add_timeout_filter');

/*
 * Removes the timeout filter after the mail function has been successfully 
 * executed
 */

function pma_remove_timeout_filter() {
    remove_filter('http_request_timeout', 'pma_filter_http_request_timeout');
}

add_action('after_wp_mail', 'pma_remove_timeout_filter');

/**
 * Imports the settings of the Postmark Approved Wordpress plugin
 */
function pma_admin_import_settings() {
    pma_import_settings();
    echo "Settings Imported";
    die();
}

add_action('wp_ajax_pma_import_settings', 'pma_admin_import_settings');
