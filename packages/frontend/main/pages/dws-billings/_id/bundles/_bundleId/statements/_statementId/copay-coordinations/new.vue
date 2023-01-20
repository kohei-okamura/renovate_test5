<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-billing-copay-coordination-form
      button-text="登録"
      :errors="errors"
      :progress="progress"
      :bundle="bundle"
      :office="office"
      :statement="statement"
      :status="status"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { assert } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { dwsBillingStatementStateKey } from '~/composables/stores/use-dws-billing-statement-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'

type Form = Partial<DwsBillingCopayCoordinationsApi.Form>

export default defineComponent({
  name: 'DwsBillingCopayCoordinationsNewPage',
  middleware: [auth(Permission.createBillings)],
  validate: ({ params, query }: NuxtContext) => {
    return !isNaN(+params.id) && !isNaN(+params.bundleId) && !isNaN(+query.userId)
  },
  setup () {
    const { $api, $form, $route, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const billingStore = useInjected(dwsBillingStoreKey)
    const statementStore = useInjected(dwsBillingStatementStateKey)

    const billing = billingStore.state.billing.value
    const bundle = (billingStore.state.bundles.value ?? []).find(({ id }) => id === +$route.params.bundleId)
    const statement = statementStore.statement.value

    assert(typeof billing !== 'undefined', 'DwsBilling does not loaded')
    assert(typeof bundle !== 'undefined', 'DwsBillingBundle does not loaded')
    assert(typeof statement !== 'undefined', 'DwsBillingStatement does not loaded')

    $form.preventUnexpectedUnload()
    const createCopayCoordination = (form: Form) => withAxios(
      () => $form.submit(async () => {
        const params = {
          billingId: billing.id,
          bundleId: bundle.id,
          form
        }
        const { copayCoordination } = await $api.dwsBillingCopayCoordinations.create(params)
        await billingStore.get({ id: billing.id })
        await catchErrorStack(() => (
          $router.replace(`/dws-billings/${billing.id}/bundles/${bundle.id}/statements/${statement.id}/copay-coordinations/${copayCoordination.id}`)
        ))
        $snackbar.success('利用者負担上限額管理結果票を作成しました。')
      }),
      () => $snackbar.error('利用者負担上限額管理結果票の作成に失敗しました。')
    )
    return {
      ...useBreadcrumbs('dwsBillings.copayCoordination.new', { billingId: billing.id }),
      bundle,
      errors,
      office: billing.office,
      progress,
      submit: createCopayCoordination,
      status: DwsBillingStatus.checking,
      statement,
      value: {}
    }
  },
  head: () => ({
    title: '障害福祉サービス請求 利用者負担上限額管理結果票を作成'
  })
})
</script>
