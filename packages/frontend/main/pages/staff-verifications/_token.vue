<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container fill-height>
    <v-row align-content="center" justify="center">
      <v-col cols="12" lg="4" md="5" sm="8" xl="3">
        <v-card>
          <template v-if="ok">
            <z-card-titlebar color="primary">メールアドレス確認</z-card-titlebar>
            <v-card-text>メールアドレスの確認が完了しました。</v-card-text>
            <v-card-actions>
              <v-btn block color="primary" nuxt text to="/">ログイン画面へ</v-btn>
            </v-card-actions>
          </template>
          <template v-else-if="forbidden">
            <z-card-titlebar color="primary">URL の有効期限が切れています</z-card-titlebar>
            <v-card-text>お手数ですが、もう一度最初からご登録ください。</v-card-text>
            <v-card-actions>
              <v-btn block color="primary" nuxt text to="/staffs/new">新規登録</v-btn>
            </v-card-actions>
          </template>
          <template v-else>
            <z-card-titlebar color="primary">エラーが発生しました</z-card-titlebar>
            <v-card-text>
              <p>しばらく経ってからもう一度お試しください。</p>
              <p>この画面が繰り返し表示される場合は、お手数ですがシステム管理者までお問い合わせください。</p>
            </v-card-text>
          </template>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'
import { HttpStatusCode } from '~/models/http-status-code'
import { NuxtContext } from '~/models/nuxt'
import { isAxiosError } from '~/support'

export default defineComponent({
  name: 'StaffVerificationPage',
  layout: 'login',
  validate ({ params }: NuxtContext) {
    return /^[a-zA-Z0-9]{60}$/.test(params.token)
  },
  setup () {
    const { $api, $route } = usePlugins()
    const forbidden = ref(false)
    const ok = ref(false)
    $api.staffs.verify({ token: $route.params.token })
      .then(() => {
        ok.value = true
      })
      .catch(reason => {
        forbidden.value = isAxiosError(reason) && reason.response?.status === HttpStatusCode.Forbidden
      })
    return {
      forbidden,
      ok
    }
  },
  head: () => ({
    title: 'スタッフ登録'
  })
})
</script>
