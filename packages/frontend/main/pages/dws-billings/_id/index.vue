<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page v-if="billing" data-page-dws-billing :class="$style.root" :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="事業所名" :icon="$icons.office" :value="billing.office.name" />
      <z-data-card-item label="事業所番号" :value="billing.office.code" />
      <z-data-card-item label="処理対象年月" :icon="$icons.dateRange" :value="eraMonth(billing.transactedIn)" />
      <z-data-card-item label="請求の状態" :icon="statusIcon" :value="resolveDwsBillingStatus(billing.status)" />
    </z-data-card>
    <z-data-card
      v-for="(v, i) in ['statement', 'report']"
      :key="`statuses_${i}`"
      :title="`${ i === 0 ? '給付費明細書' : 'サービス提供実績記録票'}の状態`"
    >
      <v-simple-table class="status-count" dense>
        <template #default>
          <thead>
            <tr>
              <th
                v-for="(item, j) in statusTableHeaders"
                :key="`statusHeader-${j}`"
                :class="item.align ? `text-${item.align}` : `text-end`"
              >
                {{ item.text }}
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-if="statusCounts.length === 0">
              <th class="pt-2 text-center" :colspan="statusTableHeaders.length">データがありません</th>
            </template>
            <template v-else>
              <tr v-for="(item, j) in statusCounts" :key="j">
                <td class="text-start">{{ eraMonth(item.providedIn) }}</td>
                <td class="text-end">{{ item[v].checking }}</td>
                <td class="text-end">{{ item[v].ready }}</td>
                <td class="text-end">{{ item[v].fixed }}</td>
                <td class="text-end">{{ item[v].disabled }}</td>
                <td class="text-end">{{ item[v].total }}</td>
              </tr>
            </template>
          </tbody>
        </template>
      </v-simple-table>
    </z-data-card>
    <z-data-card title="上限額管理結果票の状態">
      <v-simple-table class="status-count" dense>
        <template #default>
          <thead>
            <tr>
              <th class="text-start">サービス提供年月</th>
              <th class="text-end">未作成</th>
              <th class="text-end">未入力</th>
              <th class="text-end">入力中</th>
              <th class="text-end">入力済</th>
              <th class="text-end">合計</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="statusCounts.length === 0">
              <th class="pt-2 text-center" :colspan="statusTableHeaders.length">データがありません</th>
            </template>
            <template v-else>
              <tr v-for="(item, i) in statusCounts" :key="i">
                <td class="text-start">{{ eraMonth(item.providedIn) }}</td>
                <td class="text-end">{{ item.copayCoordination.uncreated }}</td>
                <td class="text-end">{{ item.copayCoordination.unfilled }}</td>
                <td class="text-end">{{ item.copayCoordination.checking }}</td>
                <td class="text-end">{{ item.copayCoordination.fulfilled }}</td>
                <td class="text-end">{{ item.copayCoordination.total }}</td>
              </tr>
            </template>
          </tbody>
        </template>
      </v-simple-table>
    </z-data-card>
    <z-dws-billing-statement-list-card
      v-for="(group, i) in billingUnitsGroups"
      :key="i"
      :billing-status="billing.status"
      :items="filter(i, group.units)"
      :provided-in="group.providedIn"
    />
    <z-billing-file-list-card :downloadable="canDownload" :items="billing.files" @click:download="download" />
    <z-system-meta-card :id="billing.id" :created-at="billing.createdAt" :updated-at="billing.updatedAt" />
    <z-fab-speed-dial v-if="canUpdateStatus" data-fab :icon="$icons.edit">
      <z-fab-speed-dial-button v-if="canUpdateStatus" :icon="$icons.disableContract" @click="speedDial.disable">
        請求を無効にする
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button v-if="isReady" :icon="$icons.fix" @click="speedDial.fix">
        請求を確定する
      </z-fab-speed-dial-button>
      <template v-else-if="isFixed">
        <z-fab-speed-dial-button :icon="$icons.copy" @click="speedDial.copy">
          コピーを作成する
        </z-fab-speed-dial-button>
      </template>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref } from '@nuxtjs/composition-api'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { debounce } from '@zinger/helpers'
import { colors } from '~/colors'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { eraMonth } from '~/composables/era-date'
import { dwsBillingStoreKey, DwsBillingUnit } from '~/composables/stores/use-dws-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDwsBillingStatusIcon } from '~/composables/use-dws-billing-status-icon'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBillingFile } from '~/models/dws-billing-file'

