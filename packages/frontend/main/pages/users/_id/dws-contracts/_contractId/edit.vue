<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-contract-form
      button-text="保存"
      :contract-status="contract.status"
      :errors="errors"
      :permission="permission"
      :progress="progress"
      :service-segment="segment"
      :user="user"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { ContractStatus, resolveContractStatus } from '@zinger/enums/lib/contract-status'
import { parseEnum } from '@zinger/enums/lib/enum'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { pick } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsContractStateKey, dwsContractStoreKey } from '~/composables/stores/use-dws-contract-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { Contract } from '~/models/contract'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'

type Form = Partial<DwsContractsApi.UpdateForm>

export default defineComponent({
  name: 'DwsContractsEditPage',
  middleware: [auth(Permission.updateDwsContracts)],
  setup: () => {
    const { $route, $router } = usePlugins()
    const { contract } = useInjected(dwsContractStateKey)
    const dwsContractStore = useInjected(dwsContractStoreKey)
    const { user } = useInjected(userStateKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const status = parseEnum(+$route.query.status, ContractStatus)
    if (status !== ContractStatus.formal && status !== ContractStatus.terminated) {
      $router.replace(`../new?segment=${contract.value!.serviceSegment}`)
    }
    const createFormValue = (x: Contract, status: ContractStatus): Form => ({
      ...pick(x, ['officeId', 'contractedOn', 'terminatedOn', 'dwsPeriods', 'note']),
      status
    })
    const resolvedStatus = computed(() => resolveContractStatus(contract.value?.status))
    const submit = (form: Form) => {
      const id = contract.value!.id
      const userId = user.value!.id
      return updateUserDependant({
        dependant: '契約情報',
        userId,
        callback: () => dwsContractStore.update({ form, id, userId }),
        hash: 'dws'
      })
    }
    return {
      ...useBreadcrumbs('users.dwsContracts.edit', user, contract),
      contract,
      errors,
      permission: Permission.updateDwsContracts,
      progress,
      resolvedStatus,
      segment: ServiceSegment.disabilitiesWelfare,
      user,
      value: createFormValue(contract.value!, status),
      submit
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス契約を編集'
  })
})
</script>
