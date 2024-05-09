<?php
/*
Plugin Name: WP IP Block
Description: This plugin restricts admin access to a specified set of IP addresses
Version: 1.0
Author: Andrew Drake <andrew@drake.nz>
License: MIT License
*/

// Add a new settings page
add_action('admin_menu', 'wp_ip_block_menu');

function wp_ip_block_menu() {
	add_options_page('WP IP Block Settings', 'WP IP Block', 'manage_options', 'wp-ip-block', 'wp_ip_block_options');
}

// Display the settings page
function wp_ip_block_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Save the IP addresses if the form has been submitted
    if (isset($_POST['ip_addresses'])) {
        update_option('wp_ip_block_addresses', $_POST['ip_addresses']);
    }

    // Get the saved IP addresses
    $ip_addresses = get_option('wp_ip_block_addresses', '');

    echo '<div class="wrap">';
    echo '<h2>WP IP Block</h2>';
    echo '<form method="post" action="">';
    echo '<p><label for="ip_addresses">Allowed IP Addresses (one per line):</label></p>';
    echo '<p><textarea id="ip_addresses" name="ip_addresses" rows="10" cols="50">' . esc_textarea($ip_addresses) . '</textarea></p>';
    echo '<p><input type="submit" value="Save Changes" class="button button-primary"></p>';
    echo '</form>';

    // Instructions for .htaccess IP block
    echo '<h3>.htaccess IP Block Instructions</h3>';
    echo '<p>For extra security, to block all IP addresses except those listed above from accessing the wp-admin directory, add the following to your .htaccess file:</p>';
    echo '<pre>';
    echo "order deny,allow\n";
    echo "deny from all\n";
    foreach (explode("\n", $ip_addresses) as $ip) {
        echo "allow from " . trim($ip) . "\n";
    }
    echo '</pre>';
    echo '</div>';
}

// Check if the user is trying to access the admin area
add_action('admin_init', 'wp_ip_block_check');

function wp_ip_block_check() {
	// Get the saved IP addresses
	$ip_addresses = get_option('wp_ip_block_addresses', '');

	// Convert the IP addresses to an array
	$allowed_ips = array_map('trim', explode("\n", $ip_addresses));

	// Get the user's IP address
	$user_ip = $_SERVER['REMOTE_ADDR'];

	// If the user's IP address is not in the list of allowed IP addresses, redirect them to the home page
	if (!in_array($user_ip, $allowed_ips)) {
		wp_redirect(home_url());
		exit;
	}
}