export default defineComponent({
  name: 'DwsBillingViewPage',
  setup () {
    const { $api, $confirm, $download, $snackbar } = usePlugins()
    const { isAuthorized } = useAuth()
    const { progress, withAxios } = useAxios()
    const { execute } = useJobWithNotification()

    const store = useInjected(dwsBillingStoreKey)
    const { billing, billingUnitsGroups, statusCounts } = store.state
    const canUpdate = computed(() => isAuthorized.value([Permission.updateBillings]))
    const canUpdateStatus = computed(() => {
      // ログインユーザーが請求の更新権限を持っている かつ 請求の状態が '無効'以外の場合
      const status = billing.value?.status
      return canUpdate.value && status !== DwsBillingStatus.disabled
    })
    const useFilter = () => {
      const filterConditions = computed(() => reactive(billingUnitsGroups.value.map(_ => ({ status: 0, word: '' }))))
      const onInputWord = debounce({ wait: 250 }, (index: number, word: string | undefined) => {
        filterConditions.value[index].word = word?.trim() ?? ''
      })
      return {
        filterConditions,
        filter: (index: number, billingUnits: DwsBillingUnit[]) => billingUnits.filter(v => {
          const conditions = filterConditions.value
          const status: number = conditions[index].status
          if (!v.statement || conditions.length === 0 || (status && v.statement.status !== status)) {
            return false
          }
          const word = conditions[index].word
          return !word ? true : [v.userName, v.userPhoneticName, v.cityName].some(v => v.includes(word))
        }),
        onInputWord
      }
    }
    const statusText = {
      checking: resolveDwsBillingStatus(DwsBillingStatus.checking),
      ready: resolveDwsBillingStatus(DwsBillingStatus.ready),
      fixed: resolveDwsBillingStatus(DwsBillingStatus.fixed),
      disabled: resolveDwsBillingStatus(DwsBillingStatus.disabled)
    }
    const statusTableHeaders = [
      { text: 'サービス提供年月', align: 'start' },
      { text: statusText.checking },
      { text: statusText.ready },
      { text: statusText.fixed },
      { text: statusText.disabled },
      { text: '合計' }
    ]
    const download = ({ name, token }: DwsBillingFile) => withAxios(async () => {
      if (billing.value) {
        const id = billing.value.id
        const { url } = await $api.dwsBillings.file({ id, token })
        await $download.uri(url, name)
      } else {
        $snackbar.error('ファイルのURLが取得できませんでした。')
      }
    })
    const useSpeedDial = () => {
      const updateStatus = (status: DwsBillingStatus, f?: () => Promise<void>) => () => withAxios(
        async () => {
          const confirmed = status !== DwsBillingStatus.disabled || await $confirm.show({
            color: colors.critical,
            message: 'この請求を無効にします。この操作は取り消すことができません。\n\n本当によろしいですか？',
            positive: '無効にする'
          })
          if (confirmed) {
            const id = billing.value!.id
            await store.updateStatus(id, status)
            $snackbar.success('請求の状態を変更しました。')
            if (f) {
              await f()
            }
          }
        },
        () => $snackbar.error('請求の状態変更に失敗しました。')
      )
      const disable = updateStatus(DwsBillingStatus.disabled)
      const fix = updateStatus(DwsBillingStatus.fixed, async () => {
        const job = store.state.job.value
        if (job) {
          await execute({
            notificationProps: {
              text: {
                progress: '障害福祉サービス請求書ファイルの作成を開始します',
                success: '障害福祉サービス請求書ファイルの作成に成功しました',
                failure: '障害福祉サービス請求書ファイルの作成に失敗しました'
              }
            },
            process: () => Promise.resolve({ job })
          })
        }
      })
      const copy = async () => {
        const isPositiveClicked = await $confirm.show({
          color: colors.critical,
          message: 'この請求をコピーして新しい請求を作成します。この請求は無効化されます。\n\n本当によろしいですか？',
          positive: 'コピーを作成'
        })
        if (isPositiveClicked) {
          const linkToOnSuccess = ref('')
          return withAxios(() => execute({
            notificationProps: {
              linkToOnSuccess: () => linkToOnSuccess.value,
              text: {
                progress: '請求情報のコピーを作成中です...',
                success: '請求情報のコピーを作成しました',
                failure: '請求情報のコピーに失敗しました'
              }
            },
            process: () => $api.dwsBillings.copy({ id: billing.value!.id }),
            success: job => {
              linkToOnSuccess.value = `/dws-billings/${job.data.billing.id}`
              store.get({ id: billing.value!.id })
            }
          }))
        }
      }
      return {
        copy,
        disable,
        fix
      }
    }

    return {
      ...useBreadcrumbs('dwsBillings.view'),
      ...useDwsBillingStatusIcon(billing),
      ...useFilter(),
      billing,
      billingUnitsGroups,
      download,
      canDownload: computed(() => isAuthorized.value([Permission.downloadBillings])),
      canUpdate,
      canUpdateStatus,
      eraMonth,
      isFixed: computed(() => billing.value?.status === DwsBillingStatus.fixed),
      isReady: computed(() => billing.value?.status === DwsBillingStatus.ready),
      progress,
      resolveDwsBillingStatus,
      speedDial: useSpeedDial(),
      statusCounts,
      statusTableHeaders
    }
  },
  head: () => ({
    title: '障害福祉サービス請求詳細'
  })
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles';

.root {
  :global {
    tr:hover {
      background: inherit !important;
    }

    .file-type {
      vertical-align: middle;
    }
  }
}

@media #{map-get($display-breakpoints, 'xs-only')} {
  .root {
    :global {
      .v-data-table.status-count > .v-data-table__wrapper > table {
        th,
        td {
          padding: 0 6px;

          &:first-of-type {
            padding-left: 8px;
          }

          &:last-of-type {
            padding-right: 8px;
          }
        }
      }
    }
  }
}

;
</style>
