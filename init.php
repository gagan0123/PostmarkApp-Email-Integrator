<?php
/*
Plugin Name: Postmarkapp WordPress Plugin
Plugin URI: https://blog.gagan.pro/postmarkapp-wordpress-plugin/
Description: Overwrites wp_mail to send emails through Postmark.
Author: Gagan Deep Singh
Version: 1.0
Author URI: https://gagan.pro
*/

// Define
define('POSTMARKAPP_ENDPOINT', 'http://api.postmarkapp.com/email');

// Admin Functionality
add_action('admin_menu', 'pma_admin_menu'); // Add Postmark to Settings

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
	if($_POST['submit']) {
		$pma_enabled = $_POST['pma_enabled'];
		if($pma_enabled):
			$pma_enabled = 1;
		else:
			$pma_enabled = 0;
		endif;

		$api_key = $_POST['pma_api_key'];
		$sender_email = $_POST['pma_sender_address'];

		$pma_forcehtml = $_POST['pma_forcehtml'];
		if($pma_forcehtml):
			$pma_forcehtml = 1;
		else:
			$pma_forcehtml = 0;
		endif;

		$pma_poweredby = $_POST['pma_poweredby'];
		if($pma_poweredby):
			$pma_poweredby = 1;
		else:
			$pma_poweredby = 0;
		endif;

		$pma_trackopens = $_POST['pma_trackopens'];
		if($pma_trackopens){
			$pma_trackopens = 1;
			$pma_forcehtml = 1;
		}
		else
		{
			$pma_trackopens = 0;
		}


		update_option('postmarkapp_enabled', $pma_enabled);
		update_option('postmarkapp_api_key', $api_key);
		update_option('postmarkapp_sender_address', $sender_email);
		update_option('postmarkapp_force_html', $pma_forcehtml);
		update_option('postmarkapp_poweredby', $pma_poweredby);
		update_option('postmarkapp_trackopens', $pma_trackopens);

		$msg_updated = "Postmarkapp settings have been saved.";
	}
	?>

	<script type="text/javascript" >
	jQuery(document).ready(function($) {

		$("#test-form").submit(function(e){
			e.preventDefault();
			var $this = $(this);
			var send_to = $('#pma_test_address').val();

			$("#test-form .button-primary").val("Sendingâ€¦");
			$.post(ajaxurl, {email: send_to, action:$this.attr("action")}, function(data){
				$("#test-form .button-primary").val(data);
			});
		});

	});
	</script>

	<div class="wrap">

		<?php if($msg_updated): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
		<?php if($msg_error): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>

		<div id="icon-tools" class="icon32"></div>
		<h2>Postmarkapp Settings</h2>
    <h3>What is Postmark?</h3>
		<p>This Postmark Approved plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance. </p>
		<p>If you don't already have a free Postmark account, <a href="https://postmarkapp.com/sign_up">you can get one in minutes</a>. Every account comes with 10,000 free sends.</p>

		<br />

		<h3>Your Postmark Settings</h3>
		<form method="post" action="options-general.php?page=pma_admin">
			<table class="form-table">
			<tbody>
				<tr>
					<th><label for="pma_enabled">Send using Postmark</label></th>
					<td><input name="pma_enabled" id="" type="checkbox" value="1"<?php if(get_option('postmarkapp_enabled') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Sends emails sent using wp_mail via Postmark.</span></td>
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
					<td><input name="pma_forcehtml" id="" type="checkbox" value="1"<?php if(get_option('postmarkapp_force_html') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Force all emails to be sent as HTML.</span></td>
				</tr>
				<tr>
					<th><label for="pma_trackopens">Track Opens</label></th>
					<td><input name="pma_trackopens" id="" type="checkbox" value="1"<?php if(get_option('postmarkapp_trackopens') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Use Postmark's Open Tracking feature to capture open events. (Forces Html option to be turned on)</span></td>
				</tr>
				<tr>
					<th><label for="pma_poweredby">Support Postmark</label></th>
					<td><input name="pma_poweredby" id="" type="checkbox" value="1"<?php if(get_option('postmarkapp_poweredby') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Adds a credit to Postmark at the bottom of emails.</span></td>
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
if(get_option('postmarkapp_enabled') == 1){
	if (!function_exists("wp_mail")){
		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array()) {

			// Define Headers
			$postmark_headers = array(
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
		        'X-Postmark-Server-Token' => get_option('postmarkapp_api_key')
			);

			// If "Support Postmark" is on
			if(get_option('postmarkapp_poweredby') == 1){
				// Check Content Type
				if(!strpos($headers, "text/html")){
					$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
				}
			}

			// Send Email
			if(!is_array($to)){
				$recipients = explode(",", $to);
			} else {
				$recipients = $to;
			}

			foreach($recipients as $recipient){
				// Construct Message
				$email = array();
				$email['To'] = $recipient;
				$email['From'] = get_option('postmarkapp_sender_address');
		    	$email['Subject'] = $subject;
		    	$email['TextBody'] = $message;

		    	if(strpos($headers, "text/html" ) || get_option('postmarkapp_force_html') == 1){
			    	$email['HtmlBody'] = $message;
		    	}

		    	if(get_option('postmarkapp_trackopens') == 1){
		    		$email['TrackOpens'] = "true";
		    	}

        		$response = pma_send_mail($postmark_headers, $email);
			}
			return $response;
		}
	}
}


function pma_send_test(){
	$email_address = $_POST['email'];

	// Define Headers
	$postmark_headers = array(
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
        'X-Postmark-Server-Token' => get_option('postmarkapp_api_key')
	);

	$message = 'This is a test email sent via Postmark from '.get_bloginfo('name').'.';
	$html_message = 'This is a test email sent via <strong>Postmark</strong> from '.get_bloginfo('name').'.';

	if(get_option('postmarkapp_poweredby') == 1){
		$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
		$html_message .= '<br /><br />Postmark solves your WordPress email problems. Send transactional email confidently using <a href="http://postmarkapp.com">Postmark</a>.';
	}
	
	$email = array();
	$email['To'] = $email_address;
	$email['From'] = get_option('postmarkapp_sender_address');
    $email['Subject'] = get_bloginfo('name').' Postmark Test';
    $email['TextBody'] = $message;
    
    if(get_option('postmarkapp_force_html') == 1){
    	$email['HtmlBody'] = $html_message;
	}

	if(get_option('postmarkapp_trackopens') == 1){
		$email['TrackOpens'] = "true";
	}

    $response = pma_send_mail($postmark_headers, $email);

    if ($response === false){
    	return "Test Failed with Error ".curl_error($curl);
    } else {
    	return "Test Sent";
   	}

    die();
}


function pma_send_mail($headers, $email){
	$args = array(
		'headers' => $headers,
		'body' => json_encode($email)
	);
	$response = wp_remote_post(POSTMARKAPP_ENDPOINT, $args);

	if($response['response']['code'] == 200) {
		return true;
	} else {
		return false;
	}
}

?>