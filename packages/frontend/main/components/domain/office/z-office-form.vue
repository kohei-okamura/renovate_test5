<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-purpose vid="purpose" :rules="rules.purpose">
            <v-radio-group
              v-model="form.purpose"
              :error-messages="errors"
            >
              <template #label><span class="text-caption">事業者区分 *</span></template>
              <v-row>
                <v-col v-for="x in purposeOptions" :key="x.value" md="2" sm="3">
                  <v-radio :label="x.text" :value="x.value" />
                </v-col>
              </v-row>
            </v-radio-group>
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-name vid="name" :rules="rules.name">
            <z-text-field v-model.trim="form.name" label="事業所名 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-phonetic-name vid="phoneticName" :rules="rules.phoneticName">
            <z-text-field
              v-model="form.phoneticName"
              v-auto-kana
              data-phonetic-name-input
              label="事業所名：フリガナ *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-abbr vid="abbr" :rules="rules.abbr">
            <z-text-field
              v-model.trim="form.abbr"
              :label="`事業所名：略称 ${isInternal ? '*' : ''}`"
              :error-messages="errors"
            />
          </z-form-card-item>
          <z-form-card-item
            v-if="isInternal"
            v-slot="{ errors }"
            data-office-group-id
            vid="officeGroupId"
            :rules="rules.officeGroupId"
          >
            <z-select
              v-model="form.officeGroupId"
              label="事業所グループ *"
              :error-messages="errors"
              :items="officeGroupOptions"
              :loading="isLoadingOfficeGroups"
            />
          </z-form-card-item>
          <template v-else-if="isExternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-corporation-name
              vid="corporationName"
              :rules="rules.corporationName"
            >
              <z-text-field v-model.trim="form.corporationName" label="法人名" :error-messages="errors" />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-phonetic-corporation-name
              vid="phoneticCorporationName"
              :rules="rules.phoneticCorporationName"
            >
              <z-text-field
                v-model="form.phoneticCorporationName"
                v-auto-kana
                data-phonetic-corporation-name-input
                label="法人名：フリガナ"
                :error-messages="errors"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="statusIcon">
          <z-form-card-item v-slot="{ errors }" data-status vid="status" :rules="rules.status">
            <z-select
              v-model="form.status"
              label="状態 *"
              :error-messages="errors"
              :items="statusOptions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.addr">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-postcode vid="postcode" :rules="rules.postcode">
                <z-text-field
                  v-model.trim="form.postcode"
                  v-mask="'###-####'"
                  type="tel"
                  :label="`郵便番号 ${isInternal ? '*' : ''}`"
                  :error-messages="errors"
                >
                  <template #append-outer>
                    <z-postcode-resolver :postcode="form.postcode" @update="onPostcodeResolved" />
                  </template>
                </z-text-field>
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-prefecture vid="prefecture" :rules="rules.prefecture">
                <z-select
                  v-model="form.prefecture"
                  :label="`都道府県 ${isInternal ? '*' : ''}`"
                  :error-messages="errors"
                  :items="prefectureOptions"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
          <z-form-card-item v-slot="{ errors }" data-city vid="city" :rules="rules.city">
            <z-text-field
              v-model.trim="form.city"
              :label="`市区町村 ${isInternal ? '*' : ''}`"
              :error-messages="errors"
            />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-street vid="street" :rules="rules.street">
            <z-text-field
              ref="streetInput"
              v-model.trim="form.street"
              :label="`町名・番地 ${isInternal ? '*' : ''}`"
              :error-messages="errors"
            />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-apartment vid="apartment" :rules="rules.apartment">
            <z-text-field v-model.trim="form.apartment" label="建物名など" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.tel">
          <z-flex>
            <z-form-card-item v-slot="{ errors }" data-tel vid="tel" :rules="rules.tel">
              <z-text-field
                v-model.trim="form.tel"
                v-phone-number
                data-tel-input
                :label="`電話番号 ${isInternal ? '*' : ''}`"
                type="tel"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item v-slot="{ errors }" data-fax vid="fax" :rules="rules.fax">
              <z-text-field
                v-model.trim="form.fax"
                v-phone-number
                data-fax-input
                label="FAX番号"
                type="tel"
                :error-messages="errors"
              />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="isInternal" :icon="$icons.email">
          <z-form-card-item v-slot="{ errors }" data-email vid="email" :rules="rules.email">
            <z-text-field v-model.trim="form.email" label="メールアドレス" type="email" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set>
          <z-form-card-item
            v-slot="{ errors }"
            data-qualifications
            vid="qualifications"
          >
            <z-select
              v-model="form.qualifications"
              label="指定区分"
              multiple
              small-chips
              :error-messages="errors"
              :items="qualificationOptions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasDwsGenericService || hasDwsOthersService" title="障害福祉サービス">
        <z-form-card-item-set :icon="$icons.dws">
          <z-form-card-item
            v-slot="{ errors }"
            data-dws-generic-service-code
            vid="dwsGenericService.code"
            :rules="rules.dwsGenericService.code"
          >
            <z-text-field
              v-model.trim="form.dwsGenericService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal && hasDwsGenericService">
            <z-form-card-item
              v-slot="{ errors }"
              data-dws-generic-service-opened-on
              vid="dwsGenericService.openedOn"
              :rules="rules.dwsGenericService.openedOn"
            >
              <z-date-field
                v-model="form.dwsGenericService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-dws-generic-service-designation-expired-on
              vid="dwsGenericService.designationExpiredOn"
              :rules="rules.dwsGenericService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.dwsGenericService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-dws-generic-service-dws-area-grade-id
              vid="dwsGenericService.dwsAreaGradeId"
              :rules="rules.dwsGenericService.dwsAreaGradeId"
            >
              <z-select
                v-model="form.dwsGenericService.dwsAreaGradeId"
                label="地域区分 *"
                :error-messages="errors"
                :items="dwsAreaGradeOptions"
                :loading="isLoadingDwsAreaGrades"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasDwsCommAccompanyService" title="障害福祉サービス：地域生活支援事業・移動支援">
        <z-form-card-item-set :icon="$icons.dws">
          <z-form-card-item
            v-slot="{ errors }"
            data-dws-comm-accompany-service-code
            vid="dwsCommAccompanyService.code"
            :rules="rules.dwsCommAccompanyService.code"
          >
            <z-text-field
              v-model.trim="form.dwsCommAccompanyService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-dws-comm-accompany-service-opened-on
              vid="dwsCommAccompanyService.openedOn"
              :rules="rules.dwsCommAccompanyService.openedOn"
            >
              <z-date-field
                v-model="form.dwsCommAccompanyService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-dws-comm-accompany-service-designation-expired-on
              vid="dwsCommAccompanyService.designationExpiredOn"
              :rules="rules.dwsCommAccompanyService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.dwsCommAccompanyService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasLtcsHomeVisitLongTermCareService" title="介護保険サービス：訪問介護">
        <z-form-card-item-set :icon="$icons.ltcs">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-home-visit-long-term-care-service-code
            vid="ltcsHomeVisitLongTermCareService.code"
            :rules="rules.ltcsHomeVisitLongTermCareService.code"
          >
            <z-text-field
              v-model.trim="form.ltcsHomeVisitLongTermCareService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-home-visit-long-term-care-service-opened-on
              vid="ltcsHomeVisitLongTermCareService.openedOn"
              :rules="rules.ltcsHomeVisitLongTermCareService.openedOn"
            >
              <z-date-field
                v-model="form.ltcsHomeVisitLongTermCareService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-home-visit-long-term-care-service-designation-expired-on
              vid="ltcsHomeVisitLongTermCareService.designationExpiredOn"
              :rules="rules.ltcsHomeVisitLongTermCareService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.ltcsHomeVisitLongTermCareService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-home-visit-long-term-care-service-ltcs-area-grade-id
              vid="ltcsHomeVisitLongTermCareService.ltcsAreaGradeId"
              :rules="rules.ltcsHomeVisitLongTermCareService.ltcsAreaGradeId"
            >
              <z-select
                v-model="form.ltcsHomeVisitLongTermCareService.ltcsAreaGradeId"
                label="地域区分 *"
                :error-messages="errors"
                :items="ltcsAreaGradeOptions"
                :loading="isLoadingLtcsAreaGrades"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasLtcsCompHomeVisitingService" title="介護保険サービス：総合事業・訪問型サービス">
        <z-form-card-item-set :icon="$icons.ltcs">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-comp-home-visiting-service-code
            vid="ltcsCompHomeVisitingService.code"
            :rules="rules.ltcsCompHomeVisitingService.code"
          >
            <z-text-field
              v-model.trim="form.ltcsCompHomeVisitingService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-comp-home-visiting-service-opened-on
              vid="ltcsCompHomeVisitingService.openedOn"
              :rules="rules.ltcsCompHomeVisitingService.openedOn"
            >
              <z-date-field
                v-model="form.ltcsCompHomeVisitingService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-comp-home-visiting-service-designation-expired-on
              vid="ltcsCompHomeVisitingService.designationExpiredOn"
              :rules="rules.ltcsCompHomeVisitingService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.ltcsCompHomeVisitingService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasLtcsCareManagementService" title="介護保険サービス：居宅介護支援">
        <z-form-card-item-set :icon="$icons.ltcs">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-care-management-service-code
            vid="ltcsCareManagementService.code"
            :rules="rules.ltcsCareManagementService.code"
          >
            <z-text-field
              v-model.trim="form.ltcsCareManagementService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-care-management-service-opened-on
              vid="ltcsCareManagementService.openedOn"
              :rules="rules.ltcsCareManagementService.openedOn"
            >
              <z-date-field
                v-model="form.ltcsCareManagementService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-care-management-service-designation-expired-on
              vid="ltcsCareManagementService.designationExpiredOn"
              :rules="rules.ltcsCareManagementService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.ltcsCareManagementService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-care-management-service-ltcs-area-grade-id
              vid="ltcsCareManagementService.ltcsAreaGradeId"
              :rules="rules.ltcsCareManagementService.ltcsAreaGradeId"
            >
              <z-select
                v-model="form.ltcsCareManagementService.ltcsAreaGradeId"
                label="地域区分 *"
                :error-messages="errors"
                :items="ltcsAreaGradeOptions"
                :loading="isLoadingLtcsAreaGrades"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasLtcsPreventionService" title="介護保険サービス：介護予防支援">
        <z-form-card-item-set :icon="$icons.ltcs">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-prevention-service-code
            vid="ltcsPreventionService.code"
            :rules="rules.ltcsPreventionService.code"
          >
            <z-text-field
              v-model.trim="form.ltcsPreventionService.code"
              label="事業所番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
          <template v-if="isInternal">
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-prevention-service-opened-on
              vid="ltcsPreventionService.openedOn"
              :rules="rules.ltcsPreventionService.openedOn"
            >
              <z-date-field
                v-model="form.ltcsPreventionService.openedOn"
                label="開設日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-ltcs-prevention-service-designation-expired-on
              vid="ltcsPreventionService.designationExpiredOn"
              :rules="rules.ltcsPreventionService.designationExpiredOn"
            >
              <z-date-field
                v-model="form.ltcsPreventionService.designationExpiredOn"
                label="指定更新期日 *"
                :error-messages="errors"
              />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs, watch } from '@nuxtjs/composition-api'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Permission } from '@zinger/enums/lib/permission'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose } from '@zinger/enums/lib/purpose'
