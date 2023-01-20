<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card title="利用者情報">
    <z-data-card-item label="利用者名" :icon="$icons.user">
      <nuxt-link v-if="isAuthorized([permissions.viewUsers])" :to="`/users/${user.id}`">
        {{ user.name.displayName }}
      </nuxt-link>
      <template v-else>{{ user.name.displayName }}</template>
    </z-data-card-item>
    <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(user.sex)" />
    <z-data-card-item label="生年月日" :icon="$icons.birthday">
      <z-era-date :value="user.birthday" />
    </z-data-card-item>
  </z-data-card>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveSex } from '@zinger/enums/lib/sex'
import { eraDate } from '~/composables/era-date'
import { useAuth } from '~/composables/use-auth'

export default defineComponent({
  name: 'ZUserCard',
  props: {
    user: { type: Object, required: true }
  },
  setup: () => {
    return {
      ...useAuth(),
      eraDate,
      resolveSex
    }
  }
})
</script>
