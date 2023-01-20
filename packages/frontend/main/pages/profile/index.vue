<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-alert v-if="noBankAccount" data-no-bank-account-alert type="warning">
      <span>給与振込口座が登録されていません。</span>
      <span><nuxt-link to="/profile/bank-account/edit">こちらから登録してください</nuxt-link>。</span>
    </v-alert>
    <z-staff-card hide-roles :offices="offices" v-bind="staff" />
    <z-bank-account-card v-if="hasBankAccount" title="給与振込口座" v-bind="bankAccount" />
    <z-system-meta-card :id="staff.id" :created-at="staff.createdAt" :updated-at="staff.updatedAt" />
    <template v-if="isAuthorized([permissions.updateUsers])">
      <z-fab v-if="hasBankAccount" bottom data-fab fixed nuxt right to="/profile/edit" :icon="$icons.edit" />
      <z-fab-speed-dial v-else data-fab-speed-dial :icon="$icons.edit">
        <z-fab-speed-dial-button nuxt to="/profile/edit" :icon="$icons.edit">
          登録情報を編集
        </z-fab-speed-dial-button>
        <z-fab-speed-dial-button
          v-if="isAuthorized([permissions.updateUsersBankAccount])"
          data-fab-edit-bank-account
          nuxt
          to="/profile/bank-account/edit"
          :icon="$icons.bank"
        >
          給与振込口座を登録
        </z-fab-speed-dial-button>
      </z-fab-speed-dial>
    </template>
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
  name: 'SettingsProfileViewPage',
  middleware: [auth(Permission.viewStaffs)],
  setup () {
    const { bankAccount, offices, staff } = useInjected(staffStateKey)
    const noBankAccount = computed(() => isEmpty(bankAccount.value?.bankAccountNumber))
    const hasBankAccount = computed(() => !noBankAccount.value)
    const { isAuthorized, permissions } = useAuth()
    return {
      ...useBreadcrumbs('settings.profile.index'),
      bankAccount,
      hasBankAccount,
      noBankAccount,
      isAuthorized,
      permissions,
      offices,
      staff
    }
  },
  head: () => ({
    title: 'スタッフ登録情報'
  })
})
</script>
