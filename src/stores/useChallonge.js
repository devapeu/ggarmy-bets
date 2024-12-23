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

  return { matches, fetchMatches }
})
