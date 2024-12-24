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
    player1: props.match.votes.filter(vote => vote.voted_for_player_id == props.match.player1.id),
    player2: props.match.votes.filter(vote => vote.voted_for_player_id == props.match.player2.id),
    tie: props.match.votes.filter(vote => vote.voted_for_player_id == 0),
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
    <div 
      class="match-card__vote match-card__vote--player1">
      <div class="match-card__player">
        <div class="match-card__player-name">
          {{ match.player1.name }}
        </div>
      </div>

      <button 
        @click="handleVote(match.id, match.player1.id)"
        class="vote-button vote-button--player1"
        :class="{ 'vote-button--active': userIp && groupedVotes.player1.some(vote => vote.ip === userIp) }"
        :disabled="match.state !== 'open'">
        Gana {{ match.player1.name }}
      </button>
    </div>

    <div 
      class="match-card__vote match-card__vote--tie">
      <div 
        v-if="match.scores"
        class="match-card__score-box">
        <span class="match-card__score">{{ match.scores.split('-')[0] }}</span>
        -
        <span class="match-card__score">{{ match.scores.split('-')[1] }}</span>
      </div>

      <button 
        @click="handleVote(match.id, 0)"
        class="vote-button vote-button--tie"
        :class="{ 'vote-button--active': userIp && groupedVotes.tie.some(vote => vote.ip === userIp) }"
        :disabled="match.state !== 'open'">
        Empate
      </button>
    </div>
    
    <div
      class="match-card__vote match-card__vote--player2">
      <div class="match-card__player">
        <div class="match-card__player-name">
          {{ match.player2.name }}
        </div>
      </div>
      <button 
        @click="handleVote(match.id, match.player2.id)"
        class="vote-button vote-button--player2"
        :class="{ 'vote-button--active': userIp && groupedVotes.player2.some(vote => vote.ip === userIp) }"
        :disabled="match.state !== 'open'">
        Gana {{ match.player2.name }}
      </button>
    </div>

    <div class="match-card__percentage-bar">
      <div 
        v-if="groupedVotes.player1.length > 0"
        class="match-card__percentage-bar__item match-card__percentage-bar__item--player1"
        :style="{ width: `${getPercentage(groupedVotes.player1.length, groupedVotes.total)}%` }">
        {{ getPercentage(groupedVotes.player1.length, groupedVotes.total) }}% 
        ({{ groupedVotes.player1.length }})
      </div>
      <div 
        v-if="groupedVotes.tie.length > 0"
        class="match-card__percentage-bar__item match-card__percentage-bar__item--tie"
        :style="{ width: `${getPercentage(groupedVotes.tie.length, groupedVotes.total)}%` }">
        {{ getPercentage(groupedVotes.tie.length, groupedVotes.total) }}%
        ({{ groupedVotes.tie.length }})
      </div>
      <div 
        v-if="groupedVotes.player2.length > 0"
        class="match-card__percentage-bar__item match-card__percentage-bar__item--player2"
        :style="{ width: `${getPercentage(groupedVotes.player2.length, groupedVotes.total)}%` }">
        {{ getPercentage(groupedVotes.player2.length, groupedVotes.total) }}%
        ({{ groupedVotes.player2.length }})
      </div>
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
  &__result
    grid-column: 1 / span 3
    display: inline-block
    padding: 4px 8px
    border-radius: 4px
    background-color: #f0f0f0
    font-size: 0.8em
  &__player-name
    font-size: 20px
    font-weight: bold
  &__vote
    display: flex
    flex-direction: column
    justify-content: flex-end
    gap: 12px
    &--player1
      align-items: flex-start
    &--tie
      align-items: center
    &--player2
      align-items: flex-end
  &__score-box
    display: flex
    align-items: center
    justify-content: center
    gap: 4px
  &__score
    padding: 4px 8px
    border-radius: 4px
    background-color: #f0f0f0
    width: min-content
  &__percentage-bar
    grid-column: 1 / span 3
    display: flex
    border-radius: 4px
    overflow: hidden
    &__item
      text-align: center
      font-weight: bold
      padding: 4px 8px
      &--player1
        background-color: #3b82f6
        color: #fff
      &--tie
        background-color: #334155
        color: #fff
      &--player2
        background-color: #ef4444
        color: #fff

.vote-button
  padding: 8px 16px
  color: #2d2d2d
  border: none
  border-radius: 4px
  cursor: pointer
  transition: background-color 0.2s
  border: 2px solid transparent
  &:hover
    filter: brightness(1.1)
  &--player1
    border-color: #3b82f6
    &.vote-button--active
      background-color: #3b82f6
      color: #fff
  &--tie
    border-color: #334155
    &.vote-button--active
      background-color: #334155
      color: #fff
  &--player2
    border-color: #ef4444
    &.vote-button--active
      background-color: #ef4444
      color: #fff

.vote-button:disabled
  background-color: #cccccc
  cursor: not-allowed

.vote-button:disabled:hover
  background-color: #cccccc
</style>