<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page :breadcrumbs="breadcrumbs" :compact="tab !== 'billings'" :tabs="tabs">
    <z-tabs-items v-model="tab" class="transparent">
      <v-tab-item value="user">
        <z-data-card title="基本情報">
          <z-data-card-item label="状態" :icon="statusIcon" :value="resolveUserStatus(user.isEnabled)" />
          <z-data-card-item label="利用者名" :icon="$icons.user" :value="user.name.displayName" />
          <z-data-card-item label="利用者名：フリガナ" :value="user.name.phoneticDisplayName" />
          <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(user.sex)" />
          <z-data-card-item label="生年月日" :icon="$icons.birthday">
            <z-era-date :value="user.birthday" />
          </z-data-card-item>
          <z-data-card-item label="住所" :icon="$icons.addr">
            〒{{ user.addr.postcode }}<br>
            {{ resolvePrefecture(user.addr.prefecture) }}{{ user.addr.city }}{{ user.addr.street }}
            <template v-if="user.addr.apartment"><br>{{ user.addr.apartment }}</template>
          </z-data-card-item>
          <z-data-card-item label="電話番号" :icon="$icons.tel">
            <div v-for="(x, i) in user.contacts" :key="i" class="mb-1">
              {{ x.tel }}（{{ resolveContactRelationship(x.relationship) }}{{ x.name && '・' + x.name }}）
            </div>
          </z-data-card-item>
        </z-data-card>
        <z-billing-destination-card
          data-billing-destination-card
          :billing-destination="user.billingDestination"
        />
        <z-bank-account-card
          v-if="isAuthorized([permissions.viewUsersBankAccount])"
          data-bank-account-card
          v-bind="bankAccount"
        />
        <z-system-meta-card :id="user.id" :created-at="user.createdAt" :updated-at="user.updatedAt" />
      </v-tab-item>
      <v-tab-item value="dws">
        <z-dws-contracts-card
          v-if="isAuthorized([permissions.listDwsContracts])"
          data-dws-contracts-card
          :items="dwsContracts"
          :user="user"
        />
        <z-dws-certifications-card
          v-if="isAuthorized([permissions.listDwsCertifications])"
          data-dws-certifications-card
          :items="dwsCertifications"
          :user="user"
        />
        <z-dws-subsidies-card
          v-if="isAuthorized([permissions.listUserDwsSubsidies])"
          data-dws-subsidies-card
          :items="dwsSubsidies"
          :user="user"
        />
        <z-user-dws-calc-specs-card
          v-if="isAuthorized([permissions.listUserDwsCalcSpecs])"
          data-user-dws-calc-specs-card
          :items="dwsCalcSpecs"
          :user="user"
        />
        <z-dws-projects-card
          v-if="isAuthorized([permissions.listDwsProjects])"
          data-dws-projects-card
          :contracts="dwsContracts"
          :items="dwsProjects"
          :user="user"
        />
      </v-tab-item>
      <v-tab-item value="ltcs">
        <z-ltcs-contracts-card
          v-if="isAuthorized([permissions.listLtcsContracts])"
          data-ltcs-contracts-card
          :items="ltcsContracts"
          :user="user"
        />
        <z-ltcs-ins-cards-card
          v-if="isAuthorized([permissions.listLtcsInsCards])"
          data-ltcs-ins-cards-card
          :items="ltcsInsCards"
          :user="user"
        />
        <z-ltcs-subsidies-card
          v-if="isAuthorized([permissions.listUserLtcsSubsidies])"
          data-ltcs-subsidies-card
          :items="ltcsSubsidies"
          :user="user"
        />
        <z-user-ltcs-calc-specs-card
          v-if="isAuthorized([permissions.listUserLtcsCalcSpecs])"
          data-user-ltcs-calc-specs-card
          :items="ltcsCalcSpecs"
          :user="user"
        />
        <z-ltcs-projects-card
          v-if="isAuthorized([permissions.listLtcsProjects])"
          data-ltcs-projects-card
          :contracts="ltcsContracts"
          :items="ltcsProjects"
          :user="user"
        />
      </v-tab-item>
      <v-tab-item value="billings">
        <v-alert
          v-if="hasError"
          class="text-sm-body-2 mb-3"
          data-action-errors
          dense
          type="error"
        >
          <template v-for="x in errors">
            {{ x }}<br :key="x">
          </template>
        </v-alert>
        <z-user-billings-view
          :user-id="user.id"
          @click:download:invoice="downloadInvoices"
          @click:download:receipt="downloadReceipts"
          @click:download:notice="downloadNotices"
          @click:download:statement="downloadStatements"
        />
      </v-tab-item>
    </z-tabs-items>
    <template v-if="tab === 'user'">
      <z-fab-speed-dial
        v-if="isAuthorized([permissions.updateUsers, permissions.updateUsersBankAccount])"
        data-fab
        :icon="$icons.edit"
      >
        <z-fab-speed-dial-button
          v-if="isAuthorized([permissions.updateUsers])"
          nuxt
          :icon="$icons.editVariant"
          :to="`/users/${user.id}/edit`"
        >
          <span>基本情報を編集</span>
        </z-fab-speed-dial-button>
        <z-fab-speed-dial-button
          v-if="isAuthorized([permissions.updateUsersBankAccount])"
          nuxt
          :icon="$icons.bank"
          :to="`/users/${user.id}/bank-account/edit`"
        >
          <span>銀行口座情報を編集</span>
        </z-fab-speed-dial-button>
      </z-fab-speed-dial>
    </template>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { ContactRelationship, resolveContactRelationship } from '@zinger/enums/lib/contact-relationship'
