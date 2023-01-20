/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'

type UnrefState = {
  errors?: string[]
}

const createBillingBulkUpdateState = (): UnrefState => ({
  errors: undefined
})

export function useBillingBulkUpdateStore () {
  const state = reactive(createBillingBulkUpdateState())
  const actions = {
    updateErrors (errors: UnrefState['errors']) {
      assign(state, { errors })
    },
    resetState () {
      assign(state, createBillingBulkUpdateState())
    }
  }
  return createStore({ state, actions })
}

export type BillingBulkUpdateStore = ReturnType<typeof useBillingBulkUpdateStore>

export type BillingBulkUpdateState = BillingBulkUpdateStore['state']

export const billingBulkUpdateStoreKey: InjectionKey<BillingBulkUpdateStore> = Symbol('billingBulkUpdateStore')

export const billingBulkUpdateStateKey: InjectionKey<BillingBulkUpdateState> = Symbol('billingBulkUpdateState')
