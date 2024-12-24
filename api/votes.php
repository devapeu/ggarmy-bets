<?php

require_once 'db.php';

function getVotes($tournamentId) {
  $sql = "SELECT * FROM votes WHERE tournament_id = :tournament_id";
  $stmt = queryDatabase($sql, ['tournament_id' => $tournamentId]);
  $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $votes; 
}

function addVote($tournamentId, $matchId, $playerId, $ip) {
    $sql = "INSERT INTO votes (match_id, voted_for_player_id, tournament_id, ip) 
            VALUES (:match_id, :voted_for_player_id, :tournament_id, :ip)
            ON DUPLICATE KEY UPDATE 
                voted_for_player_id = VALUES(voted_for_player_id),
                updated_at = CURRENT_TIMESTAMP";
                
    $stmt = queryDatabase($sql, [
        'match_id' => $matchId, 
        'voted_for_player_id' => $playerId, 
        'tournament_id' => $tournamentId, 
        'ip' => $ip
    ]);

    if ($GLOBALS['pdo']->lastInsertId() == 0) {
        $sql = "SELECT id FROM votes WHERE match_id = :match_id AND ip = :ip";
        $stmt = queryDatabase($sql, ['match_id' => $matchId, 'ip' => $ip]);
        $existingVote = $stmt->fetch(PDO::FETCH_ASSOC);
        $newVoteId = $existingVote['id'];
    } else {
        $newVoteId = $GLOBALS['pdo']->lastInsertId();
    }

    // Get the complete vote record
    $sql = "SELECT * FROM votes WHERE id = :id";
    $stmt = queryDatabase($sql, ['id' => $newVoteId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}