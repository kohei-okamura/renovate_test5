/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'

export default defineNuxtPlugin((_, inject) => {
  inject('google', () => {
    if (window.google) {
      return Promise.resolve(window.google)
    } else {
      return new Promise(resolve => {
        const callback = () => {
          window.google ? resolve(window.google) : setTimeout(callback, 100)
        }
        const key = process.env.googleMapsApiKey ?? ''
        const version = process.env.googleMapsApiVersion ?? ''
        const script = document.createElement('script')
        script.async = true
        script.src = `https://maps.googleapis.com/maps/api/js?key=${key}&v=${version}`
        script.onload = callback
        document.head.appendChild(script)
      })
    }
  })
})
