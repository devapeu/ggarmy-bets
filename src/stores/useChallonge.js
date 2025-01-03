import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useLoadingStore } from './useLoading'

const BASE_URL = import.meta.env.VITE_API_URL

export const useChallongeStore = defineStore('challonge', () => {
  const matches = ref([])
  const loadingStore = useLoadingStore();

  const fetchMatches = async (tournamentId) => {
    await fetch(`${BASE_URL}/matches?tournamentId=${tournamentId}`)
      .then(response => response.json())
      .then(data => (matches.value = data))
      .catch(error => {
        console.error('Error fetching matches:', error)
      })
  }

  const sendVote = async (tournamentId, matchId, playerId, ip) => {
    loadingStore.setIsLoading(true);
    await fetch(`${BASE_URL}/send-vote?tournamentId=${tournamentId}&matchId=${matchId}&playerId=${playerId}&ip=${ip}`)
      .then(response => response.json())
      .then(data => updateMatchVote(matchId, data))
      .catch(error => {
        console.error('Error sending vote:', error)
      })
  }

  function updateMatchVote(matchId, vote) {
    const match = matches.value.find(m => m.id === matchId)
    if (match) {
      const existingVoteIndex = match.votes.findIndex(v => v.ip === vote.ip)
      if (existingVoteIndex !== -1) {
        match.votes[existingVoteIndex] = vote
      } else {
        match.votes.push(vote)
      }
      loadingStore.setIsLoading(false);
    }
  }

  return { matches, fetchMatches, sendVote, updateMatchVote }
})
