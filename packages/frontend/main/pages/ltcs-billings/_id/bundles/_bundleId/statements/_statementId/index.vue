<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page v-if="statement" data-page-ltcs-billing-statement compact :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="状態" :icon="statusIcon" :value="resolveLtcsBillingStatus(statement.status)" />
      <z-data-card-item label="保険者" :icon="$icons.city">
        <span>{{ statement.insurerName }}（{{ statement.insurerNumber }}）</span>
      </z-data-card-item>
      <z-data-card-item label="サービス提供年月" :icon="$icons.date">
        <z-era-month :value="bundle.providedIn" />
      </z-data-card-item>
    </z-data-card>

    <v-container class="pa-0">
      <v-row dense>
        <v-col class="d-flex flex-column align-stretch" cols="12" sm="6">
          <z-data-card title="被保険者">
            <z-data-card-item label="被保険者番号" :icon="$icons.ltcsInsNumber" :value="user.insNumber" />
            <z-data-card-item label="氏名" :icon="$icons.user">
              <span>{{ user.name.displayName }}（{{ user.name.phoneticDisplayName }}）</span>
            </z-data-card-item>
            <z-data-card-item label="生年月日" :icon="$icons.birthday">
              <z-era-date :value="user.birthday" />
            </z-data-card-item>
            <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(user.sex)" />
            <z-data-card-item label="要介護状態区分" :icon="$icons.level" :value="resolveLtcsLevel(user.ltcsLevel)" />
            <z-data-card-item label="認定有効期間" :icon="$icons.dateRange">
              <z-era-date :value="user.activatedOn" />
              <span>〜</span>
              <z-era-date :value="user.deactivatedOn" />
            </z-data-card-item>
          </z-data-card>
        </v-col>
        <v-col class="d-flex flex-column align-stretch" cols="12" sm="6">
          <z-data-card title="請求事業者">
            <z-data-card-item label="事業所番号" :icon="$icons.office" :value="office.code" />
            <z-data-card-item label="事業所名称" :value="office.name" />
            <z-data-card-item label="所在地" :icon="$icons.addr">
              〒{{ office.addr.postcode }}<br>
              {{ resolvePrefecture(office.addr.prefecture) }}{{ office.addr.city }}{{ office.addr.street }}
              <template v-if="office.addr.apartment"><br>{{ office.addr.apartment }}</template>
            </z-data-card-item>
            <z-data-card-item label="連絡先" :icon="$icons.tel" :value="office.tel" />
          </z-data-card>
        </v-col>
      </v-row>
    </v-container>

    <z-data-card title="居宅サービス計画">
      <z-data-card-item
        label="作成区分"
        :icon="$icons.carePlanAuthor"
        :value="resolveLtcsCarePlanAuthorType(statement.carePlanAuthor.authorType)"
      />
      <template v-if="displayCarePlanAuthorOffice">
        <z-data-card-item label="事業所番号" :icon="$icons.office" :value="statement.carePlanAuthor.code" />
        <z-data-card-item label="事業所名称" :value="statement.carePlanAuthor.name" />
      </template>
    </z-data-card>

    <z-data-card v-if="displayAgreedOn || displayExpiredOn" title="開始年月日・中止年月日">
      <z-data-card-item v-if="displayAgreedOn" label="開始年月日" :icon="$icons.dateStart">
        <z-era-date :value="statement.agreedOn" />
      </z-data-card-item>
      <template v-if="displayExpiredOn">
        <z-data-card-item label="中止年月日" :icon="$icons.dateEnd">
          <z-era-date :value="statement.expiredOn" />
        </z-data-card-item>
        <z-data-card-item label="中止理由" :value="resolveLtcsExpiredReason(statement.expiredReason)" />
      </template>
    </z-data-card>

    <z-ltcs-billing-statement-item-list-card
      :items="statement.items"
      :office-id="billing.office.officeId"
      :provided-in="bundle.providedIn"
    />

    <z-ltcs-billings-statements-form
      :errors="errors"
      :progress="progress"
      :value="formValue"
      :statement="statement"
      :can-update-content="canUpdateContent"
      @submit="submit"
    />

    <z-system-meta-card :id="statement.id" :created-at="statement.createdAt" :updated-at="statement.updatedAt" />

    <z-fab-speed-dial v-if="canUpdateStatus" data-fab :icon="$icons.edit">
      <z-fab-speed-dial-button v-if="isReady" :icon="$icons.fix" @click="fix">
        明細書を確定する
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button v-else-if="isFixed" :icon="$icons.undo" @click="unfix">
        明細書を未確定にする
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive } from '@nuxtjs/composition-api'
import { LtcsBillingStatus, resolveLtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { LtcsCarePlanAuthorType, resolveLtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { resolveLtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { resolveLtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { resolveLtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import { Permission } from '@zinger/enums/lib/permission'
import { resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { resolveSex } from '@zinger/enums/lib/sex'
import { nonEmpty } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import { ltcsBillingStatementStoreKey } from '~/composables/stores/use-ltcs-billing-statement-store'
import { ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useLtcsBillingStatusIcon } from '~/composables/use-ltcs-billing-status-icon'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'

export default defineComponent({
  name: 'LtcsBillingStatementsViewPage',
  middleware: [auth(Permission.viewBillings)],
  setup () {
    const { $snackbar } = usePlugins()
    const { isAuthorized } = useAuth()
    const { errors, progress, withAxios } = useAxios()

    const billingStore = useInjected(ltcsBillingStoreKey)
    const store = useInjected(ltcsBillingStatementStoreKey)
    const { billing, bundle, statement } = store.state

    const displayCarePlanAuthorOffice = computed(() => {
      return statement.value?.carePlanAuthor.authorType === LtcsCarePlanAuthorType.careManagerOffice
    })

    const isFixed = computed(() => statement.value?.status === LtcsBillingStatus.fixed)
    const isReady = computed(() => statement.value?.status === LtcsBillingStatus.ready)
    const hasUpdatePermission = computed(() => isAuthorized.value([Permission.updateBillings]))
    const canUpdateContent = computed(() => hasUpdatePermission.value && !isFixed.value)
    const canUpdateStatus = computed(() => {
      return hasUpdatePermission.value &&
        statement.value &&
        statement.value.status !== LtcsBillingStatus.checking
    })
    const createUpdateStatusFunction = (status: LtcsBillingStatus) => () => withAxios(
      async () => {
        const { billingId, bundleId, id } = statement.value!
        await store.updateStatus({ billingId, bundleId, id }, status)
        await billingStore.get({ id: billingId })
        $snackbar.success('請求の状態を変更しました。')
      },
      () => $snackbar.error('請求の状態変更に失敗しました。')
    )
    const fix = createUpdateStatusFunction(LtcsBillingStatus.fixed)
    const unfix = createUpdateStatusFunction(LtcsBillingStatus.ready)

    // 明細書更新
    const aggregates = statement.value!.aggregates
    const formValue = reactive(Object.fromEntries(aggregates.map(x => {
      return [x.serviceDivisionCode, String(x.plannedScore)]
    })))
    type Aggregates = LtcsBillingStatementsApi.UpdateForm['aggregates']
    const submit = (formValue: {[p: string]: string}) => {
      if (!canUpdateContent) {
        return
      }
      return withAxios(
        async () => {
          const aggregates = Object.entries(formValue)
            .map(([k, v]) => ({ serviceDivisionCode: k, plannedScore: parseInt(v) })) as Aggregates
          await store.update({
            billingId: billing.value!.id,
            bundleId: bundle.value!.id,
            form: { aggregates },
            id: statement.value!.id
          })
          $snackbar.success('明細書を編集しました。')
        },
        () => {
          $snackbar.error('明細書の編集に失敗しました。')
        }
      )
    }
    return {
      ...useBreadcrumbs('ltcsBillings.statement.view', billing),
      ...useLtcsBillingStatusIcon(statement),
      billing,
      bundle,
      canUpdateContent,
      canUpdateStatus,
      displayAgreedOn: computed(() => nonEmpty(statement.value?.agreedOn)),
      displayCarePlanAuthorOffice,
      displayExpiredOn: computed(() => nonEmpty(statement.value?.expiredOn)),
      errors,
      fix,
      formValue,
      isFixed,
      isReady,
      numeral: (x: number | '-' = '-', format: string = '0,0') => numeral(x, format),
      office: computed(() => billing.value?.office),
      progress,
      resolveLtcsBillingStatus,
      resolveLtcsCarePlanAuthorType,
      resolveLtcsExpiredReason,
      resolveLtcsLevel,
      resolveLtcsServiceDivisionCode,
      resolvePrefecture,
      resolveSex,
      statement,
      submit,
      unfix,
      user: computed(() => statement.value?.user)
    }
  },
  head: () => ({
    title: '介護保険サービス請求 明細書'
  })
})
</script>
