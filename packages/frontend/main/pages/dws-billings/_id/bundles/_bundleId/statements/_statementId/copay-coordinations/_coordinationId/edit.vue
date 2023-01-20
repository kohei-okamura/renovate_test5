<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-billing-copay-coordination-form
      button-text="保存"
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
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import {
  dwsBillingCopayCoordinationStateKey,
  dwsBillingCopayCoordinationStoreKey
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { dwsBillingStatementStateKey } from '~/composables/stores/use-dws-billing-statement-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'

type Form = Partial<DwsBillingsApi.CreateForm>

export default defineComponent({
  name: 'DwsBillingCopayCoordinationEditPage',
  middleware: [auth(Permission.updateBillings)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    /*
     * _coordinationId.vue で取得しているので、この時点で dwsBillingCopayCoordinationState から情報が取れることは保証されているため、
     * billing, bundle, copayCoordination は value! で使用している
     * また、不変の項目（e.g. id）や 将来的にもこの画面で更新されることがなさそうな項目（e.g. office, user）は computed にしていない
     */
    const copayCoordinationStore = useInjected(dwsBillingCopayCoordinationStoreKey)
    const { billing, bundle, copayCoordination } = useInjected(dwsBillingCopayCoordinationStateKey)
    const { statement } = useInjected(dwsBillingStatementStateKey)
    const { breadcrumbs } = useBreadcrumbs(
      'dwsBillings.copayCoordination.edit',
      {
        billingId: billing.value!.id,
        bundleId: bundle.value!.id,
        id: copayCoordination.value!.id,
        name: copayCoordination.value!.user.name.displayName,
        statementId: statement.value!.id
      }
    )
    $form.preventUnexpectedUnload()
    return {
      breadcrumbs,
      bundle,
      errors,
      office: billing.value!.office,
      progress,
      statement,
      status: copayCoordination.value!.status,
      value: computed(() => ({
        items: copayCoordination.value!.items.map(x => ({ officeId: x.office.officeId, subtotal: x.subtotal })),
        exchangeAim: copayCoordination.value!.exchangeAim,
        result: copayCoordination.value!.result
      })),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const billingId = billing.value!.id
          const bundleId = bundle.value!.id
          const id = copayCoordination.value!.id
          const statementId = statement.value!.id
          const params = {
            billingId,
            bundleId,
            form,
            id
          }
          await copayCoordinationStore.update(params)
          await catchErrorStack(() => (
            $router.replace(`/dws-billings/${billingId}/bundles/${bundleId}/statements/${statementId}/copay-coordinations/${id}`)
          ))
          $snackbar.success('利用者負担上限額管理結果票を編集しました。')
        }),
        () => $alert.error('利用者負担上限額管理結果票の編集に失敗しました。')
      )
    }
  },
  head: () => ({
    title: '障害福祉サービス請求 利用者負担上限額管理結果票を編集'
  })
})
</script>
