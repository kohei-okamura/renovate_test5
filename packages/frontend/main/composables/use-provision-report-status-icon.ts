/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<{
  status: DwsProvisionReportStatus | LtcsProvisionReportStatus
}>

export const resolveProvisionReportStatusIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case DwsProvisionReportStatus.notCreated:
    case LtcsProvisionReportStatus.notCreated:
      return $icons.statusNotAvailable
    case DwsProvisionReportStatus.inProgress:
    case LtcsProvisionReportStatus.inProgress:
      return $icons.statusChecking
    case DwsProvisionReportStatus.fixed:
    case LtcsProvisionReportStatus.fixed:
      return $icons.statusResolved
    default:
      return $icons.statusUnknown
  }
}

export const useProvisionReportStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveProvisionReportStatusIcon(unref(source)))
})
