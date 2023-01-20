<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <v-card>
        <v-row class="pt-6" justify="center">
          <v-img max-width="30%" src="/images/login-logo.png" />
        </v-row>
        <v-card-text>
          <validation-provider v-slot="{ errors: validateError }" data-email tag="div" vid="email" :rules="rules.email">
            <z-text-field
              v-model="form.email"
              autofocus="autofocus"
              label="メールアドレス"
              :disabled="progress"
              :error-messages="validateError"
              :prepend-icon="$icons.email"
            />
          </validation-provider>
          <validation-provider
            v-slot="{ errors: validateError }"
            data-password
            tag="div"
            vid="password"
            :rules="rules.password"
          >
            <z-text-field
              ref="autofilledField"
              v-model="form.password"
              label="パスワード"
              :append-icon="showPassword ? $icons.visible : $icons.invisible"
              :class="{ [$style.autofilled]: autofilled }"
              :disabled="progress"
              :error-messages="validateError"
              :prepend-icon="$icons.password"
              :type="showPassword ? 'text' : 'password'"
              @click:append="toggleShowPassword"
              @focus="unwatchAutofill"
            />
          </validation-provider>
          <v-checkbox
            v-model="form.rememberMe"
            data-remember-me
            hide-details="hide-details"
            label="ログインしたままにする"
            :disabled="progress"
          />
          <z-error-container
            v-if="hasUnauthorizedError"
            class="mt-3 error--text v-messages"
            data-unauthorized-error
          >
            メールアドレス、またはパスワードが違います。
          </z-error-container>
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-btn block color="primary" depressed type="submit" :disabled="progress" :loading="progress">
            <span>ログイン</span>
          </v-btn>
        </v-card-actions>
        <v-card-actions class="pa-4">
          <v-btn block color="secondary" nuxt text to="/password-resets/new" :disabled="progress">
            <span>パスワードをお忘れですか？</span>
          </v-btn>
        </v-card-actions>
      </v-card>
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent, ref, watchEffect } from '@nuxtjs/composition-api'
import { useAutofillWorkaround } from '~/composables/use-autofill-workaround'
import { FormProps } from '~/composables/use-form-bindings'
import { SessionsApi } from '~/services/api/sessions-api'
import { observerRef, unref } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<SessionsApi.Form> & Readonly<{
  hasUnauthorizedError: boolean
}>

export default defineComponent<Props>({
  name: 'IndexPageForm',
  props: {
    errors: { type: Object, required: true },
    hasUnauthorizedError: { type: Boolean, required: true },
    progress: { type: Boolean, required: true }
  },
  setup (props, context) {
    const observer = observerRef()
    const form = ref({
      email: '',
      password: '',
      rememberMe: false
    })
    watchEffect(() => {
      observer.value?.setErrors(props.errors)
    })
    const submit = async () => {
      if (await observer.value?.validate()) {
        context.emit('submit', unref(form))
      }
    }
    const showPassword = ref(false)
    const toggleShowPassword = () => {
      showPassword.value = !showPassword.value
    }
    const rules = validationRules({
      email: { required },
      password: { required }
    })
    return {
      ...useAutofillWorkaround(),
      form,
      observer,
      rules,
      showPassword,
      submit,
      toggleShowPassword
    }
  }
})
</script>

<style lang="scss" module>
// 自動補完された入力欄のラベルの位置、大きさを調整する（入力欄にフォーカスした時の動きの再現）
.autofilled {
  :global {
    // ラベルの裏の枠線を見えなくする
    legend {
      width: 50.25px !important;
    }

    // ラベルの大きさを小さくし、入力欄の上部に移動する
    .v-label {
      color: rgba(0, 0, 0, 0.6);
      transform: translateY(-17px) scale(0.8);
    }
  }
}
</style>
