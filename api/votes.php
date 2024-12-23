<?php

function getVotes($tournamentId) {
  $sql = "SELECT * FROM votes WHERE tournament_id = :tournament_id";
  $stmt = queryDatabase($sql, ['tournament_id' => $tournamentId]);
  $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $votes; 
}

function addVote($matchId, $playerId, $ip) {
  $sql = "INSERT INTO votes (match_id, player_id, ip) VALUES (:match_id, :player_id, :ip)";
  $stmt = queryDatabase($sql, ['match_id' => $matchId, 'player_id' => $playerId, 'ip' => $ip]);
}