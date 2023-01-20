/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { colors } from '~/colors'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeStoreKey } from '~/composables/stores/use-office-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'

type HomeVisitLongTermCareCalcSpecDependantParams = {
  editOrRegistration: string
  officeId: OfficeId
  callback: () => Promise<HomeVisitLongTermCareCalcSpecsApi.UpdateResponse>
}

const params: ConfirmDialogParams = {
  color: colors.primary,
  message: '適用期間中に予実が見つかりました。\n新しい加算情報を反映する必要があります。\n\n予実の一覧に遷移しますか？',
  positive: '遷移'
}

export const useHomeVisitLongTermCareCalcSpecDependant = () => {
  const { $alert, $confirm, $router, $form, $snackbar } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const officeStore = useInjected(officeStoreKey)
  $form.preventUnexpectedUnload()

  const createHomeVisitLongTermCareCalcSpecDependant = (
    { editOrRegistration, officeId, callback }: HomeVisitLongTermCareCalcSpecDependantParams
  ): Promise<void> => {
    return withAxios(
      () => $form.submit(async () => {
        const { provisionReportCount } = await callback()
        $snackbar.success(`算定情報（介保・訪問介護）を${editOrRegistration}しました。`)
        if (provisionReportCount !== 0 && await $confirm.show(params)) {
          await catchErrorStack(() => $router.replace(`/ltcs-provision-reports?page=1&officeId=${officeId}`))
        } else {
          await officeStore.get({ id: officeId })
          await catchErrorStack(() => $router.replace(`/offices/${officeId}#calc-specs`))
        }
      }),
      error => $alert.error(`算定情報（介保・訪問介護）の${editOrRegistration}に失敗しました。`, error.stack)
    )
  }

  return {
    createHomeVisitLongTermCareCalcSpecDependant,
    errors,
    progress
  }
}
