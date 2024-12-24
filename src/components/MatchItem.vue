<script setup>
import { ref, computed, onMounted } from 'vue'
import { useIpStore } from '../stores/useIp'

const ipStore = useIpStore()

const props = defineProps({
  match: {
    type: Object,
    required: true
  }
})

const groupedVotes = computed(() => {
  return {
    player1: props.match.votes.filter(vote => vote.voted_for_player_id === props.match.player1.id),
    player2: props.match.votes.filter(vote => vote.voted_for_player_id === props.match.player2.id),
    tie: props.match.votes.filter(vote => vote.voted_for_player_id === 0),
    total: props.match.votes.length,
  }
})

const userIp = computed(() => ipStore.ip)

function getPercentage(value, total) {
  if (total === 0) return 0
  return Math.round((value / total) * 100)
}

const emit = defineEmits(['vote'])

const handleVote = (matchId, playerId) => {
  emit('vote', matchId, playerId, userIp.value)
}
</script>

<template>
  <div class="match-card">
    Round {{ match.round }}
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
        {{ getPercentage(groupedVotes.player1.length, groupedVotes.total) }}
        ({{ groupedVotes.player1.length }})
      </div>
      <button 
        @click="handleVote(match.id, match.player1.id)"
        class="vote-button"
        :disabled="match.state !== 'open'">
        Gana {{ match.player1.name }}
      </button>
    </div>

    <div class="match-card__vote match-card__vote--tie">
      <div class="match-card__odds">
        {{ getPercentage(groupedVotes.tie.length, groupedVotes.total) }}
        ({{ groupedVotes.tie.length }})
      </div>
      <button 
        @click="handleVote(match.id, 0)"
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
        {{ getPercentage(groupedVotes.player2.length, groupedVotes.total) }}
        ({{ groupedVotes.player2.length }})
      </div>
      <button 
        @click="handleVote(match.id, match.player2.id)"
        class="vote-button"
        :disabled="match.state !== 'open'">
        Gana {{ match.player2.name }}
      </button>
    </div>
  </div>
</template>

<style lang="sass" scoped>

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

.vote-button:disabled
  background-color: #cccccc
  cursor: not-allowed

.vote-button:disabled:hover
  background-color: #cccccc
</style>