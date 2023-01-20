<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-z-dws-certification-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="児童情報">
        <z-form-card-item-set :icon="$icons.user">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-family-name
                vid="childFamilyName"
                :rules="rules.child.familyName"
              >
                <z-text-field
                  v-model.trim="form.child.name.familyName"
                  data-family-name-input
                  label="姓"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-given-name vid="childGivenName" :rules="rules.child.givenName">
                <z-text-field
                  v-model.trim="form.child.name.givenName"
                  data-given-name-input
                  label="名"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-phonetic-family-name
                vid="childPhoneticFamilyName"
                :rules="rules.child.phoneticFamilyName"
              >
                <z-text-field
                  v-model.trim="form.child.name.phoneticFamilyName"
                  v-auto-kana
                  data-phonetic-family-name-input
                  label="フリガナ：姓"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-phonetic-given-name
                vid="childPhoneticGivenName"
                :rules="rules.child.phoneticGivenName"
              >
                <z-text-field
                  v-model.trim="form.child.name.phoneticGivenName"
                  v-auto-kana
                  data-phonetic-given-name-input
                  label="フリガナ：名"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.birthday">
          <z-form-card-item v-slot="{ errors }" data-birthday vid="childBirthday" :rules="rules.child.birthday">
            <z-date-field
              v-model="form.child.birthday"
              birthday
              label="生年月日"
              :error-messages="errors"
              :max="$datetime.now.toISODate()"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.date">
          <z-form-card-item v-slot="{ errors }" data-effectivated-on vid="effectivatedOn" :rules="rules.effectivatedOn">
            <z-date-field v-model="form.effectivatedOn" label="適用日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="statusIcon">
          <z-form-card-item v-slot="{ errors }" data-status vid="status" :rules="rules.status">
            <z-select
              v-model="form.status"
              label="認定区分 *"
              :error-messages="errors"
              :items="dwsCertificationStatuses"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dwsNumber">
          <z-form-card-item v-slot="{ errors }" data-dws-number vid="dwsNumber" :rules="rules.dwsNumber">
            <z-text-field v-model.trim="form.dwsNumber" label="受給者証番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set label="障害種別 *" :icon="$icons.category">
          <z-form-card-item v-slot="{ errors }" class="ml-0 d-flex" vid="dwsTypes_input" :rules="rules.dwsTypes">
            <v-checkbox
              v-for="x in dwsTypes"
              :key="x.value"
              v-model="form.dwsTypes"
              class="flex-grow-1"
              dense
              :error="!!errors[0]"
              :label="x.text"
              :value="x.value"
            />
          </z-form-card-item>
          <z-validate-error-messages
            v-slot="{ errors }"
            class="mb-2 mt-n2"
            data-dws-types
            vid="dwsTypes"
            :rules="rules.dwsTypes"
            :value="form.dwsTypes"
          >
            <z-error-container v-if="!!errors[0]">
              <div class="error--text v-messages">{{ errors[0] }}</div>
            </z-error-container>
          </z-validate-error-messages>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.issuedOn">
          <z-form-card-item v-slot="{ errors }" data-issued-on vid="issuedOn" :rules="rules.issuedOn">
            <z-date-field v-model="form.issuedOn" label="交付年月日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.city">
          <z-form-card-item v-slot="{ errors }" data-city-name vid="cityName" :rules="rules.cityName">
            <z-text-field v-model.trim="form.cityName" label="市町村名 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-city-code vid="cityCode" :rules="rules.cityCode">
            <z-text-field v-model.trim="form.cityCode" label="市町村番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="介護給付費の支給決定内容">
        <z-form-card-item-set :icon="$icons.level">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-dws-level vid="dwsLevel" :rules="rules.dwsLevel">
                <z-select v-model="form.dwsLevel" label="障害支援区分 *" :error-messages="errors" :items="dwsLevels" />
              </z-form-card-item>
            </v-col>
            <v-col v-show="isDwsLevel6" class="pl-sm-4" cols="12" sm="6">
              <v-checkbox
                v-model="form.isSubjectOfComprehensiveSupport"
                color="grey darken-2"
                label="重度障害者等包括支援対象"
              />
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item v-slot="{ errors }" data-activated-on vid="activatedOn" :rules="rules.activatedOn">
              <z-date-field v-model="form.activatedOn" label="認定有効期間 *" :error-messages="errors" />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item v-slot="{ errors }" data-deactivated-on vid="deactivatedOn" :rules="rules.deactivatedOn">
              <z-date-field v-model="form.deactivatedOn" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
      </z-form-card>
      <transition-group name="card-list" tag="div">
        <z-form-card v-for="(grant, index) in form.grants" :key="grantKeys[index]" class="mt-2">
          <template #header>
            <v-spacer />
            <v-btn color="secondary" data-delete-grant icon text @click="deleteGrant(index)">
              <v-icon>{{ $icons.close }}</v-icon>
            </v-btn>
          </template>
          <template #default>
            <z-form-card-item-set :icon="$icons.category">
              <z-form-card-item
                v-slot="{ errors }"
                :data-grant-dws-certification-service-type="index"
                :vid="`grants.${index}.dwsCertificationServiceType`"
                :rules="serviceTypeRule(
                  index,
                  grant.dwsCertificationServiceType,
                  form.effectivatedOn,
                  form.dwsLevel,
                  form.isSubjectOfComprehensiveSupport,
                  form.grants,
                  grant.activatedOn,
                  grant.deactivatedOn
                ).dwsCertificationServiceType"
              >
                <z-select
                  v-model="grant.dwsCertificationServiceType"
                  label="サービス種別 *"
                  :error-messages="errors"
                  :items="dwsCertificationServiceTypes"
                />
              </z-form-card-item>
            </z-form-card-item-set>
            <z-form-card-item-set :icon="$icons.text">
              <z-form-card-item
                v-slot="{ errors }"
                :data-grant-granted-amount="index"
                :rules="rules.grants.grantedAmount"
                :vid="`grants.${index}.grantedAmount`"
              >
                <z-textarea v-model.trim="grant.grantedAmount" label="支給量等 *" :error-messages="errors" />
              </z-form-card-item>
            </z-form-card-item-set>
            <z-form-card-item-set :icon="$icons.dateRange">
              <z-flex>
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-grant-activated-on="index"
                  :rules="rules.grants.activatedOn"
                  :vid="`grants.${index}.activatedOn`"
                >
                  <z-date-field v-model="grant.activatedOn" label="支給決定期間 *" :error-messages="errors" />
                </z-form-card-item>
                <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-grant-deactivated-on="index"
                  :rules="rules.grants.deactivatedOn"
                  :vid="`grants.${index}.deactivatedOn`"
                >
                  <z-date-field v-model="grant.deactivatedOn" :error-messages="errors" />
                </z-form-card-item>
              </z-flex>
            </z-form-card-item-set>
          </template>
        </z-form-card>
      </transition-group>
      <z-validate-error-messages
        v-slot="{ errors }"
        v-model="form.grants.length"
        class="mt-3"
        data-grants
        vid="grants"
        :rules="rules.grants.whole"
      >
        <v-alert class="mb-3" dense type="error" :icon="false">
          {{ errors[0] }}
        </v-alert>
      </z-validate-error-messages>
      <v-card>
        <v-card-text class="pa-2 text-center">
          <v-btn block color="primary" data-add-grant text @click="addGrant">
            <v-icon left>{{ $icons.add }}</v-icon>
            <span>介護給付費の支給決定内容を追加</span>
          </v-btn>
        </v-card-text>
      </v-card>
      <z-form-card title="利用者負担に関する事項">
        <z-form-card-item-set :icon="$icons.copayLimit">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-copay-limit vid="copayLimit" :rules="rules.copayLimit">
                <z-text-field
                  ref="copayLimitInput"
                  v-model.trim="form.copayLimit"
                  class="z-text-field--numeric"
                  label="負担上限月額 *"
                  suffix="円/月"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item
              v-slot="{ errors }"
              data-copay-activated-on
              vid="copayActivatedOn"
              :rules="rules.copayActivatedOn"
            >
              <z-date-field v-model="form.copayActivatedOn" label="利用者負担適用期間 *" :error-messages="errors" />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item
              v-slot="{ errors }"
              data-copay-deactivated-on
              vid="copayDeactivatedOn"
              :rules="rules.copayDeactivatedOn"
            >
              <z-date-field v-model="form.copayDeactivatedOn" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.level">
          <z-form-card-item
            v-slot="{ errors }"
            data-copay-coordination-type
            vid="copayCoordinationType"
            :rules="rules.copayCoordinationTypes"
          >
            <z-select
              v-model="form.copayCoordination.copayCoordinationType"
              label="上限管理区分 *"
              :error-messages="errors"
              :items="copayCoordinationTypes"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="isCopayCoordinationOfficeRequired" :icon="$icons.office">
          <z-form-card-item
            v-slot="{ errors }"
            data-copay-office-id
            vid="copayOfficeId"
            :rules="rules.copayCoordinationOfficeId"
          >
            <z-keyword-filter-autocomplete
              v-model="form.copayCoordination.officeId"
              label="上限額管理事業所名"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-subheader class="mt-4">訪問系サービス事業者記入欄</z-subheader>
      <v-row dense>
        <v-col v-for="(agreement, index) in form.agreements" :key="agreementKeys[index]" cols="12" sm="6">
          <z-form-card class="ma-0">
            <template #header>
              <v-spacer />
              <v-btn color="secondary" data-delete-agreement icon text @click="deleteAgreement(index)">
                <v-icon>{{ $icons.close }}</v-icon>
              </v-btn>
            </template>
            <template #default>
              <z-form-card-item-set :icon="$icons.number">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-index-number="index"
                  :vid="`agreements.${index}.indexNumber`"
                  :rules="rules.indexNumber"
                >
                  <z-text-field v-model.trim="agreement.indexNumber" label="番号 *" :error-messages="errors" />
                </z-form-card-item>
              </z-form-card-item-set>
              <z-form-card-item-set :icon="$icons.office">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-office-id="index"
                  :vid="`agreements.${index}.officeId`"
                  :rules="rules.officeId"
                >
                  <z-keyword-filter-autocomplete
                    v-model="agreement.officeId"
                    label="事業所 *"
                    :error-messages="errors"
                    :items="internalOffices.officeOptions.value"
                    :loading="internalOffices.isLoadingOffices.value"
                  />
                </z-form-card-item>
              </z-form-card-item-set>
              <z-form-card-item-set :icon="$icons.category">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-dws-certification-agreement-type="index"
                  :vid="`agreements.${index}.dwsCertificationAgreementType`"
                  :rules="agreementTypeRule(index).dwsCertificationAgreementType"
                >
                  <z-select
                    v-model="agreement.dwsCertificationAgreementType"
                    label="サービス内容 *"
                    :error-messages="errors"
                    :items="dwsCertificationAgreementTypes"
                  />
                </z-form-card-item>
              </z-form-card-item-set>
              <z-form-card-item-set :icon="$icons.timeAmount">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-payment-amount="index"
                  :vid="`agreements.${index}.paymentAmount`"
                  :rules="rules.paymentAmount"
                >
                  <z-hour-and-minute-field
                    v-model="agreement.paymentAmount"
                    label="契約支給量 *"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </z-form-card-item-set>
              <z-form-card-item-set :icon="$icons.dateStart">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-agreed-on="index"
                  :vid="`agreements.${index}.agreedOn`"
                  :rules="rules.agreedOn"
                >
                  <z-date-field v-model="agreement.agreedOn" label="契約日 *" :error-messages="errors" />
                </z-form-card-item>
              </z-form-card-item-set>
              <z-form-card-item-set :icon="$icons.dateEnd">
                <z-form-card-item
                  v-slot="{ errors }"
                  :data-agreement-expired-on="index"
                  :vid="`agreements.${index}.expiredOn`"
                  :rules="rules.expiredOn"
                >
                  <z-date-field
                    v-model="agreement.expiredOn"
                    label="当該契約支給量によるサービス提供終了日"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </z-form-card-item-set>
            </template>
          </z-form-card>
        </v-col>
        <z-validate-error-messages
          v-slot="{ errors }"
          v-model="form.agreements.length"
          class="ml-1"
          data-agreements
          vid="agreements"
          :rules="rules.agreements"
        >
          <v-alert class="mb-2" dense type="error" :icon="false">
            {{ errors[0] }}
          </v-alert>
        </z-validate-error-messages>
        <v-col class="align-stretch d-flex justify-center" cols="12" sm="6">
          <v-card class="align-stretch d-flex" style="width: 100%">
            <v-card-text class="align-center d-flex justify-center pa-2">
              <v-btn block :class="$style.addAgreement" color="primary" data-add-agreement text @click="addAgreement">
                <v-icon left>{{ $icons.add }}</v-icon>
                <span>訪問系サービス事業者記入欄を追加</span>
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs, watch } from '@nuxtjs/composition-api'
import { CopayCoordinationType } from '@zinger/enums/lib/copay-coordination-type'
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { isEmpty, nonEmpty, pick } from '@zinger/helpers'
import { Interval } from 'luxon'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useDwsCertificationStatusIcon } from '~/composables/use-dws-certification-status-icon'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useRateOptions } from '~/composables/use-rate-options'
import { autoKana } from '~/directives/auto-kana'
import { DateLike } from '~/models/date'
import { TimeDuration } from '~/models/time-duration'
import { User } from '~/models/user'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { katakana, numeric, required, validTimeDuration } from '~/support/validation/rules'
import { CustomRuleParams } from '~/support/validation/rules/custom'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DeepPartial<DwsCertificationsApi.Form>> & Readonly<{
  buttonText: string
  permission: Permission
  user: User
}>

