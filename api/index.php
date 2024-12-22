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

# Check if tournament ID is provided, must be provided for all requests
if (!$tournamentId) {
    http_response_code(400);
    echo json_encode(['error' => 'Tournament ID is required']);
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

  if ($httpCode !== 200) {
      http_response_code($httpCode);
      echo json_encode(['error' => 'Failed to fetch data']);
      exit;
  }

  curl_close($ch);
  echo $response;
}

# API endpoints
if ($uri === '/matches') {
    $apiURL = "https://api.challonge.com/v1/tournaments/{$tournamentId}/matches.json";
    getCurl($apiURL, $challongeApiKey);
}

else if ($uri === '/participants') {
    $apiURL = "https://api.challonge.com/v1/tournaments/{$tournamentId}/participants.json";
    getCurl($apiURL, $challongeApiKey);
}

else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}