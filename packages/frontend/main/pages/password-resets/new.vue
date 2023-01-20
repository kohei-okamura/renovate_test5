<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container fill-height>
    <v-row align-content="center" justify="center" no-gutters="no-gutters">
      <v-col cols="12" lg="4" md="5" sm="8" xl="3">
        <transition mode="out-in" name="slide">
          <v-form v-if="!done" data-form @submit.prevent="submit">
            <validation-observer ref="observer" tag="div">
              <v-card>
                <z-card-titlebar color="primary">パスワード再設定</z-card-titlebar>
                <v-card-text>
                  <p>登録されているメールアドレスにパスワード再設定用の URL を送信します。</p>
                  <validation-provider v-slot="{ errors }" data-email tag="div" vid="email" :rules="rules.email">
                    <z-text-field
                      v-model.trim="form.email"
                      autofocus="autofocus"
                      label="メールアドレス"
                      :disabled="progress"
                      :error-messages="errors"
                    />
                  </validation-provider>
                </v-card-text>
                <v-card-actions class="pa-4">
                  <v-btn block color="primary" text type="submit" :disabled="progress" :loading="progress">
                    <v-icon dark left>{{ $icons.send }}</v-icon>
                    <span>メールを送信</span>
                  </v-btn>
                </v-card-actions>
              </v-card>
            </validation-observer>
          </v-form>
          <v-card v-else>
            <z-card-titlebar color="primary">パスワード再設定</z-card-titlebar>
            <v-card-text>
              <p>指定されたメールアドレスにメールを送信しました。</p>
              <p>メールに記載された URL へアクセスし、パスワードを再設定してください。</p>
            </v-card-text>
            <v-card-actions class="pa-4">
              <v-btn block color="primary" nuxt text to="/">ログイン画面へ</v-btn>
            </v-card-actions>
          </v-card>
        </transition>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { observerRef } from '~/support/reactive'
import { email, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

export default defineComponent({
  name: 'PasswordResetsNewPage',
  layout: 'login',
  setup () {
    const { $api } = usePlugins()
    const observer = observerRef()
    const { errors, progress, withAxios } = useAxios()
    const data = reactive({
      done: false,
      form: {
        email: ''
      }
    })
    watch(errors, xs => observer.value?.setErrors(xs))
    return {
      ...toRefs(data),
      observer,
      progress,
      rules: validationRules({
        email: { required, email }
      }),
      submit: async () => {
        if (await observer.value?.validate()) {
          await withAxios(async () => {
            await $api.passwordResets.create({ form: data.form })
            data.done = true
          })
        }
      }
    }
  },
  head: () => ({
    title: 'パスワード再設定'
  })
})
</script>
