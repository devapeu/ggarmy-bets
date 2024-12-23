import { ref } from 'vue'
import { defineStore } from 'pinia'

const BASE_URL = `http://localhost:8000`

export const useChallongeStore = defineStore('challonge', () => {
  const matches = ref([])
  const participants = ref([])

  const fetchMatches = async (tournamentId) => {
    await fetchParticipants(tournamentId)
    await fetch(`${BASE_URL}/matches?tournamentId=${tournamentId}`)
      .then(response => response.json())
      .then(data => {
        matches.value = data.map(({ match }) => ({
          id: match.id,
          player1: participants.value.find(p => p.id === match.player1_id),
          player2: participants.value.find(p => p.id === match.player2_id),
          odds: {
            tie: 0,
            player1: 0,
            player2: 0
          },
          state: match.state,
          scores: match.scores_csv,
          identifier: match.identifier,
          round: match.round,
        })).sort((a, b) => {
          if (a.state === 'complete' && b.state !== 'complete') return 1;
          if (a.state !== 'complete' && b.state === 'complete') return -1;
          if (a.state === b.state) {
            if (a.identifier.length !== b.identifier.length) {
              return a.identifier.length - b.identifier.length;
            }
            return a.identifier.localeCompare(b.identifier);
          }
          return 0;
        })
      })
      .catch(error => {
        console.error('Error fetching matches:', error)
      })
  }

  const fetchParticipants = async (tournamentId) => {
    await fetch(`${BASE_URL}/participants?tournamentId=${tournamentId}`)
      .then(response => response.json())
      .then(data => participants.value = data.map(({ participant }) => ({
        id: participant.group_player_ids[0],
        name: participant.name,
      })))
      .catch(error => {
        console.error('Error fetching participants:', error)
      })
  }

  return { matches, participants, fetchMatches, fetchParticipants }
})
