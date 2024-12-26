import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useLoadingStore = defineStore('loading', () => {
  const isLoading = ref(true);

  function setIsLoading(status) {
    isLoading.value = status;
  }

  return { isLoading, setIsLoading }
})