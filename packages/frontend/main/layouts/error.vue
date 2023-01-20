<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container fill-height>
    <v-row align-content="center" justify="center">
      <v-col cols="12" lg="4" md="5" sm="8" xl="3">
        <v-card>
          <v-card-title class="justify-center">
            <h1 v-if="error.statusCode === 404" class="text-h3">Not Found</h1>
            <h1 v-else class="text-h6">エラーが発生しました、管理者へのお問い合わせをお願いします。</h1>
          </v-card-title>
          <v-card-text>
            <div class="text-center">{{ error.message }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
export default {
  layout: 'login',
  props: {
    error: { type: Object, default: () => ({}) }
  }
}
</script>