import { Permission } from '@zinger/enums/lib/permission'
import { resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { resolveSex } from '@zinger/enums/lib/sex'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { userStateKey } from '~/composables/stores/use-user-store'
import { tabs } from '~/composables/tabs'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { useUserStatusIcon } from '~/composables/use-user-status-icon'
import { resolveUserStatus } from '~/composables/use-user-status-resolver'
import { NuxtContext } from '~/models/nuxt'
import { computedWith } from '~/support/reactive'

export default defineComponent({
  name: 'UsersViewPage',
  setup () {
    const { isAuthorized, permissions } = useAuth()
    const { $tabs } = usePlugins()
    const state = useInjected(userStateKey)
    const { contracts, user } = state
    const { tab } = $tabs
    const dwsContracts = computedWith(contracts, xs => {
      return xs?.filter(x => x.serviceSegment === ServiceSegment.disabilitiesWelfare) ?? []
    })
    const ltcsContracts = computedWith(contracts, xs => {
      return xs?.filter(x => x.serviceSegment === ServiceSegment.longTermCare) ?? []
    })
    const tabItems = tabs([
      { label: '利用者詳細', to: '#user' },
      {
        label: '障害者総合支援',
        permissions: [
          Permission.listDwsContracts,
          Permission.listDwsCertifications,
          Permission.listUserDwsSubsidies,
          Permission.listUserDwsCalcSpecs,
          Permission.listDwsProjects
        ],
        to: '#dws'
      },
      {
        label: '介護保険',
        permissions: [
          Permission.listLtcsContracts,
          Permission.listLtcsInsCards,
          Permission.listUserLtcsSubsidies,
          Permission.listUserLtcsCalcSpecs,
          Permission.listLtcsProjects
        ],
        to: '#ltcs'
      },
      {
        label: '利用者請求',
        permissions: [
          Permission.listUserBillings
        ],
        to: '#billings'
      }
    ])
    // 帳票ダウンロード
    const downloader = useUserBillingFileDownloader()
    const errors = computed(() => Object.values(downloader.errors.value).flat())
    const hasError = computed(() => Object.keys(errors.value).length >= 1)

    return {
      ...state,
      ...useBreadcrumbs('users.view', user),
      ...useUserStatusIcon(user),
      ContactRelationship,
      downloadInvoices: downloader.downloadInvoices,
      downloadNotices: downloader.downloadNotices,
      downloadReceipts: downloader.downloadReceipts,
      downloadStatements: downloader.downloadStatements,
      dwsContracts,
      errors,
      hasError,
      isAuthorized,
      ltcsContracts,
      permissions,
      resolveContactRelationship,
      resolvePrefecture,
      resolveSex,
      resolveUserStatus,
      tab,
      tabs: computed(() => tabItems.filter(tabItem => isAuthorized.value(tabItem.permissions)))
    }
  },
  fetch ({ redirect, route }: NuxtContext) {
    if (route.hash === '') {
      redirect(route.path + '#user')
    }
  },
  head: () => ({
    title: '利用者詳細'
  })
})
</script>