export default defineComponent<Props>({
  name: 'ZDwsCertificationForm',
  directives: {
    autoKana
  },
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    permission: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup (props: Props, context) {
    const propRefs = toRefs(props)
    const { $datetime } = usePlugins()
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        grants: form.grants ?? [{}],
        agreements: form.agreements ?? [{}],
        child: form.child ?? { name: {} },
        copayCoordination: form.copayCoordination ?? {},
        dwsTypes: form.dwsTypes ?? [],
        dwsLevel: form.dwsLevel,
        isSubjectOfComprehensiveSupport: form.isSubjectOfComprehensiveSupport ?? false
      }),
      processOutput: output => ({
        ...output,
        agreements: output.agreements?.map(x => ({
          ...x,
          paymentAmount: TimeDuration.isTimeDuration(x.paymentAmount) ? x.paymentAmount.totalMinutes : x.paymentAmount
        })),
        isSubjectOfComprehensiveSupport: output.dwsLevel === DwsLevel.level6 && output.isSubjectOfComprehensiveSupport
      })
    })
    const agreementsWrapper = createArrayWrapper(form.agreements)
    const useAgreements = () => ({
      agreementKeys: agreementsWrapper.keys,
      addAgreement: () => agreementsWrapper.push({}),
      deleteAgreement: (index: number) => agreementsWrapper.remove(index)
    })
    const grantsWrapper = createArrayWrapper(form.grants)
    const useGrants = () => ({
      grantKeys: grantsWrapper.keys,
      addGrant: () => grantsWrapper.push({}),
      deleteGrant: (index: number) => grantsWrapper.remove(index)
    })
    const isCopayCoordinationOfficeRequired = computed(() => (
      form.copayCoordination?.copayCoordinationType === CopayCoordinationType.internal ||
      form.copayCoordination?.copayCoordinationType === CopayCoordinationType.external
    ))
    const isDwsLevel6 = computed(() => form.dwsLevel === DwsLevel.level6)
    watch(isDwsLevel6, value => {
      if (!value && form.isSubjectOfComprehensiveSupport) {
        form.isSubjectOfComprehensiveSupport = false
      }
    })
    const agreementTypeRule = (index: number) => {
      const custom: CustomRuleParams = {
        message: '障害支援区分とサービス内容の組み合わせが正しくありません。間違いがないかご確認ください。',
        validate: value => {
          if (isEmpty(form.dwsLevel) || isEmpty(form.effectivatedOn)) {
            return true
          }

          // 「該当契約支給量によるサービス提供終了日」が受給者証の「適用日」より前である場合はバリデーションしない。
          if (
            nonEmpty(form.agreements?.[index].expiredOn) &&
            $datetime.parse(form.effectivatedOn as string) >
            $datetime.parse(form.agreements![index].expiredOn as string)
          ) {
            return true
          }

          /**
           * 「サービス内容」と「障害支援区分」「重度障害者等包括支援対象」の正しい組み合わせであるかどうかを判定する。
           * @link https://eustylelab-engineers.growi.cloud/62175273d1b1cdd218e4aa65
           */
          switch (value) {
            case DwsCertificationAgreementType.visitingCareForPwsd1:
              return form.dwsLevel === DwsLevel.level6 && form.isSubjectOfComprehensiveSupport === true
            case DwsCertificationAgreementType.visitingCareForPwsd2:
              return form.dwsLevel === DwsLevel.level6 && form.isSubjectOfComprehensiveSupport === false
            case DwsCertificationAgreementType.visitingCareForPwsd3:
            case DwsCertificationAgreementType.outingSupportForPwsd:
              return ([DwsLevel.level3, DwsLevel.level4, DwsLevel.level5, DwsLevel.level6] as DwsLevel[])
                .includes(form.dwsLevel)
            default:
              return true
          }
        }
      }
      return validationRules({
        dwsCertificationAgreementType: { custom, required, numeric }
      })
    }
    const serviceTypeRule = (
      index: number,
      value: DwsCertificationServiceType,
      effectivatedOn: DateLike,
      dwsLevel: DwsLevel,
      isSubjectOfComprehensiveSupport: boolean,
      grants: DwsCertificationsApi.Form['grants'],
      activatedOn: DateLike,
      deactivatedOn: DateLike
    ) => {
      // 1つのフィールドに2つのカスタムルールが設定できないので無理矢理なんとかする.
      const isGrantDuplicated = (() => {
        if (typeof grants === 'undefined') {
          return false
        } else if (
          value !== DwsCertificationServiceType.visitingCareForPwsd1 &&
          value !== DwsCertificationServiceType.visitingCareForPwsd2 &&
          value !== DwsCertificationServiceType.visitingCareForPwsd3
        ) {
          return false
        } else if (isEmpty(activatedOn) || isEmpty(deactivatedOn)) {
          return false
        }
        const interval = Interval.fromISO(`${activatedOn}/${deactivatedOn}`)
        return grants.some((x, i: number) => i !== index &&
          x.activatedOn !== undefined &&
          x.deactivatedOn !== undefined &&
          (
            x.dwsCertificationServiceType === DwsCertificationServiceType.visitingCareForPwsd1 ||
            x.dwsCertificationServiceType === DwsCertificationServiceType.visitingCareForPwsd2 ||
            x.dwsCertificationServiceType === DwsCertificationServiceType.visitingCareForPwsd3
          ) &&
          Interval.fromISO(`${x.activatedOn}/${x.deactivatedOn}`).overlaps(interval)
        )
      })()
      const isContradicted = (() => {
        if (isEmpty(effectivatedOn) || isEmpty(deactivatedOn)) {
          return false
        }
        if ($datetime.parse(effectivatedOn) > $datetime.parse(deactivatedOn)) {
          return false
        }
        switch (value) {
          case DwsCertificationServiceType.visitingCareForPwsd1:
            return dwsLevel !== DwsLevel.level6 || !isSubjectOfComprehensiveSupport
          case DwsCertificationServiceType.visitingCareForPwsd2:
            return dwsLevel !== DwsLevel.level6
          case DwsCertificationServiceType.visitingCareForPwsd3:
            return (() => {
              const xs: DwsLevel[] = [DwsLevel.level3, DwsLevel.level4, DwsLevel.level5, DwsLevel.level6]
              return !xs.includes(dwsLevel)
            })()
          default:
            return false
        }
      })()
      const custom: CustomRuleParams = {
        message: isGrantDuplicated
          ? '支給決定期間が重複する重度訪問介護の支給決定内容が他に存在します。'
          : '障害支援区分と矛盾するサービス種別です。間違いがないかご確認ください。',
        validate: () => !isGrantDuplicated && !isContradicted
      }
      return validationRules({
        dwsCertificationServiceType: { custom, required }
      })
    }
    const rules = computed(() => {
      const requiredChild = Object.values({ ...form.child?.name, birthday: form.child?.birthday }).join('') !== ''
      const requiredCopayCoordinationOffice = isCopayCoordinationOfficeRequired.value
      return validationRules({
        agreements: { nonItemsZero: { itemName: '訪問系サービス事業者記入欄' } },
        child: {
          familyName: { required: requiredChild, max: process.env.familyNameMaxLength },
          givenName: { required: requiredChild, max: process.env.givenNameMaxLength },
          phoneticFamilyName: { required: requiredChild, katakana, max: process.env.phoneticFamilyNameMaxLength },
          phoneticGivenName: { required: requiredChild, katakana, max: process.env.phoneticGivenNameMaxLength },
          birthday: { required: requiredChild }
        },
        effectivatedOn: { required },
        status: { required },
        dwsNumber: { required, digits: 10 },
        dwsTypes: { required },
        issuedOn: { required },
        cityName: { required, max: process.env.cityMaxLength },
        cityCode: { required, digits: 6 },
        dwsLevel: { required },
        activatedOn: { required },
        deactivatedOn: { required },
        grants: {
          whole: { nonItemsZero: { itemName: '介護給付費の支給内容' } },
          grantedAmount: { required },
          activatedOn: { required },
          deactivatedOn: { required }
        },
        copayLimit: { required, numeric },
        copayActivatedOn: { required },
        copayDeactivatedOn: { required },
        copayCoordinationTypes: { required, numeric },
        copayCoordinationOfficeId: { required: requiredCopayCoordinationOffice },
        indexNumber: { required, numeric, between: { min: 1, max: 99 } },
        officeId: { required, numeric },
        paymentAmount: { required, validTimeDuration },
        agreedOn: { required }
      })
    })
    const permission = propRefs.permission
    const purpose = computed(() => {
      const type = form.copayCoordination?.copayCoordinationType
      switch (type) {
        case CopayCoordinationType.internal:
          return Purpose.internal
        case CopayCoordinationType.external:
          return Purpose.external
        default:
          return undefined
      }
    })
    return {
      ...useAgreements(),
      ...useDwsCertificationStatusIcon(computed(() => pick(form, ['status']))),
      ...useGrants(),
      ...useOffices({
        purpose,
        qualifications: [
          OfficeQualification.dwsHomeHelpService,
          OfficeQualification.dwsVisitingCareForPwsd,
          OfficeQualification.dwsCommAccompany,
          OfficeQualification.dwsOthers
        ]
      }),
      ...useRateOptions(),
      agreementTypeRule,
      serviceTypeRule,
      form,
      internalOffices: useOffices({ permission, internal: true }),
      isCopayCoordinationOfficeRequired,
      isDwsLevel6,
      copayCoordinationTypes: enumerableOptions(CopayCoordinationType),
      dwsCertificationAgreementTypes: enumerableOptions(DwsCertificationAgreementType),
      dwsCertificationServiceTypes: enumerableOptions(DwsCertificationServiceType),
      dwsCertificationStatuses: enumerableOptions(DwsCertificationStatus),
      dwsLevels: enumerableOptions(DwsLevel),
      dwsTypes: enumerableOptions(DwsType),
      observer,
      rules,
      submit
    }
  }
})
</script>

<style lang="scss" module>
.addAgreement {
  height: 100% !important;
  min-height: 40px;
}
</style>
