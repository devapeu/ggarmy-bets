<script setup>
import { ref, onMounted, computed } from 'vue'
import { useChallongeStore } from './stores/useChallonge'
import MatchItem from './components/MatchItem.vue'

const challongeStore = useChallongeStore()

const matches = computed(() => challongeStore.matches)
const loading = ref(true)
const error = ref(null)

// Fetch matches from Challonge API
const fetchMatches = async (tournamentId) => {
  try {
    await challongeStore.fetchMatches(tournamentId)
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
      <MatchItem v-for="match in matches" :key="match.id" :match="match" />
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

.loading
  text-align: center
  margin-top: 40px
  color: #666

.error
  color: #dc3545
  text-align: center
  margin-top: 40px
</style>
