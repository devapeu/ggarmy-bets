<script setup>
import { ref, onMounted } from 'vue'

const matches = ref([])
const participants = ref([])
const loading = ref(true)
const error = ref(null)

// Challonge API configuration - Move these to .env
const BASE_URL = `http://localhost:8000`

// Fetch matches from Challonge API
const fetchMatches = async (tournamentId) => {
  try {
    // Fetch participants
    const participantsResponse = await fetch(
      `${BASE_URL}/participants?tournamentId=${tournamentId}`, {
      headers: {
        'Content-Type': 'application/json', 
        'Origin': 'http://localhost:5173'
      }
    })

    if (!participantsResponse.ok) {
      throw new Error('Failed to fetch participants')
    }

    const participantsData = await participantsResponse.json()
    participants.value = participantsData.map(({ participant }) => ({
      id: participant.group_player_ids[0],
      name: participant.name
    }))

    // Fetch matches
    const matchesResponse = await fetch(
      `${BASE_URL}/matches?tournamentId=${tournamentId}`, {
      headers: {
        'Content-Type': 'application/json', 
        'Origin': 'http://localhost:5173'
      }
    })

    if (!matchesResponse.ok) {
      throw new Error('Failed to fetch matches')
    }

    const matchesData = await matchesResponse.json()
    matches.value = matchesData.map(({ match }) => ({
      id: match.id,
      player1: participants.value.find(p => p.id === match.player1_id),
      player2: participants.value.find(p => p.id === match.player2_id),
      odds: {
        tie: 0,
        player1: 0,
        player2: 0
      },
      state: match.state,
      scores: match.scores_csv
    }))

    loading.value = false
  } catch (err) {
    console.error('Error fetching data:', err)
    error.value = err.message
    loading.value = false
  }
}

// Handle voting
const handleVote = (matchId, player) => {
  const match = matches.value.find(m => m.id === matchId)
  if (match) {
    if (player === 'A') {
      match.votesPlayerA++
    } else {
      match.votesPlayerB++
    }
    // Here you would typically send the vote to your backend
  }
}

onMounted(() => {
  fetchMatches('qhwkxvmo')
})
</script>

<template>
  <div class="container">
    <h1>Liga 1 GGArmy - Bunkerbet</h1>
    
    <div v-if="loading" class="loading">
      Loading matches...
    </div>

    <div v-else-if="error" class="error">
      {{ error }}
    </div>
    
    <div v-else class="matches-grid">
      <div v-for="match in matches" :key="match.id" class="match-card">
        <div class="match-header">
          <h3>{{ match.player1.name }} vs {{ match.player2.name }}</h3>
          <span class="match-state">{{ match.state }}</span>
        </div>
        
        <div class="match-info">
          <p v-if="match.scores">Score: {{ match.scores }}</p>
        </div>
        
        <div class="match-votes">
          <div class="player-votes">
            <button 
              @click="handleVote(match.id, 'A')"
              class="vote-button"
              :disabled="match.state !== 'open'"
            >
              {{ match.player1.name }} {{ match.odds.player1 }}
            </button>
          </div>

          <div class="tie-votes">
            <button 
              @click="handleVote(match.id, 'tie')"
              class="vote-button"
              :disabled="match.state !== 'open'"
            >
              Tie {{ match.odds.tie }}
            </button>
          </div>
          
          <div class="player-votes">
            <button 
              @click="handleVote(match.id, 'B')"
              class="vote-button"
              :disabled="match.state !== 'open'"
            >
              {{ match.player2.name }} {{ match.odds.player2 }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.matches-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.match-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.match-header {
  margin-bottom: 16px;
}

.match-header h3 {
  margin: 0;
  color: #333;
}

.match-date {
  color: #666;
  font-size: 0.9em;
}

.match-votes {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.player-votes {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.vote-button {
  padding: 8px 16px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.vote-button:hover {
  background-color: #45a049;
}

.loading {
  text-align: center;
  margin-top: 40px;
  color: #666;
}

.error {
  color: #dc3545;
  text-align: center;
  margin-top: 40px;
}

.match-state {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f0f0f0;
  font-size: 0.8em;
  margin-left: 8px;
}

.vote-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.vote-button:disabled:hover {
  background-color: #cccccc;
}

.match-info {
  margin: 10px 0;
  padding: 8px;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.match-info p {
  margin: 4px 0;
  font-size: 0.9em;
  color: #666;
}
</style>
