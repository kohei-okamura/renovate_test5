<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-app data-layout-login>
    <v-main :class="$style.content">
      <div :class="$style.wrapper">
        <nuxt />
      </div>
    </v-main>
  </v-app>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { authenticated } from '~/middleware/authenticated'

export default defineComponent({
  name: 'LoginLayout',
  middleware: [authenticated()]
})
</script>

<style lang="scss" module>
.content {
  background-image: url('/images/login-background.jpg');
  background-position: center center;
  background-size: cover;
}

.wrapper {
  background-color: rgba(10, 10, 10, .6);
  height: 100%;
}
</style>
