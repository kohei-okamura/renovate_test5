<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page v-if="office" compact :breadcrumbs="breadcrumbs" :tabs="tabs">
    <z-tabs-items v-model="tab" class="transparent">
      <v-tab-item value="office">
        <z-data-card title="基本情報">
          <z-data-card-item label="事業者区分" :icon="$icons.category" :value="resolvePurpose(office.purpose)" />
          <z-data-card-item label="事業所名" :icon="$icons.office" :value="office.name" />
          <z-data-card-item label="事業所名：フリガナ" :value="office.phoneticName" />
          <z-data-card-item label="事業所名：略称" :value="office.abbr" />
          <template v-if="isInternal">
            <z-data-card-item v-if="officeGroup" label="事業所グループ" :value="officeGroup.name" />
          </template>
          <template v-else-if="isExternal">
            <z-data-card-item label="法人名" :value="office.corporationName || '-'" />
            <z-data-card-item label="法人名：フリガナ" :value="office.phoneticCorporationName || '-'" />
          </template>
          <z-data-card-item label="状態" :icon="statusIcon" :value="resolveOfficeStatus(office.status)" />
          <z-data-card-item label="住所" :icon="$icons.addr">
            〒{{ office.addr.postcode }}<br>
            {{ resolvePrefecture(office.addr.prefecture) }}{{ office.addr.city }}{{ office.addr.street }}
            <template v-if="office.addr.apartment"><br>{{ office.addr.apartment }}</template>
          </z-data-card-item>
          <z-data-card-item label="電話番号" :icon="$icons.tel" :value="office.tel" />
          <z-data-card-item label="FAX番号" :value="office.fax || '-'" />
          <z-data-card-item label="メールアドレス" :icon="$icons.email">
            <a :href="`mailto:${office.email}`">{{ office.email }}</a>
          </z-data-card-item>
          <z-data-card-item v-if="office.qualifications.length" label="指定区分">
            <v-chip v-for="x in office.qualifications" :key="x" label small>{{ resolveOfficeQualification(x) }}</v-chip>
          </z-data-card-item>
        </z-data-card>
        <z-data-card v-if="office.dwsGenericService" title="障害福祉サービス">
          <z-data-card-item label="事業所番号" :icon="$icons.dws" :value="office.dwsGenericService.code" />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.dwsGenericService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.dwsGenericService.designationExpiredOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="地域区分" :value="resolveDwsAreaGrade(office.dwsGenericService.dwsAreaGradeId)" />
          </template>
        </z-data-card>
        <z-data-card v-if="office.dwsCommAccompanyService" title="障害福祉サービス：地域生活支援事業・移動支援">
          <z-data-card-item label="事業所番号" :icon="$icons.dws" :value="office.dwsCommAccompanyService.code" />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.dwsCommAccompanyService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.dwsCommAccompanyService.designationExpiredOn || '-'" />
            </z-data-card-item>
          </template>
        </z-data-card>
        <z-data-card v-if="office.ltcsHomeVisitLongTermCareService" title="介護保険サービス：訪問介護">
          <z-data-card-item
            label="事業所番号"
            :icon="$icons.ltcs"
            :value="office.ltcsHomeVisitLongTermCareService.code"
          />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.ltcsHomeVisitLongTermCareService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.ltcsHomeVisitLongTermCareService.designationExpiredOn || '-'" />
            </z-data-card-item>
            <z-data-card-item
              label="地域区分"
              :value="resolveLtcsAreaGrade(office.ltcsHomeVisitLongTermCareService.ltcsAreaGradeId)"
            />
          </template>
        </z-data-card>
        <z-data-card v-if="office.ltcsCompHomeVisitingService" title="介護保険サービス：総合事業・訪問型サービス">
          <z-data-card-item label="事業所番号" :icon="$icons.ltcs" :value="office.ltcsCompHomeVisitingService.code" />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.ltcsCompHomeVisitingService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.ltcsCompHomeVisitingService.designationExpiredOn || '-'" />
            </z-data-card-item>
          </template>
        </z-data-card>
        <z-data-card v-if="office.ltcsCareManagementService" title="介護保険サービス：居宅介護支援">
          <z-data-card-item label="事業所番号" :icon="$icons.ltcs" :value="office.ltcsCareManagementService.code" />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.ltcsCareManagementService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.ltcsCareManagementService.designationExpiredOn || '-'" />
            </z-data-card-item>
            <z-data-card-item
              label="地域区分"
              :value="resolveLtcsAreaGrade(office.ltcsCareManagementService.ltcsAreaGradeId)"
            />
          </template>
        </z-data-card>
        <z-data-card v-if="office.ltcsPreventionService" title="介護保険サービス：介護予防支援">
          <z-data-card-item label="事業所番号" :icon="$icons.ltcs" :value="office.ltcsPreventionService.code" />
          <template v-if="isInternal">
            <z-data-card-item label="開設日">
              <z-era-date :value="office.ltcsPreventionService.openedOn || '-'" />
            </z-data-card-item>
            <z-data-card-item label="指定更新期日">
              <z-era-date :value="office.ltcsPreventionService.designationExpiredOn || '-'" />
            </z-data-card-item>
          </template>
        </z-data-card>
        <z-system-meta-card :id="office.id" :created-at="office.createdAt" :updated-at="office.updatedAt" />
        <z-fab
          v-if="canUpdateOffices"
          bottom
          data-fab
          fixed
          nuxt
          right
          :icon="$icons.edit"
          :to="`/offices/${office.id}/edit`"
        >
          事業所情報を編集
        </z-fab>
      </v-tab-item>
      <v-tab-item value="calc-specs">
        <template v-if="hasQualificationOfCalcSpecs">
          <z-home-help-service-calc-specs-card
            v-if="hasDwsHomeHelpService"
            :items="homeHelpServiceCalcSpecs"
            :office="office"
          />
          <z-visiting-care-for-pwsd-calc-specs-card
            v-if="hasDwsVisitingCareForPwsd"
            :items="visitingCareForPwsdCalcSpecs"
            :office="office"
          />
          <z-home-visit-long-term-care-calc-specs-card
            v-if="hasLtcsHomeVisitLongTermCare"
            :items="homeVisitLongTermCareCalcSpecs"
            :office="office"
          />
        </template>
        <z-subheader v-else class="mt-4">算定情報がありません</z-subheader>
        <z-fab-speed-dial
          v-if="canRegisterCalcSpecs"
          data-fab
          :icon="$icons.add"
        >
          <z-fab-speed-dial-button
            v-if="hasLtcsHomeVisitLongTermCare"
            :icon="$icons.add"
            :to="`/offices/${office.id}/home-visit-long-term-care-calc-specs/new`"
          >
            算定情報（介保・訪問介護）を登録
          </z-fab-speed-dial-button>
          <z-fab-speed-dial-button
            v-if="hasDwsVisitingCareForPwsd"
            :icon="$icons.add"
            :to="`/offices/${office.id}/visiting-care-for-pwsd-calc-specs/new`"
          >
            算定情報（障害・重度訪問介護）を登録
          </z-fab-speed-dial-button>
          <z-fab-speed-dial-button
            v-if="hasDwsHomeHelpService"
            :icon="$icons.add"
            :to="`/offices/${office.id}/home-help-service-calc-specs/new`"
          >
            算定情報（障害・居宅介護）を登録
          </z-fab-speed-dial-button>
        </z-fab-speed-dial>
      </v-tab-item>
    </z-tabs-items>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { OfficeQualification, resolveOfficeQualification } from '@zinger/enums/lib/office-qualification'
