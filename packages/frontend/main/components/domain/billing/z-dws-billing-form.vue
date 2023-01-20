<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form class="z-dws-billing-form" data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-office vid="officeId" :rules="rules.officeId">
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              clearable
              label="事業所 *"
              :disabled="progress"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.month">
          <z-form-card-item v-slot="{ errors }" vid="transactedIn" :rules="rules.transactedIn" data-transactedIn>
            <z-date-field
              v-model="form.transactedIn"
              label="処理対象年月 *"
              type="month"
              :disabled="progress"
              :error-messages="errors"
              :max="$datetime.now.toISODate()"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button text="作成開始" :disabled="progress" :icon="$icons.save" :loading="progress" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DwsBillingsApi.CreateForm>

export default defineComponent<Props>({
  name: 'ZDwsBillingForm',
  props: getFormPropsOptions(),
  setup (props, context) {
    const { $datetime } = usePlugins()
    return {
      ...useOffices({ permission: Permission.createBillings, internal: true }),
      ...useFormBindings(props, context, {
        init: form => ({
          officeId: form.officeId,
          transactedIn: form.transactedIn ?? $datetime.now.toFormat(ISO_MONTH_FORMAT),
          providedIn: form.providedIn ?? $datetime.now.toFormat(ISO_MONTH_FORMAT)
        })
      }),
      rules: validationRules({
        officeId: { required },
        transactedIn: { required }
      })
    }
  }
})
</script>
