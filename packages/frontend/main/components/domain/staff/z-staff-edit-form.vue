<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <template v-if="isInputLimited">
          <z-data-card-item label="社員番号" :icon="$icons.employeeNumber" :value="form.employeeNumber" />
          <z-data-card-item label="状態" :icon="statusIcon" :value="resolveStaffStatus(form.status)" />
          <z-data-card-item label="名前" :icon="$icons.staff" :value="`${form.familyName} ${form.givenName}`" />
          <z-data-card-item label="名前：フリガナ" :value="`${form.phoneticFamilyName} ${form.phoneticGivenName}`" />
          <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(form.sex)" />
          <z-data-card-item label="生年月日" :icon="$icons.birthday">
            <z-era-date :value="form.birthday" />
          </z-data-card-item>
        </template>
        <template v-else>
          <z-form-card-item-set :icon="$icons.employeeNumber">
            <z-form-card-item
              v-slot="{ errors }"
              data-employee-number
              vid="employeeNumber"
              :rules="rules.employeeNumber"
            >
              <z-text-field
                v-model.trim="form.employeeNumber"
                data-employee-number-input
                label="社員番号"
                :error-messages="errors"
              />
            </z-form-card-item>
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
          <z-form-card-item-set :icon="$icons.staff">
            <v-row no-gutters>
              <v-col cols="12" sm="6">
                <z-form-card-item v-slot="{ errors }" data-family-name vid="familyName" :rules="rules.familyName">
                  <z-text-field
                    v-model.trim="form.familyName"
                    data-family-name-input
                    label="姓 *"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item v-slot="{ errors }" data-given-name vid="givenName" :rules="rules.givenName">
                  <z-text-field
                    v-model.trim="form.givenName"
                    data-given-name-input
                    label="名 *"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item
                  v-slot="{ errors }"
                  data-phonetic-family-name
                  vid="phoneticFamilyName"
                  :rules="rules.phoneticFamilyName"
                >
                  <z-text-field
                    v-model.trim="form.phoneticFamilyName"
                    v-auto-kana
                    data-phonetic-family-name-input
                    label="フリガナ：姓 *"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item
                  v-slot="{ errors }"
                  data-phonetic-given-name
                  vid="phoneticGivenName"
                  :rules="rules.phoneticGivenName"
                >
                  <z-text-field
                    v-model.trim="form.phoneticGivenName"
                    v-auto-kana
                    data-phonetic-given-name-input
                    label="フリガナ：名 *"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.sex">
            <z-form-card-item v-slot="{ errors }" data-sex vid="sex" :rules="rules.sex">
              <z-select v-model="form.sex" label="性別 *" :error-messages="errors" :items="sexes" />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.birthday">
            <z-form-card-item v-slot="{ errors }" data-birthday vid="birthday" :rules="rules.birthday">
              <z-date-field
                v-model="form.birthday"
                birthday
                label="生年月日 *"
                :error-messages="errors"
                :max="$datetime.now.toISODate()"
              />
            </z-form-card-item>
          </z-form-card-item-set>
        </template>
        <z-form-card-item-set :icon="$icons.addr">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-postcode vid="postcode" :rules="rules.postcode">
                <z-text-field
                  v-model.trim="form.postcode"
                  v-mask="'###-####'"
                  label="郵便番号 *"
                  type="tel"
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
                <z-select v-model="form.prefecture" label="都道府県 *" :error-messages="errors" :items="prefectures" />
              </z-form-card-item>
            </v-col>
          </v-row>
          <z-form-card-item v-slot="{ errors }" data-city vid="city" :rules="rules.city">
            <z-text-field v-model.trim="form.city" label="市区町村 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item v-slot="{ errors }" data-street vid="street" :rules="rules.street">
            <z-text-field
              ref="streetInput"
              v-model.trim="form.street"
              label="町名・番地 *"
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
                label="電話番号 *"
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
        <z-form-card-item-set :icon="$icons.email">
          <z-form-card-item v-slot="{ errors }" data-email vid="email" :rules="rules.email">
            <z-text-field v-model.trim="form.email" label="メールアドレス *" type="email" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <template v-if="isInputLimited">
          <z-data-card-item label="資格" :icon="$icons.certification">
            <div v-if="form.certifications.length === 0">-</div>
            <template v-else>
              <v-chip v-for="x in form.certifications" :key="x" label small>{{ resolveCertification(x) }}</v-chip>
            </template>
          </z-data-card-item>
          <z-data-card-item label="所属事業所" :icon="$icons.office">
            <template v-if="form.officeIds.length === 0">
              <span>-</span>
            </template>
            <template v-else>
              <v-chip v-for="x in form.officeIds" :key="x" label small>{{ resolveOfficeAbbr(x) }}</v-chip>
            </template>
          </z-data-card-item>
        </template>
        <template v-else>
          <z-form-card-item-set :icon="$icons.certification">
            <z-form-card-item
              v-slot="{ errors }"
              data-certifications
              vid="certifications"
              :rules="rules.certifications"
            >
              <z-select
                v-model="form.certifications"
                label="資格"
                multiple
                small-chips
                :error-messages="errors"
                :items="certifications"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.office">
            <z-form-card-item v-slot="{ errors }" data-office-ids vid="officeIds" :rules="rules.officeIds">
              <z-keyword-filter-autocomplete
                v-model="form.officeIds"
                label="所属事業所"
                multiple
                small-chips
                :error-messages="errors"
                :items="officeOptions"
                :loading="isLoadingOffices"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.blank">
            <z-form-card-item
              v-slot="{ errors }"
              data-office-group-ids
              vid="officeGroupIds"
              :rules="rules.officeGroupIds"
            >
              <z-select
                v-model="form.officeGroupIds"
                label="所属事業所グループ"
                multipleco
                small-chips
                :error-messages="errors"
                :items="officeGroupOptions"
                :loading="isLoadingOfficeGroups"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.role">
            <z-form-card-item v-slot="{ errors }" data-role-ids vid="roleIds" :rules="rules.roleIds">
              <z-select
                v-model="form.roleIds"
                label="ロール"
                multiple
                small-chips
                :error-messages="errors"
                :items="roleOptions"
                :loading="isLoadingRoles"
              />
            </z-form-card-item>
          </z-form-card-item-set>
        </template>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Certification, resolveCertification } from '@zinger/enums/lib/certification'
