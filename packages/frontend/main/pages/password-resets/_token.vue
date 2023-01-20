<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container fill-height>
    <v-row align-content="center" justify="center" no-gutters="no-gutters">
      <v-col cols="12" lg="4" md="5" sm="8" xl="3">
        <transition mode="out-in" name="slide">
          <div v-if="committed" key="committed">
            <v-card>
              <z-card-titlebar color="primary">パスワード再設定</z-card-titlebar>
              <v-card-text>
                <p>パスワードを設定しました。</p>
                <p>ログイン画面へ移動し、新しいパスワードでログインしてください。</p>
              </v-card-text>
              <v-card-actions class="pa-4">
                <v-btn block color="primary" nuxt text to="/">ログイン画面へ</v-btn>
              </v-card-actions>
            </v-card>
          </div>
          <div v-else-if="forbidden" key="forbidden">
            <v-card>
              <z-card-titlebar color="primary">URL の有効期限が切れています</z-card-titlebar>
              <v-card-text>お手数ですが、もう一度最初からお手続きください。</v-card-text>
              <v-card-actions>
                <v-btn block color="primary" nuxt text to="/password-resets/new">パスワード再設定</v-btn>
              </v-card-actions>
            </v-card>
          </div>
          <v-form v-else-if="verified" key="verified" data-form @submit.prevent="submit">
            <validation-observer ref="observer" tag="div">
              <v-card>
                <z-card-titlebar color="primary">パスワード再設定</z-card-titlebar>
                <v-card-text>
                  <p>新しいパスワードを設定してください。</p>
                  <validation-provider
                    v-slot="{ errors }"
                    data-password
                    tag="div"
                    vid="password"
                    :rules="rules.password"
                  >
                    <z-text-field
                      v-model.trim="form.password"
                      autofocus="autofocus"
                      label="パスワード"
                      :append-icon="passwordVisibility ? $icons.visible : $icons.invisible"
                      :disabled="progress"
                      :error-messages="errors"
                      :type="passwordVisibility ? 'text' : 'password'"
                      @click:append="togglePasswordVisibility"
                    />
                  </validation-provider>
                </v-card-text>
                <v-card-actions class="pa-4">
                  <v-btn block color="primary" text type="submit" :disabled="progress" :loading="progress">
                    送信
                  </v-btn>
                </v-card-actions>
              </v-card>
            </validation-observer>
          </v-form>
        </transition>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { HttpStatusCode } from '~/models/http-status-code'
import { NuxtContext } from '~/models/nuxt'
import { isAxiosError } from '~/support'
import { observerRef } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

export default defineComponent({
  name: 'PasswordResetsCommitPage',
  layout: 'login',
  validate: ({ params }: NuxtContext) => /^[a-zA-Z0-9]{60}$/.test(params.token),
  setup () {
    const { $api, $route } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const observer = observerRef()
    const data = reactive({
      committed: false,
      forbidden: false,
      form: {
        password: ''
      },
      passwordVisibility: false,
      verified: false
    })

    const token = $route.params.token
    $api.passwordResets.verify({ token })
      .then(() => {
        data.verified = true
      })
      .catch(reason => {
        data.forbidden = isAxiosError(reason) && reason.response?.status === HttpStatusCode.Forbidden
      })

    const submit = async () => {
      if (await observer.value?.validate()) {
        await withAxios(async () => {
          await $api.passwordResets.commit({ form: data.form, token })
          data.committed = true
        })
      }
    }
    watch(errors, xs => observer.value?.setErrors(xs))

    const togglePasswordVisibility = () => {
      data.passwordVisibility = !data.passwordVisibility
    }

    const rules = validationRules({
      password: { required, min: process.env.passwordMinLength }
    })

    return {
      ...toRefs(data),
      observer,
      progress,
      rules,
      submit,
      togglePasswordVisibility
    }
  },
  head: () => ({
    title: 'パスワード再設定'
  })
})
</script>