import { resolveOfficeStatus } from '@zinger/enums/lib/office-status'
import { Permission } from '@zinger/enums/lib/permission'
import { resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { Purpose, resolvePurpose } from '@zinger/enums/lib/purpose'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { officeStateKey } from '~/composables/stores/use-office-store'
import { tabs } from '~/composables/tabs'
import { useAuth } from '~/composables/use-auth'
import { useDwsAreaGrades } from '~/composables/use-dws-area-grades'
import { useInjected } from '~/composables/use-injected'
import { useLtcsAreaGrades } from '~/composables/use-ltcs-area-grades'
import { useOfficeStatusIcon } from '~/composables/use-office-status-icon'
import { usePlugins } from '~/composables/use-plugins'
import { useTabBindings } from '~/composables/use-tab-bindings'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'OfficesViewPage',
  middleware: [auth(Permission.viewInternalOffices, Permission.viewExternalOffices)],
  setup () {
    const { $tabs } = usePlugins()
    const { isAuthorized } = useAuth()
    const { resolveDwsAreaGrade } = useDwsAreaGrades()
    const { resolveLtcsAreaGrade } = useLtcsAreaGrades()
    const {
      office,
      officeGroup,
      homeHelpServiceCalcSpecs,
      homeVisitLongTermCareCalcSpecs,
      visitingCareForPwsdCalcSpecs
    } = useInjected(officeStateKey)
    const { tab } = $tabs
    const hasLtcsHomeVisitLongTermCare = computed(() =>
      office.value?.qualifications.includes(OfficeQualification.ltcsHomeVisitLongTermCare)
    )
    const hasDwsVisitingCareForPwsd = computed(() =>
      office.value?.qualifications.includes(OfficeQualification.dwsVisitingCareForPwsd)
    )
    const hasDwsHomeHelpService = computed(() =>
      office.value?.qualifications.includes(OfficeQualification.dwsHomeHelpService)
    )
    const hasQualificationOfCalcSpecs = computed(() =>
      hasLtcsHomeVisitLongTermCare.value || hasDwsVisitingCareForPwsd.value || hasDwsHomeHelpService.value
    )
    const canUpdateOffices = computed(() =>
      isAuthorized.value([Permission.updateInternalOffices, Permission.updateExternalOffices])
    )
    const canRegisterCalcSpecs = computed(() => canUpdateOffices.value && hasQualificationOfCalcSpecs.value)
    const isInternal = computed(() => office.value?.purpose === Purpose.internal)
    const isExternal = computed(() => office.value?.purpose === Purpose.external)
    return {
      ...useBreadcrumbs('offices.view', office),
      ...useOfficeStatusIcon(office),
      ...useTabBindings(),
      canRegisterCalcSpecs,
      canUpdateOffices,
      resolveDwsAreaGrade,
      resolveLtcsAreaGrade,
      resolveOfficeQualification,
      resolvePrefecture,
      resolvePurpose,
      hasDwsHomeHelpService,
      hasDwsVisitingCareForPwsd,
      hasLtcsHomeVisitLongTermCare,
      hasQualificationOfCalcSpecs,
      homeHelpServiceCalcSpecs,
      homeVisitLongTermCareCalcSpecs,
      isInternal,
      isExternal,
      office,
      officeGroup,
      tab,
      tabs: computed(() => {
        return isInternal.value
          ? tabs([
            { label: '基本情報', to: '#office' },
            { label: '算定情報', to: '#calc-specs' }
          ])
          : tabs([{ label: '基本情報', to: '#office' }])
      }),
      resolveOfficeStatus,
      visitingCareForPwsdCalcSpecs
    }
  },
  fetch ({ redirect, route }: NuxtContext) {
    if (route.hash === '') {
      redirect(route.path + '#base')
    }
  },
  head: () => ({
    title: '事業所詳細'
  })
})
</script>
