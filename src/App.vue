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
      match.odds.player1 = match.odds.player1 + 1
    } else if (player === 'tie') {
      match.odds.tie = match.odds.tie + 1
    } else {
      match.odds.player2 = match.odds.player2 + 1
    }
  }
}

function getPercentage(value, total) {
  const sum = Object.values(total).reduce((acc, curr) => acc + curr, 0)
  if (sum === 0) return 0
  return Math.round((value / sum) * 100)
}

onMounted(() => {
  fetchMatches('qhwkxvmo')
})
</script>

<template>
  <div class="container">
    <h1>Liga 1 GGArmy - Bunkerbet</h1>
    
    <div v-if="loading" class="loading">
      Cargando partidas...
    </div>

    <div v-else-if="error" class="error">
      {{ error }}
    </div>
    
    <div v-else class="matches-grid">
      <div v-for="match in matches" :key="match.id" class="match-card">
        <div 
          class="match-card__result"
          :class="{ 'match-card__result--complete': match.state === 'complete' }">
          {{ match.state === 'complete' ? 'Completo' : 'Abierto' }}
        </div>
        <div class="match-card__vote match-card__vote--player1">
          <div class="match-card__player">
            <div v-if="match.scores" class="match-card__score">
              {{ match.scores.split('-')[0] }}
            </div>
            {{ match.player1.name }}
          </div>
          <div class="match-card__odds">
            {{ getPercentage(match.odds.player1, match.odds) }}
            ({{ match.odds.player1 }})
          </div>
          <button 
            @click="handleVote(match.id, 'A')"
            class="vote-button"
            :disabled="match.state !== 'open'">
            Gana {{ match.player1.name }}
          </button>
        </div>

        <div class="match-card__vote match-card__vote--tie">
          <div class="match-card__odds">
            {{ getPercentage(match.odds.tie, match.odds) }}
            ({{ match.odds.tie }})
          </div>
          <button 
            @click="handleVote(match.id, 'tie')"
            class="vote-button"
            :disabled="match.state !== 'open'">
            Empate
          </button>
        </div>
        
        <div class="match-card__vote match-card__vote--player2">
          <div class="match-card__player">
            <div v-if="match.scores" class="match-card__score">
              {{ match.scores.split('-')[1] }}
            </div>
            {{ match.player2.name }}
          </div>
          <div class="match-card__odds">
            {{ getPercentage(match.odds.player2, match.odds) }}
            ({{ match.odds.player2 }})
          </div>
          <button 
            @click="handleVote(match.id, 'B')"
            class="vote-button"
            :disabled="match.state !== 'open'">
            Gana {{ match.player2.name }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="sass" scoped>
.container
  max-width: 1200px
  margin: 0 auto
  padding: 20px

.matches-grid
  display: grid
  grid-template-columns: 1fr
  gap: 20px
  margin-top: 20px

.match-card
  border: 1px solid #ddd
  border-radius: 8px
  padding: 16px
  background-color: #fff
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1)
  display: grid
  grid-template-columns: 1fr 1fr 1fr
  gap: 12px
  @media (max-width: 768px)
    grid-template-columns: 1fr
  &__result
    grid-column: 1 / span 3
    display: inline-block
    padding: 4px 8px
    border-radius: 4px
    background-color: #f0f0f0
    font-size: 0.8em
  &__vote
    display: flex
    flex-direction: column
    justify-content: flex-end
    &--player1
      align-items: flex-start
    &--tie
      align-items: center
    &--player2
      align-items: flex-end
  &__score
    padding: 4px 8px
    border-radius: 4px
    background-color: #f0f0f0
    width: min-content

.vote-button
  padding: 8px 16px
  background-color: #4CAF50
  color: white
  border: none
  border-radius: 4px
  cursor: pointer
  transition: background-color 0.2s
  &:hover
    background-color: #45a049

.loading
  text-align: center
  margin-top: 40px
  color: #666

.error
  color: #dc3545
  text-align: center
  margin-top: 40px

.vote-button:disabled
  background-color: #cccccc
  cursor: not-allowed

.vote-button:disabled:hover
  background-color: #cccccc
</style>