import { Permission } from '@zinger/enums/lib/permission'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { resolveSex, Sex } from '@zinger/enums/lib/sex'
import { resolveStaffStatus, StaffStatus } from '@zinger/enums/lib/staff-status'
import { pick } from '@zinger/helpers'
import { mask } from 'vue-the-mask'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOffices } from '~/composables/use-offices'
import { usePostcodeResolver } from '~/composables/use-postcode-resolver'
import { useRoles } from '~/composables/use-roles'
import { useStaffStatusIcon } from '~/composables/use-staff-status-icon'
import { autoKana } from '~/directives/auto-kana'
import { phoneNumber } from '~/directives/phone-number'
import { StaffsApi } from '~/services/api/staffs-api'
import { email, fax, katakana, numeric, postcode, required, tel } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<StaffsApi.UpdateForm> & Readonly<{
  buttonText: string
  isInputLimited: boolean
}>

export default defineComponent<Props>({
  name: 'ZStaffEditForm',
  directives: {
    autoKana,
    mask,
    phoneNumber
  },
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    isInputLimited: { type: Boolean, default: false }
  },
  setup (props, context) {
    const { form, observer, submit } = useFormBindings(props, context)
    const rules = validationRules({
      employeeNumber: { numeric, max: process.env.employeeNumberMaxLength },
      familyName: { required, max: process.env.familyNameMaxLength },
      givenName: { required, max: process.env.givenNameMaxLength },
      phoneticFamilyName: { required, katakana, max: process.env.phoneticFamilyNameMaxLength },
      phoneticGivenName: { required, katakana, max: process.env.phoneticGivenNameMaxLength },
      sex: { required },
      birthday: { required },
      postcode: { required, postcode },
      prefecture: { required },
      city: { required, max: process.env.cityMaxLength },
      street: { required, max: process.env.streetMaxLength },
      apartment: { max: process.env.apartmentMaxLength },
      tel: { required, tel },
      fax: { fax },
      email: { required, email, max: process.env.emailMaxLength },
      password: { required, min: process.env.passwordMinLength },
      certifications: {},
      roleIds: {},
      status: { required }
    })
    return {
      ...useOfficeGroups({ permission: Permission.updateStaffs }),
      ...useOffices({ permission: Permission.updateStaffs, internal: true }),
      ...usePostcodeResolver(form),
      ...useRoles({ permission: Permission.updateStaffs }),
      ...useStaffStatusIcon(computed(() => pick(form, ['status']))),
      certifications: enumerableOptions(Certification),
      form,
      observer,
      prefectures: enumerableOptions(Prefecture),
      resolveCertification,
      resolveSex,
      resolveStaffStatus,
      rules,
      sexes: enumerableOptions(Sex).filter(x => x.value === Sex.female || x.value === Sex.male),
      statusOptions: enumerableOptions(StaffStatus),
      submit
    }
  }
})
</script>
