<?php

add_action( 'phpmailer_init', 'vtm_phpmailer_setup' );
function vtm_phpmailer_setup( $phpmailer ) {
	
	$method   = get_option( 'vtm_method', 'mail' );

	if ($method == 'smtp') {
		$fromname = get_option( 'vtm_replyto_name', "Website");
		
		$smtphost   = get_option( 'vtm_smtp_host',     '' );
		$smtpport   = get_option( 'vtm_smtp_port',     '25' );
		$smtpuser   = get_option( 'vtm_smtp_username', '' );
		$smtpauth   = get_option( 'vtm_smtp_auth',     'true' );
		$smtpsecure = get_option( 'vtm_smtp_secure',   'ssl' );
		$smtppw     = get_option( 'vtm_smtp_pw',       '' );
		
		$smtpauth = $smtpauth == 'true' ? true : false;
		
		/*echo "<li>smtphost $smtphost</li>";
		echo "<li>smtpport $smtpport</li>";
		echo "<li>smtpuser $smtpuser</li>";
		echo "<li>smtpauth $smtpauth</li>";
		echo "<li>smtpsecure $smtpsecure</li>";
		echo "<li>smtppw $smtppw</li>"; */

		$phpmailer->isSMTP();     
		$phpmailer->Host = $smtphost;
		$phpmailer->SMTPAuth = $smtpauth; // Force it to use Username and Password to authenticate
		$phpmailer->Port = $smtpport;
		$phpmailer->Username = $smtpuser;
		$phpmailer->Password = $smtppw;
		$phpmailer->SMTPSecure = $smtpsecure;                 // sets the prefix to the server
		
		$phpmailer->SetFrom($smtpuser, $fromname);

		}
	elseif ($method != 'mail') {
		echo "<p>Unknown mail transport method '$method'</p>";
	}
}

function vtm_mail_content_type( $content_type ) {
	return 'text/html';
}

function vtm_test_email($email) {
	
	$result = vtm_send_email($email, "Test Email", "This is a test email\n");
	
	if ($result)
		echo "<p style='color:green;'>Email sent to $email</p>";
	else
		echo "<p style='color:red;'>Failed to send email to $email</p>\n";
	
}

function vtm_send_email($email, $subject, $content) {
	
	$tag      = get_option( 'vtm_emailtag' );
	$fromname = get_option( 'vtm_replyto_name', "Website");
	$replyto  = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );

	$subject  = stripslashes("$tag $subject");
	
	$signature = "View your character: <a href='" . vtm_get_stlink_url('viewCharSheet') . "'>" . vtm_get_stlink_url('viewCharSheet') . "</a><br>" .
		"Spend Experience: <a href='" . vtm_get_stlink_url('viewXPSpend') . "'>" . vtm_get_stlink_url('viewXPSpend') . "</a><br>";
	if (get_option( 'vtm_feature_news', '0' ) == '1')
		$signature .= "Opt-in/out of newsletter: <a href='" . vtm_get_stlink_url('viewProfile') . "'>" . vtm_get_stlink_url('viewProfile') . "</a>";
	
	//$signature  = get_option( 'vtm_email_signature', '');
	$font       = get_option( 'vtm_email_font', 'Arial');
	$background = get_option( 'vtm_email_background', '#FFFFFF');
	$textcolor  = get_option( 'vtm_email_textcolor', '#000000');
	$linecolor  = get_option( 'vtm_email_linecolor', '#000000');
	
	$method   = get_option( 'vtm_method', 'mail' );
	if ($method == 'mail')
		$headers[] = "From: \"$fromname\" <$replyto>\n";
	$headers[] = "Reply-To: \"$fromname\" <$replyto>";
	
	// Email template
	$path = locate_template("vtmemail.php");
	if (file_exists($path)) {
		$template = $path;
	} else {
		$path = VTM_CHARACTER_URL . '/templates/vtmemail.html';
		if (file_exists($path)) {
			$template = $path;
		} else {
			print "<p>Failed to identify email template from $path or " . locate_template("vtmemail.html") . "</p>";
			return false;
		}
	}

	$body = file_get_contents($template);
	if ($body === false) {
		print "<p>Failed to read email template from $template</p>";
		return false;
	}
	
	
	// Replace macros with content, etc
	$body = preg_replace("/\[Subject\]/", $subject, $body);
	$body = preg_replace("/\[Font\]/", $font, $body);
	$body = preg_replace("/\[BackgroundColor\]/", $background, $body);
	$body = preg_replace("/\[TextColor\]/", $textcolor, $body);
	$body = preg_replace("/\[LineColor\]/", $linecolor, $body);
	$body = preg_replace("/\[Signature\]/", $signature, $body);
	$body = preg_replace("/\[MessageBody\]/", stripslashes($content), $body);
	
	add_filter( 'wp_mail_content_type', 'vtm_mail_content_type' );
	$result = wp_mail($email, $subject, $body, $headers);
	remove_filter( 'wp_mail_content_type', 'vtm_mail_content_type' );
	
	if (!$result) {
			global $phpmailer;
			print ("<pre>");
			print_r($phpmailer->ErrorInfo);
			print ("</pre>");
	}
	
	return $result;
}

?>