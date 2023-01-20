<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs" :title="staff.name.displayName">
    <z-staff-card :offices="offices" :roles="roles" v-bind="staff" />
    <z-bank-account-card v-bind="bankAccount" />
    <z-system-meta-card :id="staff.id" :created-at="staff.createdAt" :updated-at="staff.updatedAt" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateStaffs])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button nuxt :icon="$icons.edit" :to="`/staffs/${staff.id}/edit`">
        基本情報を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button nuxt :icon="$icons.bank" :to="`/staffs/${staff.id}/bank-account/edit`">
        銀行口座情報を編集
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { staffStateKey } from '~/composables/stores/use-staff-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'StaffsViewPage',
  middleware: [auth(Permission.viewStaffs)],
  setup () {
    const { bankAccount, offices, roles, staff } = useInjected(staffStateKey)
    const noBankAccount = computed(() => isEmpty(bankAccount.value?.bankAccountNumber))
    const hasBankAccount = computed(() => !noBankAccount.value)
    return {
      ...useAuth(),
      ...useBreadcrumbs('staffs.view', staff),
      bankAccount,
      hasBankAccount,
      noBankAccount,
      offices,
      roles,
      staff
    }
  },
  head: () => ({
    title: 'スタッフ詳細'
  })
})
</script>
