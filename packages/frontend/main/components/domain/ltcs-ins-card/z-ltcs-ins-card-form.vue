<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.date">
          <z-form-card-item v-slot="{ errors }" data-effectivated-on vid="effectivatedOn" :rules="rules.effectivatedOn">
            <z-date-field v-model="form.effectivatedOn" label="適用日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.ltcsInsNumber">
          <z-form-card-item v-slot="{ errors }" data-ins-number vid="insNumber" :rules="rules.insNumber">
            <z-text-field v-model.trim="form.insNumber" label="被保険者証番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="statusIcon">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-ins-card-status
            vid="status"
            :rules="rules.status"
          >
            <z-select
              v-model="form.status"
              label="認定区分 *"
              :error-messages="errors"
              :items="ltcsInsCardStatuses"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.issuedOn">
          <z-form-card-item v-slot="{ errors }" data-issued-on vid="issuedOn" :rules="rules.issuedOn">
            <z-date-field v-model="form.issuedOn" label="交付年月日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.city">
          <z-form-card-item v-slot="{ errors }" data-insurer-name vid="insurerName" :rules="rules.insurerName">
            <z-text-field v-model.trim="form.insurerName" label="保険者の名称 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-insurer-number vid="insurerNumber" :rules="rules.insurerNumber">
            <z-text-field v-model.trim="form.insurerNumber" label="保険者番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.level">
          <z-form-card-item v-slot="{ errors }" data-ltcs-level vid="ltcsLevel" :rules="rules.ltcsLevel">
            <z-select
              v-model="form.ltcsLevel"
              label="要介護状態区分等 *"
              :error-messages="errors"
              :items="ltcsLevels"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.issuedOn">
          <z-form-card-item v-slot="{ errors }" data-certificated-on vid="certificatedOn" :rules="rules.certificatedOn">
            <z-date-field v-model="form.certificatedOn" label="認定年月日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item v-slot="{ errors }" data-activated-on vid="activatedOn" :rules="rules.activatedOn">
              <z-date-field v-model="form.activatedOn" label="認定の有効期間 *" :error-messages="errors" />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item v-slot="{ errors }" data-deactivated-on vid="deactivatedOn" :rules="rules.deactivatedOn">
              <z-date-field v-model="form.deactivatedOn" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="居宅サービス計画">
        <z-form-card-item-set :icon="$icons.carePlanAuthor">
          <z-form-card-item
            v-if="isCareLevel"
            v-slot="{ errors }"
            data-care-plan-author-type
            vid="carePlanAuthorType"
            :rules="rules.carePlanAuthorType"
          >
            <v-radio-group v-model="form.carePlanAuthorType" :error-messages="errors">
              <template #label>
                <div class="text-caption">居宅サービス計画作成区分 *</div>
              </template>
              <v-radio v-for="x in carePlanAuthorTypes" :key="x.value" d :label="x.text" :value="x.value" />
            </v-radio-group>
          </z-form-card-item>
          <div v-else :class="$style.item">
            <div :class="$style.content">
              <div class="text-caption" :class="$style.label">
                居宅サービス計画作成区
              </div>
              <div class="text-body-2 text--primary" :class="$style.value">
                介護予防支援事業所・地域包括支援センター作成
              </div>
            </div>
          </div>
          <template v-if="!isCareLevel">
            <z-form-card-item
              v-slot="{ errors }"
              data-community-general-support-center-id
              vid="communityGeneralSupportCenterId"
              :rules="rules.communityGeneralSupportCenterId"
            >
              <z-keyword-filter-autocomplete
                v-model="form.communityGeneralSupportCenterId"
                label="地域包括支援センター *"
                :error-messages="errors"
                :items="communityGeneralSupportCenterOptions"
              />
            </z-form-card-item>
          </template>
          <template v-if="!isSelfCreated">
            <z-form-card-item
              v-slot="{ errors }"
              data-care-plan-author-office-id
              vid="carePlanAuthorOfficeId"
              :rules="rules.carePlanAuthorOfficeId"
            >
              <z-keyword-filter-autocomplete
                v-model="form.carePlanAuthorOfficeId"
                label="居宅介護支援事業所 *"
                :error-messages="errors"
                :items="carePlanAuthorOfficeOptions"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-care-manager-name
              vid="careManagerName"
              :rules="rules.careManagerName"
            >
              <z-text-field v-model.trim="form.careManagerName" label="担当者の名称 *" :error-messages="errors" />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-subheader class="mt-6">種類支給限度基準額</z-subheader>
      <transition-group name="card-list" tag="div">
        <z-form-card v-for="(quota, index) in form.maxBenefitQuotas" :key="quotaKeys[index]" class="mt-2">
          <template #header>
            <v-spacer />
            <v-btn color="secondary" data-delete-quota icon text @click="deleteQuota(index)">
              <v-icon>{{ $icons.close }}</v-icon>
            </v-btn>
          </template>
          <template #default>
            <z-form-card-item-set :icon="$icons.category">
              <z-flex>
                <z-form-card-item
                  v-slot="{ errors }"
                  data-ltcs-ins-card-service-type
                  vid="ltcsInsCardServiceType"
                  :rules="rules.ltcsInsCardServiceType"
                >
                  <z-select
                    v-model="quota.ltcsInsCardServiceType"
                    label="サービスの種類 *"
                    :error-messages="errors"
                    :items="ltcsInsCardServiceTypes"
                  />
                </z-form-card-item>
                <z-form-card-item
                  v-slot="{ errors }"
                  data-max-benefit-quota
                  vid="maxBenefitQuota"
                  :rules="rules.maxBenefitQuota"
                >
                  <z-text-field
                    v-model.trim="quota.maxBenefitQuota"
                    class="z-text-field--numeric"
                    label="種類支給限度基準額 *"
                    suffix="単位/月"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </z-flex>
            </z-form-card-item-set>
          </template>
        </z-form-card>
      </transition-group>
      <v-card class="mt-2">
        <v-card-text class="pa-2 text-center">
          <v-btn block color="primary" data-add-quota text @click="addQuota">
            <v-icon left>{{ $icons.add }}</v-icon>
            <span>種類支給限度基準額を追加</span>
          </v-btn>
        </v-card-text>
      </v-card>
      <z-form-card title="介護保険負担割合証情報">
        <z-form-card-item-set :icon="$icons.ratio">
          <z-form-card-item v-slot="{ errors }" data-copay-rate vid="copayRate" :rules="rules.copayRate">
            <z-select
              v-model="form.copayRate"
              label="利用者負担の割合 *"
              :error-messages="errors"
              :items="rateOptions"
            />
          </z-form-card-item>
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
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, watch } from '@nuxtjs/composition-api'
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { pick } from '@zinger/helpers'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useLtcsInsCardStatusIcon } from '~/composables/use-ltcs-ins-card-status-icon'
import { useOffices } from '~/composables/use-offices'
import { useRateOptions } from '~/composables/use-rate-options'
import { User } from '~/models/user'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<LtcsInsCardsApi.Form> & Readonly<{
  buttonText: string
  user: User
}>

