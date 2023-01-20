/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { Contract } from '~/models/contract'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<Contract, 'status'>> | ContractStatus

function isStatus (x: Source | undefined): x is ContractStatus {
  return typeof x === 'number'
}

const resolveIcon = (source: Source | undefined) => {
  switch (isStatus(source) ? source : source?.status) {
    case ContractStatus.provisional:
      return $icons.statusProgress
    case ContractStatus.formal:
      return $icons.statusResolved
    case ContractStatus.terminated:
      return $icons.statusRejected
    case ContractStatus.disabled:
      return $icons.statusDisabled
    default:
      return $icons.statusUnknown
  }
}

export const useContractStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
