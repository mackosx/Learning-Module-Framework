<?php
header('Content-type: application/json');
/**
 * Template Name: Badge Issuer Profile page
 * Description: Outputs the profile information as JSON
 *
 */
global $wpdb;
$options_table = $wpdb->prefix . 'options';
$results = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='issuer_profile_options'", ARRAY_A);
// Should only be one row
$data = $results[0]['option_value'];
$obj = unserialize($data);

$obj['type'] = 'Issuer';
$obj['id'] = 'http://localhost/wordpress/?page_id=104';
$wpdb->close();
echo json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);