<?php
// index.php

require_once 'vendor/autoload.php'; // Autoload Facebook SDK

session_start();

$fb = new \Facebook\Facebook([
  'app_id' => '1226695641844806', // Replace with your App ID
  'app_secret' => '104baee7034e5b319bb3cdc6cfa28939', // Replace with your App Secret
  'default_graph_version' => 'v20.0',
]);

$helper = $fb->getRedirectLoginHelper();

// Define the permissions you need
$permissions = ['email', 'public_profile', 'pages_show_list']; // Add other permissions as needed

// The URL to redirect back to after login
$callbackUrl = htmlspecialchars('http://localhost:8000/facebook-auth-callback.php');

// Get the login URL
$loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

// Redirect the user to Facebook's OAuth dialog
header('Location: ' . $loginUrl);
exit();
?>
