<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-data-card data-data-card title="基本情報">
      <z-data-card-item
        label="事業所名"
        :icon="$icons.office"
        :value="resolveOfficeAbbr(ownExpenseProgram.officeId, 'すべての事業所')"
      />
      <z-data-card-item label="自費サービス名" :icon="$icons.ownExpenseProgram" :value="ownExpenseProgram.name" />
      <z-data-card-item label="単位時間数" :icon="$icons.timeAmount">
        {{ numeral(ownExpenseProgram.durationMinutes) }}分
      </z-data-card-item>
      <z-data-card-item
        label="課税区分"
        :icon="$icons.category"
        :value="resolveTaxType(ownExpenseProgram.fee.taxType)"
      />
      <z-data-card-item v-if="hasTaxExempted" label="費用" :icon="$icons.yen">
        {{ numeral(ownExpenseProgram.fee.taxExcluded) }}円
      </z-data-card-item>
      <template v-else>
        <z-data-card-item label="費用（税抜）" :icon="$icons.yen">
          {{ numeral(ownExpenseProgram.fee.taxExcluded) }}円
        </z-data-card-item>
        <z-data-card-item label="費用（税込）">
          {{ numeral(ownExpenseProgram.fee.taxIncluded) }}円
        </z-data-card-item>
        <z-data-card-item label="税率区分" :value="resolveTaxCategory(ownExpenseProgram.fee.taxCategory)" />
      </template>
      <z-data-card-item label="備考" :icon="$icons.text" :value="ownExpenseProgram.note" />
    </z-data-card>
    <z-system-meta-card
      :id="ownExpenseProgram.id"
      :created-at="ownExpenseProgram.createdAt"
      :updated-at="ownExpenseProgram.updatedAt"
    />
    <z-fab
      v-if="isAuthorized([permissions.updateOwnExpensePrograms])"
      bottom
      data-fab
      fixed
      nuxt
      right
      :icon="$icons.edit"
      :to="`/own-expense-programs/${ownExpenseProgram.id}/edit`"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveTaxCategory } from '@zinger/enums/lib/tax-category'
import { resolveTaxType, TaxType } from '@zinger/enums/lib/tax-type'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import { ownExpenseProgramStateKey } from '~/composables/stores/use-own-expense-program-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'OwnExpenseProgramsViewPage',
  middleware: [auth(Permission.viewOwnExpensePrograms)],
  setup () {
    const { ownExpenseProgram } = useInjected(ownExpenseProgramStateKey)
    const hasTaxExempted = computed(() => ownExpenseProgram.value?.fee.taxType === TaxType.taxExempted)
    return {
      ...useAuth(),
      ...useBreadcrumbs('ownExpensePrograms.view'),
      ...useOffices({ permission: Permission.viewOwnExpensePrograms }),
      hasTaxExempted,
      numeral,
      ownExpenseProgram,
      resolveTaxCategory,
      resolveTaxType
    }
  },
  head: () => ({
    title: '自費サービス詳細'
  })
})
</script>
