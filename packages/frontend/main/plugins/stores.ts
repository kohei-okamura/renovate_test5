/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin, InjectionKey, onGlobalSetup, provide } from '@nuxtjs/composition-api'
import {
  attendancesStateKey,
  attendancesStoreKey,
  useAttendancesStore
} from '~/composables/stores/use-attendances-store'
import {
  billingBulkUpdateStateKey,
  billingBulkUpdateStoreKey,
  useBillingBulkUpdateStore
} from '~/composables/stores/use-billing-bulk-update-store'
import {
  dwsBillingsStateKey,
  dwsBillingsStoreKey,
  useDwsBillingsStore
} from '~/composables/stores/use-dws-billings-store'
import {
  dwsProvisionReportsIndexStateKey,
  dwsProvisionReportsIndexStoreKey,
  useDwsProvisionReportsStore
} from '~/composables/stores/use-dws-provision-reports-store'
import {
  ltcsBillingsStateKey,
  ltcsBillingsStoreKey,
  useLtcsBillingsStore
} from '~/composables/stores/use-ltcs-billings-store'
import {
  ltcsProvisionReportsIndexStateKey,
  ltcsProvisionReportsIndexStoreKey,
  useLtcsProvisionReportsStore
} from '~/composables/stores/use-ltcs-provision-reports-store'
import {
  notificationStateKey,
  notificationStoreKey,
  useNotificationStore
} from '~/composables/stores/use-notification-store'
import { officesStateKey, officesStoreKey, useOfficesStore } from '~/composables/stores/use-offices-store'
import {
  ownExpenseProgramsStateKey,
  ownExpenseProgramsStoreKey,
  useOwnExpenseProgramsStore
} from '~/composables/stores/use-own-expense-programs-store'
import { rolesStateKey, rolesStoreKey, useRolesStore } from '~/composables/stores/use-roles-store'
import { sessionStateKey, sessionStoreKey, useSessionStore } from '~/composables/stores/use-session-store'
import {
  shiftImportsStateKey,
  shiftImportsStoreKey,
  useShiftImportsStore
} from '~/composables/stores/use-shift-imports-store'
import { shiftsStateKey, shiftsStoreKey, useShiftsStore } from '~/composables/stores/use-shifts-store'
import { staffsStateKey, staffsStoreKey, useStaffsStore } from '~/composables/stores/use-staffs-store'
import {
  userBillingsStateKey,
  userBillingsStoreKey,
  useUserBillingsStore
} from '~/composables/stores/use-user-billings-store'
import { usersStateKey, usersStoreKey, useUsersStore } from '~/composables/stores/use-users-store'
import { StateProvider } from '~/composables/stores/utils'

type ProvideStore = <T extends Record<string, unknown>, U extends StateProvider<T>> (
  store: U,
  storeKey: InjectionKey<U>,
  stateKey: InjectionKey<T>
) => void

const provideStore: ProvideStore = (store, storeKey, stateKey) => {
  provide(storeKey, store)
  provide(stateKey, store.state)
}

export default defineNuxtPlugin(() => onGlobalSetup(() => {
  provideStore(useUserBillingsStore(), userBillingsStoreKey, userBillingsStateKey)
  provideStore(useLtcsBillingsStore(), ltcsBillingsStoreKey, ltcsBillingsStateKey)
  provideStore(useDwsBillingsStore(), dwsBillingsStoreKey, dwsBillingsStateKey)
  provideStore(useAttendancesStore(), attendancesStoreKey, attendancesStateKey)
  provideStore(useShiftsStore(), shiftsStoreKey, shiftsStateKey)
  provideStore(useOwnExpenseProgramsStore(), ownExpenseProgramsStoreKey, ownExpenseProgramsStateKey)
  provideStore(useOfficesStore(), officesStoreKey, officesStateKey)
  provideStore(useStaffsStore(), staffsStoreKey, staffsStateKey)
  provideStore(useUsersStore(), usersStoreKey, usersStateKey)
  provideStore(useDwsProvisionReportsStore(), dwsProvisionReportsIndexStoreKey, dwsProvisionReportsIndexStateKey)
  provideStore(useLtcsProvisionReportsStore(), ltcsProvisionReportsIndexStoreKey, ltcsProvisionReportsIndexStateKey)
  provideStore(useNotificationStore(), notificationStoreKey, notificationStateKey)
  provideStore(useRolesStore(), rolesStoreKey, rolesStateKey)
  provideStore(useSessionStore(), sessionStoreKey, sessionStateKey)
  provideStore(useShiftImportsStore(), shiftImportsStoreKey, shiftImportsStateKey)
  provideStore(useBillingBulkUpdateStore(), billingBulkUpdateStoreKey, billingBulkUpdateStateKey)
}))
