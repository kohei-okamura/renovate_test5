<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card :title="title">
    <z-data-card-item label="銀行名（銀行コード）" :icon="$icons.bank">
      {{ bankName }}（{{ bankCode }}）
    </z-data-card-item>
    <z-data-card-item label="支店名（支店コード）">
      {{ bankBranchName }}（{{ bankBranchCode }}）
    </z-data-card-item>
    <z-data-card-item label="銀行口座種別" :value="resolveBankAccountType(bankAccountType)" />
    <z-data-card-item label="口座番号" :value="bankAccountNumber" />
    <z-data-card-item label="口座名義" :value="bankAccountHolder" />
    <v-card-actions v-if="$slots.default">
      <v-spacer />
      <slot></slot>
    </v-card-actions>
  </z-data-card>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveBankAccountType } from '@zinger/enums/lib/bank-account-type'

export default defineComponent({
  name: 'ZBankAccountCard',
  props: {
    bankAccountHolder: { type: String, required: true },
    bankAccountNumber: { type: String, required: true },
    bankAccountType: { type: Number, required: true },
    bankBranchCode: { type: String, required: true },
    bankBranchName: { type: String, required: true },
    bankCode: { type: String, required: true },
    bankName: { type: String, required: true },
    title: { type: String, default: '銀行口座' }
  },
  setup: () => ({
    resolveBankAccountType
  })
})
</script>