import { assign, noop, pick } from '@zinger/helpers'
import { mask } from 'vue-the-mask'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useDwsAreaGrades } from '~/composables/use-dws-area-grades'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useLtcsAreaGrades } from '~/composables/use-ltcs-area-grades'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOfficeStatusIcon } from '~/composables/use-office-status-icon'
import { usePostcodeResolver } from '~/composables/use-postcode-resolver'
import { autoKana } from '~/directives/auto-kana'
import { phoneNumber } from '~/directives/phone-number'
import { OfficesApi } from '~/services/api/offices-api'
import { asciiAlphaNum, email, fax, katakana, postcode, required, tel } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Form = OfficesApi.Form

type Props = FormProps<OfficesApi.Form> & Readonly<{
  buttonText: string
  permission: Permission
}>

export default defineComponent<Props>({
  name: 'ZOfficeForm',
  directives: {
    autoKana,
    mask,
    phoneNumber
  },
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    permission: { type: String, required: true }
  },
  setup (props, context) {
    const propRefs = toRefs(props)
    const { form, observer, submit } = useFormBindings<Form>(props, context, {
      init: form => ({
        purpose: form.purpose,
        name: form.name,
        abbr: form.abbr,
        phoneticName: form.phoneticName,
        postcode: form.postcode,
        prefecture: form.prefecture,
        city: form.city,
        street: form.street,
        apartment: form.apartment,
        tel: form.tel,
        fax: form.fax,
        email: form.email,
        officeGroupId: form.officeGroupId,
        qualifications: form.qualifications ?? [],
        dwsGenericService: form.dwsGenericService ?? {},
        dwsCommAccompanyService: form.dwsCommAccompanyService ?? {},
        ltcsHomeVisitLongTermCareService: form.ltcsHomeVisitLongTermCareService ?? {},
        ltcsCareManagementService: form.ltcsCareManagementService ?? {},
        ltcsCompHomeVisitingService: form.ltcsCompHomeVisitingService ?? {},
        ltcsPreventionService: form.ltcsPreventionService ?? {},
        status: form.status
      }),
      processOutput: x => ({
        ...x,
        // 事業所区分が「自社」の場合「法人名」および「法人名：フリガナ」は登録しない
        corporationName: x.purpose === Purpose.internal ? '' : (x.corporationName ?? ''),
        phoneticCorporationName: x.purpose === Purpose.internal ? '' : (x.phoneticCorporationName ?? '')
      })
    })
    const { dwsAreaGradeOptions, isLoadingDwsAreaGrades } = useDwsAreaGrades()
    const { ltcsAreaGradeOptions, isLoadingLtcsAreaGrades } = useLtcsAreaGrades()
    const useSelectOptions = () => ({
      prefectureOptions: enumerableOptions(Prefecture),
      purposeOptions: enumerableOptions(Purpose).filter(x => x.value !== Purpose.unknown),
      qualificationOptions: enumerableOptions(OfficeQualification),
      statusOptions: enumerableOptions(OfficeStatus)
    })
    const qualifications = computed(() => form.qualifications ?? [])
    const flags = {
      hasDwsGenericService: computed(() => qualifications.value.some(x => {
        const xs: OfficeQualification[] = [
          OfficeQualification.dwsHomeHelpService,
          OfficeQualification.dwsVisitingCareForPwsd
        ]
        return xs.includes(x)
      })),
      hasDwsCommAccompanyService: computed(() => qualifications.value.includes(OfficeQualification.dwsCommAccompany)),
      hasDwsOthersService: computed(() => qualifications.value.includes(OfficeQualification.dwsOthers)),
      hasLtcsHomeVisitLongTermCareService: computed(() =>
        qualifications.value.includes(OfficeQualification.ltcsHomeVisitLongTermCare)
      ),
      hasLtcsCareManagementService: computed(() =>
        qualifications.value.includes(OfficeQualification.ltcsCareManagement)
      ),
      hasLtcsCompHomeVisitingService: computed(() =>
        qualifications.value.includes(OfficeQualification.ltcsCompHomeVisiting)
      ),
      hasLtcsPreventionService: computed(() =>
        qualifications.value.includes(OfficeQualification.ltcsPrevention)
      ),
      isInternal: computed(() => form.purpose === Purpose.internal),
      isExternal: computed(() => form.purpose === Purpose.external)
    }
    const watchFlags = <K extends keyof typeof flags> (property: K, falsy: () => void, truthy = noop) => {
      watch(() => flags[property].value, value => { if (!value) { falsy() } else { truthy() } })
    }
    // 自社以外の場合は事業所グループ、メールアドレスをクリア
    watchFlags('isInternal', () => assign(form, {
      officeGroupId: undefined,
      email: undefined
    }))
    // 他社以外の場合は法人名、法人名：フリガナをクリア、他社の場合は各サービスの事業所番号以外をクリア
    watchFlags(
      'isExternal',
      () => assign(form, {
        corporationName: undefined,
        phoneticCorporationName: undefined
      }),
      () => {
        const xs = [
          'dwsGenericService',
          'dwsCommAccompanyService',
          'ltcsHomeVisitLongTermCareService',
          'ltcsCareManagementService',
          'ltcsCompHomeVisitingService',
          'ltcsPreventionService'
        ] as const
        xs.forEach(x => {
          form[x] = {
            code: form[x]?.code
          }
        })
      }
    )
    // 障害福祉サービスを含まない場合
    watchFlags('hasDwsGenericService', () => {
      form.dwsGenericService = flags.hasDwsOthersService.value ? { code: form.dwsGenericService?.code } : {}
    })
    // 障害福祉サービス：移動支援（地域生活支援事業）を含まない場合
    watchFlags('hasDwsCommAccompanyService', () => { form.dwsCommAccompanyService = {} })
    // その他障害福祉サービスを含まない場合
    watchFlags('hasDwsOthersService', () => { if (!flags.hasDwsGenericService.value) { form.dwsGenericService = {} } })
    // 介護保険サービス：訪問介護を含まない場合
    watchFlags('hasLtcsHomeVisitLongTermCareService', () => { form.ltcsHomeVisitLongTermCareService = {} })
    // 介護保険サービス：居宅介護支援を含まない場合
    watchFlags('hasLtcsCareManagementService', () => { form.ltcsCareManagementService = {} })
    // 介護保険サービス：訪問型サービス（総合事業）を含まない場合
    watchFlags('hasLtcsCompHomeVisitingService', () => { form.ltcsCompHomeVisitingService = {} })
    // 介護保険サービス：介護予防支援を含まない場合
    watchFlags('hasLtcsPreventionService', () => { form.ltcsPreventionService = {} })
    const rules = (() => {
      const serviceCommonRules = {
        code: { required, asciiAlphaNum, length: 10 },
        openedOn: { required },
        designationExpiredOn: { required }
      }
      const commonRules = {
        purpose: { required },
        name: { required, max: 200 },
        phoneticName: { required, katakana, max: 200 },
        corporationName: { max: 200 },
        phoneticCorporationName: { katakana, max: 200 },
        status: { required },
        apartment: { max: process.env.apartmentMaxLength },
        fax: { fax },
        dwsGenericService: {
          ...serviceCommonRules,
          dwsAreaGradeId: { required }
        },
        dwsCommAccompanyService: {
          ...serviceCommonRules
        },
        ltcsHomeVisitLongTermCareService: {
          ...serviceCommonRules,
          ltcsAreaGradeId: { required }
        },
        ltcsCareManagementService: {
          ...serviceCommonRules,
          ltcsAreaGradeId: { required }
        },
        ltcsCompHomeVisitingService: {
          ...serviceCommonRules
        },
        ltcsPreventionService: {
          ...serviceCommonRules
        }
      }
      const internalRules = {
        ...commonRules,
        abbr: { required, max: 200 },
        officeGroupId: { required },
        postcode: { required, postcode },
        prefecture: { required },
        city: { required, max: process.env.cityMaxLength },
        street: { required, max: process.env.streetMaxLength },
        tel: { required, tel },
        email: { email, max: process.env.emailMaxLength }
      }
      const externalRules = {
        ...commonRules,
        postcode: { postcode },
        city: { max: process.env.cityMaxLength },
        street: { max: process.env.streetMaxLength },
        tel: { tel }
      }
      return computed(() => validationRules(flags.isInternal.value ? internalRules : externalRules))
    })()
    return {
      ...useOfficeGroups({ permission: propRefs.permission }),
      ...useOfficeStatusIcon(computed(() => pick(form, ['status']))),
      ...usePostcodeResolver(form),
      ...useSelectOptions(),
      ...flags,
      dwsAreaGradeOptions,
      form,
      isLoadingDwsAreaGrades,
      isLoadingLtcsAreaGrades,
      ltcsAreaGradeOptions,
      observer,
      rules,
      submit
    }
  }
})
</script>
