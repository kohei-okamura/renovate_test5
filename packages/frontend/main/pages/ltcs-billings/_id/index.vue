<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page v-if="billing" class="page-dws-billing" compact :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="事業所名" :icon="$icons.office" :value="billing.office.name" />
      <z-data-card-item label="事業所番号" :value="billing.office.code" />
      <z-data-card-item label="処理対象年月" :icon="$icons.dateRange">
        <z-era-month :value="billing.transactedIn" />
      </z-data-card-item>
      <z-data-card-item label="請求の状態" :icon="statusIcon" :value="resolveLtcsBillingStatus(billing.status)" />
    </z-data-card>

    <z-ltcs-billing-status-aggregate-card :aggregate="statusAggregate" :has-statements="hasStatements" />

    <z-ltcs-billing-statement-list-card
      v-for="providedIn in providedInList"
      :key="providedIn"
      :billing-status="billing.status"
      :items="groupedStatements[providedIn]"
      :provided-in="providedIn"
    />

    <z-billing-file-list-card :downloadable="canDownload" :items="billing.files" @click:download="download" />

    <z-system-meta-card :id="billing.id" :created-at="billing.createdAt" :updated-at="billing.updatedAt" />

    <z-fab-speed-dial v-if="canUpdateStatus" data-fab :icon="$icons.edit">
      <z-fab-speed-dial-button v-if="isReady" :icon="$icons.fix" @click="fix">
        請求を確定する
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button v-else-if="isFixed" :icon="$icons.disableContract" @click="toDisable">
        請求を無効にする
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { LtcsBillingStatus, resolveLtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { colors } from '~/colors'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { eraMonth } from '~/composables/era-date'
import { ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { useLtcsBillingStatusIcon } from '~/composables/use-ltcs-billing-status-icon'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsBillingFile } from '~/models/ltcs-billing-file'

export default defineComponent({
  name: 'LtcsBillingsViewPage',
  middleware: [auth(Permission.viewBillings)],
  setup () {
    const { $api, $confirm, $download, $snackbar } = usePlugins()
    const { isAuthorized } = useAuth()
    const { withAxios } = useAxios()

    const store = useInjected(ltcsBillingStoreKey)
    const { billing, groupedStatements, hasStatements, providedInList, statusAggregate } = store.state

    const isFixed = computed(() => billing.value?.status === LtcsBillingStatus.fixed)
    const isReady = computed(() => billing.value?.status === LtcsBillingStatus.ready)

    const canUpdateStatus = computed(() => {
      // ログインユーザーが請求の更新権限を持っている、かつ請求の状態が '未確定' or '確定済'
      const status = billing.value?.status
      return isAuthorized.value([Permission.updateBillings]) &&
        (status === LtcsBillingStatus.ready || status === LtcsBillingStatus.fixed)
    })
    const { execute } = useJobWithNotification()
    const id = billing.value!.id
    const fix = () => withAxios(
      async () => {
        await store.updateStatus(id, LtcsBillingStatus.fixed)
        $snackbar.success('請求の状態を変更しました。')
        const job = store.state.job.value
        if (job) {
          await execute({
            notificationProps: {
              text: {
                progress: '介護保険サービス請求書ファイルの作成を開始します',
                success: '介護保険サービス請求書ファイルの作成に成功しました',
                failure: '介護保険サービス請求書ファイルの作成に失敗しました'
              }
            },
            process: () => Promise.resolve({ job })
          })
        }
      },
      () => $snackbar.error('請求の状態変更に失敗しました。')
    )
    const toDisable = () => withAxios(
      async () => {
        const confirmed = await $confirm.show({
          color: colors.critical,
          message: 'この請求を無効にします。この操作は取り消すことができません。\n\n本当によろしいですか？',
          positive: '無効にする'
        })
        if (confirmed) {
          await store.updateStatus(id, LtcsBillingStatus.disabled)
          $snackbar.success('請求の状態を変更しました。')
        }
      },
      () => $snackbar.error('請求の状態変更に失敗しました。')
    )
    const canDownload = computed(() => isAuthorized.value([Permission.downloadBillings]))
    const download = ({ name, token }: LtcsBillingFile) => withAxios(async () => {
      if (billing.value) {
        const id = billing.value.id
        const { url } = await $api.ltcsBillings.file({ id, token })
        await $download.uri(url, name)
      } else {
        $snackbar.error('ファイルのURLが取得できませんでした。')
      }
    })

    return {
      ...useBreadcrumbs('ltcsBillings.view'),
      ...useLtcsBillingStatusIcon(billing),
      billing,
      canDownload,
      canUpdateStatus,
      download,
      eraMonth,
      fix,
      groupedStatements,
      hasStatements,
      isFixed,
      isReady,
      providedInList,
      resolveLtcsBillingStatus,
      statusAggregate,
      toDisable
    }
  },
  head: () => ({
    title: '介護保険サービス請求詳細'
  })
})
</script>
