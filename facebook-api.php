<?php
// facebook-api.php

// Start the session to manage user state
session_start();
require_once 'vendor/autoload.php'; // Autoload Facebook SDK (Ensure this path is correct)

// Set CORS headers to allow requests from your React frontend
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Facebook App credentials
$fbAppId = '1226695641844806';
$fbAppSecret = '104baee7034e5b319bb3cdc6cfa28939';
$fbRedirectUrl = 'http://localhost:3000/'; // Adjust to your React app's URL

// Initialize Facebook SDK
$fb = new \Facebook\Facebook([
    'app_id' => $fbAppId,
    'app_secret' => $fbAppSecret,
    'default_graph_version' => 'v17.0',
]);

// Get access token from session or redirect to login
if (!isset($_SESSION['fb_access_token'])) {
    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email', 'public_profile', 'pages_show_list']; // Adjust based on required permissions
    $loginUrl = $helper->getLoginUrl($fbRedirectUrl, $permissions);

    echo json_encode(['loginUrl' => $loginUrl]);
    exit;
}

$accessToken = $_SESSION['fb_access_token'];

try {
    // Fetch user profile
    $response = $fb->get('/me?fields=id,name,picture', $accessToken);
    $user = $response->getGraphUser();

    // Fetch user pages
    $pagesResponse = $fb->get('/me/accounts', $accessToken);
    $pages = $pagesResponse->getGraphEdge()->asArray();

    // Respond with the profile and pages data in JSON format
    echo json_encode([
        'profile' => [
            'name' => $user['name'],
            'picture' => $user['picture']['url'],
        ],
        'pages' => array_map(function ($page) {
            // Initialize fields with empty values if not present
            $followers = isset($page['fan_count']) ? $page['fan_count'] : 'Data not available';
            $engagement = isset($page['engagement']) ? $page['engagement'] : 'Data not available';
            $impressions = isset($page['impressions']) ? $page['impressions'] : 'Data not available';
            $reactions = isset($page['reactions']) ? $page['reactions'] : 'Data not available';

            return [
                'id' => $page['id'],
                'name' => $page['name'],
                'followers' => $followers,
                'engagement' => $engagement,
                'impressions' => $impressions,
                'reactions' => $reactions,
            ];
        }, $pages)
    ]);
    exit;

} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // Graph API returned an error
    echo json_encode(['error' => 'Graph returned an error: ' . $e->getMessage()]);
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // Facebook SDK returned an error
    echo json_encode(['error' => 'Facebook SDK returned an error: ' . $e->getMessage()]);
    exit;
}

// Handle other routes or return a 404 error if the route is not found
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
