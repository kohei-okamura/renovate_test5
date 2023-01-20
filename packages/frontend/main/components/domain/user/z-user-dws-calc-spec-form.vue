<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-z-user-dws-calc-spec-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="利用者別算定情報">
        <z-form-card-item-set :icon="$icons.dateStart">
          <z-form-card-item v-slot="{ errors }" data-effectivated-on vid="effectivatedOn" :rules="rules.effectivatedOn">
            <z-date-field v-model="form.effectivatedOn" label="適用日 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item
            v-slot="{ errors }"
            data-location-addition
            :rules="rules.locationAddition"
          >
            <z-select
              v-model="form.locationAddition"
              label="地域加算 *"
              :error-messages="errors"
              :items="locationAdditions"
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
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { Permission } from '@zinger/enums/lib/permission'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { User } from '~/models/user'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DeepPartial<UserDwsCalcSpecsApi.Form>> & Readonly<{
  buttonText: string
  permission: Permission
  user: User
}>

export default defineComponent<Props>({
  name: 'ZUserDwsCalcSpecForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup (props: Props, context) {
    const rules = validationRules({
      effectivatedOn: { required },
      locationAddition: { required }
    })
    return {
      ...useFormBindings(props, context, {
        init: form => ({
          effectivatedOn: form.effectivatedOn,
          locationAddition: form.locationAddition
        })
      }),
      locationAdditions: enumerableOptions(DwsUserLocationAddition),
      rules
    }
  }
})
</script>
