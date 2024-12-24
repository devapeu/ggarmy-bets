<?php
  // Load environment variables from .env file
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/votes.php';

header('Access-Control-Allow-Origin: ' . $FRONTEND_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


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
        $matchesResponse = json_decode(getCurl($apiURL, $challongeApiKey), true);
        
        // Get participants first since we need them for the matches
        $participantsURL = CHALLONGE_API_BASE . "/tournaments/{$tournamentId}/participants.json";
        $participantsResponse = json_decode(getCurl($participantsURL, $challongeApiKey), true);
        $participants = array_column($participantsResponse, 'participant');

        // Get votes
        $votes = getVotes($tournamentId);

        function getPlayer($participants, $playerId) {
            $player = array_values(array_filter($participants, fn($p) => in_array($playerId, $p['group_player_ids'])))[0] ?? null;
            return $player ? ['name' => $player['name'], 'id' => $player['id']] : null;
        }

        // Filter and transform matches
        $filteredMatches = array_map(function($matchData) use ($participants, $votes) {
            $match = $matchData['match'];
            return [
                'id' => $match['id'],
                'player1' => getPlayer($participants, $match['player1_id']),
                'player2' => getPlayer($participants, $match['player2_id']),
                'votes' => $votes ? array_values(array_filter($votes, fn($v) => (string)$v['match_id'] === (string)$match['id'])) : [],
                'state' => $match['state'],
                'scores' => $match['scores_csv'],
                'identifier' => $match['identifier'],
                'round' => $match['round']
            ];
        }, $matchesResponse);
        
        // Sort matches
        usort($filteredMatches, function($a, $b) {
            // Complete matches go last
            if ($a['state'] === 'complete' && $b['state'] !== 'complete') return 1;
            if ($a['state'] !== 'complete' && $b['state'] === 'complete') return -1;
            
            if ($a['state'] === $b['state']) {
                // Sort by identifier length first
                $lenDiff = strlen($a['identifier']) - strlen($b['identifier']);
                if ($lenDiff !== 0) return $lenDiff;
                
                // Then sort alphabetically by identifier
                return strcmp($a['identifier'], $b['identifier']);
            }
            return 0;
        });
        
        sendResponse($filteredMatches);
        break;
        
    case '/participants':
        $apiURL = CHALLONGE_API_BASE . "/tournaments/{$tournamentId}/participants.json";
        $response = getCurl($apiURL, $challongeApiKey);
        sendResponse(json_decode($response, true));
        break;

    case '/send-vote':
        $matchId = $_GET['matchId'] ?? null;
        $playerId = $_GET['playerId'] ?? null;
        $tournamentId = $_GET['tournamentId'] ?? null;
        $ip = $_GET['ip'] ?? null;
        $vote = addVote($tournamentId, $matchId, $playerId, $ip);
        sendResponse($vote);
        break;
        
    default:
        sendResponse(['error' => 'Not found'], 404);
}