<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container data-page-index fill-height>
    <v-row align-content="center" justify="center" no-gutters>
      <v-col cols="12" lg="4" md="5" sm="8" xl="3">
        <z-index-page-form
          :errors="errors"
          :progress="progress"
          :has-unauthorized-error="hasUnauthorizedError"
          @submit="submit"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref } from '@nuxtjs/composition-api'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { useSessionStore } from '~/composables/stores/use-session-store'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { SessionsApi } from '~/services/api/sessions-api'

type Form = Partial<SessionsApi.Form>

export default defineComponent({
  name: 'IndexPage',
  layout: 'login',
  setup () {
    const { errors, progress, withAxios } = useAxios()
    const { $router, $route } = usePlugins()
    const hasUnauthorizedError = ref(false)
    const session = useSessionStore()
    const submit = async (form: Form) => {
      hasUnauthorizedError.value = false
      await withAxios(
        () => session.create({ form }),
        () => { hasUnauthorizedError.value = true }
      )
      if (session.state.isActive.value) {
        const path = $route.query.path
        await catchErrorStack(() => $router.push(Array.isArray(path) || !path ? '/dashboard' : path))
      }
    }
    return {
      hasUnauthorizedError,
      progress,
      errors,
      submit
    }
  },
  head: () => ({
    title: 'ログイン'
  })
})
</script>