export default defineComponent<Props>({
  name: 'ZLtcsInsCardForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup (props, context) {
    const { form, observer, submit } = useFormBindings(props, context, {
      init: x => ({
        ltcsLevel: x.ltcsLevel,
        maxBenefitQuotas: x.maxBenefitQuotas ?? [],
        carePlanAuthorType: x.carePlanAuthorType
      }),
      processOutput: output => {
        const isCareLevel = ![
          LtcsLevel.supportLevel1,
          LtcsLevel.supportLevel2,
          LtcsLevel.target
        ].includes(output.ltcsLevel as any)
        return {
          ...output,
          ...(
            output.carePlanAuthorType === LtcsCarePlanAuthorType.self
              ? {
                carePlanAuthorOfficeId: undefined,
                careManagerName: undefined
              }
              : {}
          ),
          carePlanAuthorType: isCareLevel ? output.carePlanAuthorType : LtcsCarePlanAuthorType.preventionOffice,
          communityGeneralSupportCenterId: isCareLevel ? undefined : output.communityGeneralSupportCenterId,
          copayRate: (output.copayRate ?? 0) * 10 // 利用者負担割合を百分率にする
        }
      }
    })
    const quotasWrapper = createArrayWrapper(form.maxBenefitQuotas)
    const useQuotas = () => ({
      quotaKeys: quotasWrapper.keys,
      addQuota: () => quotasWrapper.push({}),
      deleteQuota: (index: number) => quotasWrapper.remove(index)
    })
    const useSelectOptions = () => ({
      carePlanAuthorTypes: enumerableOptions(LtcsCarePlanAuthorType)
        .filter(x => x.value !== LtcsCarePlanAuthorType.preventionOffice),
      ltcsInsCardServiceTypes: enumerableOptions(LtcsInsCardServiceType),
      ltcsInsCardStatuses: enumerableOptions(LtcsInsCardStatus),
      ltcsLevels: enumerableOptions(LtcsLevel)
    })
    const isCareLevel = computed(() => [
      LtcsLevel.careLevel1,
      LtcsLevel.careLevel2,
      LtcsLevel.careLevel3,
      LtcsLevel.careLevel4,
      LtcsLevel.careLevel5
    ].includes(form.ltcsLevel as any))
    const isSelfCreated = computed(() => form.carePlanAuthorType === LtcsCarePlanAuthorType.self)
    const rules = computed(() => validationRules({
      effectivatedOn: { required },
      status: { required },
      insNumber: { required, digits: 10 },
      issuedOn: { required },
      insurerNumber: { required, digits: 6 },
      insurerName: { required, max: 100 },
      ltcsLevel: { required },
      certificatedOn: { required },
      activatedOn: { required },
      deactivatedOn: { required },
      ltcsInsCardServiceType: { required },
      maxBenefitQuota: { required, numeric },
      carePlanAuthorType: { required },
      communityGeneralSupportCenterId: { required: !isCareLevel.value },
      carePlanAuthorOfficeId: { required: !isSelfCreated.value },
      careManagerName: { required: !isSelfCreated.value, max: 100 },
      copayRate: { required, numeric },
      copayActivatedOn: { required },
      copayDeactivatedOn: { required }
    }))
    watch(() => form.ltcsLevel, () => {
      if (!isCareLevel.value) {
        form.carePlanAuthorType = undefined
      }
    })
    const { officeOptions: carePlanAuthorOfficeOptions } = useOffices({
      qualifications: [OfficeQualification.ltcsCareManagement]
    })
    const { officeOptions: communityGeneralSupportCenterOptions } = useOffices({
      isCommunityGeneralSupportCenter: computed(() => !isCareLevel.value),
      qualifications: [OfficeQualification.ltcsPrevention]
    })
    return {
      ...useLtcsInsCardStatusIcon(computed(() => pick(form, ['status']))),
      ...useQuotas(),
      ...useRateOptions(),
      ...useSelectOptions(),
      carePlanAuthorOfficeOptions,
      communityGeneralSupportCenterOptions,
      form,
      isSelfCreated,
      isCareLevel,
      observer,
      rules,
      submit
    }
  }
})
</script>

<style lang="scss" module>
.item {
  display: flex;
  flex-flow: row nowrap;
  padding: 12px 16px;
}

.content {
  flex-grow: 1;

  .icon + & {
    margin-left: 24px;
  }
}

.label {
  line-height: 1rem;
}

.value {
  margin-top: 2px;

  :global(.v-chip) {
    margin-bottom: 4px;
    margin-right: 4px;
  }
}
</style>
