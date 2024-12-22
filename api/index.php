<?php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Load environment variables from .env file
require_once __DIR__ . '/config.php';

$challongeApiKey = $CHALLONGE_API_KEY;
$tournamentId = $_GET['tournamentId'] ?? null;
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define constants
define('CHALLONGE_API_BASE', 'https://api.challonge.com/v1');

// Helper function for HTTP responses
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function getCurl($url, $apiKey) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url . "?api_key=" . $apiKey,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        sendResponse(['error' => 'CURL Error: ' . curl_error($ch)], 500);
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        sendResponse(['error' => 'Challonge API Error', 'status' => $httpCode], $httpCode);
    }
    
    return $response;
}

// API endpoints
switch ($uri) {
    case '/matches':
        $apiURL = CHALLONGE_API_BASE . "/tournaments/{$tournamentId}/matches.json";
        $response = getCurl($apiURL, $challongeApiKey);
        sendResponse(json_decode($response, true));
        break;
        
    case '/participants':
        $apiURL = CHALLONGE_API_BASE . "/tournaments/{$tournamentId}/participants.json";
        $response = getCurl($apiURL, $challongeApiKey);
        sendResponse(json_decode($response, true));
        break;
        
    default:
        sendResponse(['error' => 'Not found'], 404);
}