<script setup>
import { ref, onMounted, computed } from 'vue'
import { useChallongeStore } from './stores/useChallonge'
import { useIpStore } from './stores/useIp'
import MatchItem from './components/MatchItem.vue'

const challongeStore = useChallongeStore()
const ipStore = useIpStore()

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
const handleVote = (matchId, playerId, ip) => {
  challongeStore.sendVote('qhwkxvmo', matchId, playerId, ip)
}

onMounted(() => {
  fetchMatches('qhwkxvmo');
  ipStore.getUserIp();
})
</script>

<template>
  <header class="header">
    <div class="container">
      <h1>BunkerBet</h1>
    </div>
  </header>
  <main class="container">
    <div class="tournament-header">
      <h2>Liga 1 GGArmy</h2>
      <a href="https://challonge.com/qhwkxvmo" target="_blank">
        Ver en Challonge
      </a>
    </div>
    <div v-if="loading" class="loading">
      Cargando partidas...
    </div>

    <div v-else-if="error" class="error">
      {{ error }}
    </div>
    
    <div v-else class="matches-grid">
      <MatchItem v-for="match in matches" :key="match.id" :match="match" @vote="handleVote" />
    </div>
  </main>
</template>

<style lang="sass" scoped>

.header
  margin-bottom: 20px
  background-color: #1e293b
  color: #f8fafc
  padding: 8px
  h1
    font-size: 16px
    font-weight: 700

.container
  max-width: 540px
  margin: 0 auto
  padding: 0 16px
  @media (max-width: 540px)
    padding: 0 12px

.tournament-header
  display: flex
  justify-content: space-between
  align-items: center
  margin-bottom: 20px

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
