import { ref } from 'vue'
import { defineStore } from 'pinia'

const BASE_URL = `http://localhost:8000`

export const useChallongeStore = defineStore('challonge', () => {
  const matches = ref([])

  const fetchMatches = async (tournamentId) => {
    await fetch(`${BASE_URL}/matches?tournamentId=${tournamentId}`)
      .then(response => response.json())
      .then(data => (matches.value = data))
      .catch(error => {
        console.error('Error fetching matches:', error)
      })
  }

  const sendVote = async (tournamentId, matchId, playerId, ip) => {
    await fetch(`${BASE_URL}/send-vote?tournamentId=${tournamentId}&matchId=${matchId}&playerId=${playerId}&ip=${ip}`)
      .then(response => response.json())
      .then(data => console.log(data))
      .catch(error => {
        console.error('Error sending vote:', error)
      })
  }

  function updateMatchVote(matchId, vote) {
    const match = matches.value.find(m => m.id === matchId)
    if (match) {
      match.votes.push(vote)
    }
  }

  return { matches, fetchMatches, sendVote, updateMatchVote }
})
