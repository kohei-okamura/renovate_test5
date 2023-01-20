<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="契約情報">
      <z-data-card-item label="事業所" :icon="$icons.office" :value="resolveOfficeAbbr(contract.officeId)" />
      <z-data-card-item
        label="事業領域"
        :icon="$icons.category"
        :value="resolveServiceSegment(contract.serviceSegment)"
      />
      <z-data-card-item label="契約状態" :icon="statusIcon" :value="resolvedStatus" />
      <z-data-card-item label="契約日" :icon="$icons.dateStart">
        <z-era-date :value="contract.contractedOn" />
      </z-data-card-item>
      <z-data-card-item label="解約日" :icon="$icons.dateEnd">
        <z-era-date :value="contract.terminatedOn" />
      </z-data-card-item>
      <z-data-card-item label="備考" :icon="$icons.text" :value="contract.note" />
    </z-data-card>
    <z-data-card v-for="x in divisions" :key="x.code" :title="x.title">
      <z-data-card-item label="初回サービス提供日" :icon="$icons.dateStart">
        <z-era-date :value="contract.dwsPeriods[x.code].start" />
      </z-data-card-item>
      <z-data-card-item label="最終サービス提供日" :icon="$icons.dateEnd">
        <z-era-date :value="contract.dwsPeriods[x.code].end" />
      </z-data-card-item>
    </z-data-card>
    <z-system-meta-card :id="contract.id" :created-at="contract.createdAt" :updated-at="contract.updatedAt" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateDwsContracts]) && !isDisabled"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        v-if="!isTerminated"
        data-fab-formal
        nuxt
        :icon="$icons.editContract"
        :to="urls.formal"
      >
        {{ isFormal ? '契約を編集' : '本契約へ' }}
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="!isProvisional"
        data-fab-terminate
        nuxt
        :icon="$icons.terminateContract"
        :to="urls.terminated"
      >
        {{ isTerminated ? '契約を編集' : '契約終了へ' }}
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        data-fab-disable
        :icon="$icons.disableContract"
        @click="disableContract"
      >
        契約を無効化
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { ContractStatus, resolveContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveServiceSegment } from '@zinger/enums/lib/service-segment'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsContractStateKey } from '~/composables/stores/use-dws-contract-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useContractStatusIcon } from '~/composables/use-contract-status-icon'
import { useDisableContractFunction } from '~/composables/use-disable-contract-function'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'
import { computedWith } from '~/support/reactive'

export default defineComponent({
  name: 'DwsContractsViewPage',
  middleware: [auth(Permission.viewDwsContracts)],
  setup () {
    const { contract } = useInjected(dwsContractStateKey)
    const { user } = useInjected(userStateKey)

    const resolvedStatus = computed(() => contract.value ? resolveContractStatus(contract.value.status) : '-')

    const divisions = DwsServiceDivisionCode.values.map(code => ({
      code,
      title: DwsServiceDivisionCode.resolve(code)
    }))

    const urls = computed(() => {
      const userId = user.value?.id
      const contractId = contract.value?.id
      return {
        formal: `/users/${userId}/dws-contracts/${contractId}/edit?status=${ContractStatus.formal}`,
        terminated: `/users/${userId}/dws-contracts/${contractId}/edit?status=${ContractStatus.terminated}`
      }
    })

    const { disableContractFunction } = useDisableContractFunction()
    const disableContract = disableContractFunction({ userId: user.value!.id, contract, type: 'dws' })

    return {
      ...useAuth(),
      ...useBreadcrumbs('users.dwsContracts.view', user),
      ...useContractStatusIcon(contract),
      ...useOffices({ permission: Permission.viewDwsContracts }),
      contract,
      disableContract,
      divisions,
      isDisabled: computedWith(contract, x => x?.status === ContractStatus.disabled),
      isFormal: computedWith(contract, x => x?.status === ContractStatus.formal),
      isProvisional: computedWith(contract, x => x?.status === ContractStatus.provisional),
      isTerminated: computedWith(contract, x => x?.status === ContractStatus.terminated),
      resolveServiceSegment,
      resolvedStatus,
      urls,
      user
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス契約詳細'
  })
})
</script>
