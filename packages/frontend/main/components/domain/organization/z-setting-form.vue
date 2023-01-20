<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="事業者別設定">
        <z-form-card-item-set :icon="$icons.setting">
          <z-form-card-item
            v-slot="{ errors }"
            vid="bankingClientCode"
            :rules="rules.bankingClientCode"
            data-banking-client-code
          >
            <z-text-field
              v-model.trim="form.bankingClientCode"
              label="委託者番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { SettingApi } from '~/services/api/setting-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<SettingApi.Form> & Readonly<{
  buttonText: string
}>

export default defineComponent<Props>({
  name: 'ZSettingForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true }
  },
  setup (props, context) {
    const { form, observer, submit } = useFormBindings(props, context)
    const rules = validationRules({
      bankingClientCode: { required, digits: 10 }
    })
    return {
      form,
      observer,
      rules,
      submit
    }
  }
})
</script>
