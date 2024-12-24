import { ref } from 'vue'
import { defineStore } from 'pinia'
import CryptoJS from 'crypto-js'

export const useIpStore = defineStore('ip', () => {
  const ip = ref(null)

  const hashIp = (ip) => {
    const SECRET_KEY = '1234567890'
    return CryptoJS.SHA256(ip + SECRET_KEY).toString()
  }

  async function getUserIp() {
    try {
      const response = await fetch('https://api.ipify.org?format=json')
      const data = await response.json()
      ip.value = hashIp(data.ip)
    } catch (error) {
      console.error('Error fetching IP:', error)
    }
  }

  return { ip, getUserIp }
})