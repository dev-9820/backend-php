<?php
// facebook-auth-callback.php

session_start();
require_once 'vendor/autoload.php'; // Autoload Facebook SDK

$fb = new \Facebook\Facebook([
  'app_id' => '1226695641844806', // Replace with your App ID
  'app_secret' => '104baee7034e5b319bb3cdc6cfa28939', // Replace with your App Secret
  'default_graph_version' => 'v20.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
  // Get the access token from the callback
  $accessToken = $helper->getAccessToken();
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (!isset($accessToken)) {
  if ($helper->getError()) {
    // The user denied the request
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    // Some other error
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

// Logged in
// Store the access token in the session
$_SESSION['fb_access_token'] = (string) $accessToken;

// Exchange short-lived token for long-lived token
$oAuth2Client = $fb->getOAuth2Client();

try {
  $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  $_SESSION['fb_access_token'] = (string) $longLivedAccessToken;
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
  echo "Error getting long-lived access token: " . $e->getMessage();
  exit;
}

// Redirect to the React frontend
header('Location: http://localhost:3000/profile');
exit();
?>
