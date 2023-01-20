<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form class="z-user-form" data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="statusIcon">
          <v-col class="px-1 pt-0">
            <z-form-card-item v-slot="{ errors }" :rules="rules.isEnabled" data-is-enabled vid="isEnabled">
              <v-radio-group v-model="form.isEnabled" dense :error-messages="errors">
                <template #label>
                  <div>状態</div>
                </template>
                <z-flex>
                  <div v-for="x in [true, false]" :key="x" class="mr-4">
                    <v-radio :label="`${x ? '利用中' : '利用終了'}`" :value="x" />
                  </div>
                </z-flex>
              </v-radio-group>
            </z-form-card-item>
          </v-col>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.user">
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
          <v-row v-for="(contact, i) in form.contacts" :key="i" no-gutters>
            <v-col cols="6" sm="4">
              <z-form-card-item
                v-slot="{ errors }"
                data-tel
                :rules="isRequiredContact(i) ? rules.tel : {}"
                :vid="`tel_${i}`"
              >
                <z-text-field
                  v-model.trim="contact.tel"
                  v-phone-number
                  data-tel-input
                  type="tel"
                  :error-messages="errors"
                  :label="`${i !== 0 ? `予備（${i}）` : ''}電話番号${i === 0 ? ' *' : ''}`"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="6" sm="4">
              <z-form-card-item
                v-slot="{ errors }"
                data-relationship
                :rules="isRequiredContact(i) ? rules.relationship : {}"
                :vid="`relationship_${i}`"
              >
                <z-select
                  v-model="contact.relationship"
                  clearable
                  :error-messages="errors"
                  :items="relationships"
                  :label="`${i !== 0 ? `予備（${i}）` : ''}続柄・関係${i === 0 ? ' *' : ''}`"
                  @change="changeRelationship(i, $event)"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="4">
              <z-form-card-item
                v-if="!isTheirself(contact.relationship)"
                v-slot="{ errors }"
                data-name
                :rules="isRequiredContact(i) && !isTheirself(contact.relationship) ? rules.name : {}"
                :vid="`name_${i}`"
              >
                <z-text-field
                  v-model.trim="contact.name"
                  :error-messages="errors"
                  :label="`${i !== 0 ? `予備（${i}）` : ''}名前${i === 0 ? ' *' : ''}`"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="請求先情報">
        <z-form-card-item-set :icon="$icons.billing">
          <z-form-card-item
            v-slot="{ errors }"
            class="mb-2"
            data-destination
            vid="destination"
            :rules="rules.destination"
          >
            <v-radio-group v-model="form.billingDestination.destination" :error-messages="errors" dense>
              <template #label>
                <div>請求先</div>
              </template>
              <z-flex>
                <div v-for="x in validBillingDestinations" :key="x" class="mr-4">
                  <v-radio :label="resolveBillingDestination(x)" :value="x" />
                </div>
              </z-flex>
            </v-radio-group>
          </z-form-card-item>
          <z-form-card-item
            v-slot="{ errors }"
            class="mb-2"
            data-payment-method
            vid="paymentMethod"
            :rules="rules.paymentMethod"
          >
            <v-radio-group v-model="form.billingDestination.paymentMethod" :error-messages="errors" dense>
              <template #label>
                <div>支払方法</div>
              </template>
              <z-flex>
                <div v-for="x in validPaymentMethods" :key="x" class="mr-4">
                  <v-radio :label="resolvePaymentMethod(x)" :value="x" />
                </div>
              </z-flex>
            </v-radio-group>
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="form.billingDestination.paymentMethod === PaymentMethod.withdrawal">
          <z-form-card-item v-slot="{ errors }" data-contract-number vid="contractNumber" :rules="rules.contractNumber">
            <z-text-field
              v-model.trim="form.billingDestination.contractNumber"
              label="契約者番号 *"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set
          v-if="form.billingDestination.destination === BillingDestination.corporation"
          :icon="$icons.office"
        >
          <z-form-card-item
            v-slot="{ errors }"
            data-corporation-name
            vid="corporationName"
            :rules="rules.corporationName"
          >
            <z-text-field
              v-model.trim="form.billingDestination.corporationName"
              label="法人名・団体名 *"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <template
          v-if="[BillingDestination.agent, BillingDestination.corporation]
            .includes(form.billingDestination.destination)"
        >
          <z-form-card-item-set :icon="$icons.personalName">
            <z-form-card-item v-slot="{ errors }" data-agent-name vid="agentName" :rules="rules.name">
              <z-text-field
                v-model.trim="form.billingDestination.agentName"
                :label="form.billingDestination.destination === BillingDestination.agent ? '氏名 *' : '担当者名 *'"
                :error-messages="errors"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.addr">
            <v-row no-gutters>
              <v-col cols="12" sm="6">
                <z-form-card-item v-slot="{ errors }" vid="billingDestination.postcode" :rules="rules.postcode">
                  <z-text-field
                    v-model.trim="form.billingDestination.addr.postcode"
                    v-mask="'###-####'"
                    label="郵便番号 *"
                    type="tel"
                    :error-messages="errors"
                  >
                    <template #append-outer>
                      <z-postcode-resolver
                        :postcode="form.billingDestination.addr.postcode"
                        @update="onBillingPostcodeResolved"
                      />
                    </template>
                  </z-text-field>
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item v-slot="{ errors }" vid="billingDestination.prefecture" :rules="rules.prefecture">
                  <z-select
                    v-model="form.billingDestination.addr.prefecture"
                    label="都道府県 *"
                    :error-messages="errors"
                    :items="prefectures"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
            <z-form-card-item v-slot="{ errors }" vid="billingDestination.city" :rules="rules.city">
              <z-text-field
                v-model.trim="form.billingDestination.addr.city"
                label="市区町村 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item v-slot="{ errors }" vid="billingDestination.street" :rules="rules.street">
              <z-text-field
                ref="billingStreetInput"
                v-model.trim="form.billingDestination.addr.street"
                label="町名・番地 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item v-slot="{ errors }" vid="billingDestination.apartment" :rules="rules.apartment">
              <z-text-field
                v-model.trim="form.billingDestination.addr.apartment"
                label="建物名など"
                :error-messages="errors"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.tel">
            <z-form-card-item v-slot="{ errors }" vid="billingDestination.tel" :rules="rules.tel">
              <z-text-field
                v-model.trim="form.billingDestination.tel"
                v-phone-number
                label="電話番号 *"
                :error-messages="errors"
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
import { BillingDestination, resolveBillingDestination } from '@zinger/enums/lib/billing-destination'
import { Certification } from '@zinger/enums/lib/certification'
import { ContactRelationship } from '@zinger/enums/lib/contact-relationship'
import { PaymentMethod, resolvePaymentMethod } from '@zinger/enums/lib/payment-method'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { isEmpty, pick } from '@zinger/helpers'
import { mask } from 'vue-the-mask'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { usePostcodeResolver } from '~/composables/use-postcode-resolver'
import { useUserStatusIcon } from '~/composables/use-user-status-icon'
import { autoKana } from '~/directives/auto-kana'
import { phoneNumber } from '~/directives/phone-number'
import { UserBillingDestination } from '~/models/user-billing-destination'
import { UsersApi } from '~/services/api/users-api'
import { katakana, postcode, required, tel } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DeepPartial<UsersApi.Form>> & Readonly<{
  buttonText: string
}>

type ContactsIndex = 0 | 1 | 2

export default defineComponent<Props>({
  name: 'ZUserForm',
  directives: {
    autoKana,
    mask,
    phoneNumber
  },
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true }
  },
  setup (props, context) {
    const isTheirself = (x: ContactRelationship | undefined) => x === ContactRelationship.theirself
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        familyName: form.familyName,
        givenName: form.givenName,
        phoneticFamilyName: form.phoneticFamilyName,
        phoneticGivenName: form.phoneticGivenName,
        sex: form.sex,
        birthday: form.birthday,
        postcode: form.postcode,
        prefecture: form.prefecture,
        city: form.city,
        street: form.street,
        apartment: form.apartment,
        contacts: form.contacts?.map(x => ({ ...x })).concat([{}, {}, {}]).slice(0, 3) ?? [{}, {}, {}],
        isEnabled: form.isEnabled ?? true,
        billingDestination: {
          ...form.billingDestination,
          addr: form.billingDestination?.addr ?? {}
        }
      }),
      processOutput: output => {
        const billing = output.billingDestination as Partial<UserBillingDestination>
        return {
          ...output,
          contacts: output.contacts!.filter(x => !isEmpty(x.tel))
            .map(x => ({
              ...x,
              name: isTheirself(x?.relationship) ? '' : x.name
            })),
          billingDestination: {
            destination: billing.destination,
            paymentMethod: billing.paymentMethod,
            contractNumber: billing.paymentMethod === PaymentMethod.withdrawal ? billing.contractNumber : '',
            corporationName: billing.destination === BillingDestination.corporation ? billing.corporationName : '',
            agentName: billing.destination === BillingDestination.agent || billing.destination === BillingDestination.corporation ? billing.agentName : '',
            ...(
              billing.destination !== BillingDestination.theirself
                ? { ...billing?.addr, tel: billing.tel }
                : { postcode: '', prefecture: '', city: '', street: '', apartment: '', tel: '' }
            )
          }
        }
      }
    })
    const isRequiredContact = (i: ContactsIndex) => {
      return Object.values(form.contacts![i]).join('') !== ''
    }
    const changeRelationship = (i: ContactsIndex, relationship: ContactRelationship) => {
      if (isTheirself(relationship)) {
        form.contacts![i].name = ''
      }
    }
    const rules = computed<Rules>(() => {
      const customDestination = {
        message: '入力してください',
        validate: () => form.billingDestination?.destination !== BillingDestination.none
      }
      const customPaymentMethod = {
        message: '入力してください',
        validate: () => form.billingDestination?.paymentMethod !== PaymentMethod.none
      }
      return validationRules({
        isEnabled: { required },
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
        relationship: { required },
        name: { required },
        destination: { required, custom: customDestination },
        paymentMethod: { required, custom: customPaymentMethod },
        contractNumber: { required, length: 10 },
        corporationName: { required }
      })
    })
    const {
      onPostcodeResolved: onBillingPostcodeResolved,
      streetInput: billingStreetInput
    } = usePostcodeResolver(form.billingDestination?.addr ?? {})
    return {
      ...usePostcodeResolver(form),
      ...useUserStatusIcon(computed(() => pick(form, ['isEnabled']))),
      certifications: enumerableOptions(Certification),
      changeRelationship,
      form,
      isRequiredContact,
      isTheirself,
      billingStreetInput,
      onBillingPostcodeResolved,
      BillingDestination,
      PaymentMethod,
      validBillingDestinations: BillingDestination.values.filter(x => x !== BillingDestination.none),
      validPaymentMethods: PaymentMethod.values.filter(x => x !== PaymentMethod.none),
      resolveBillingDestination,
      resolvePaymentMethod,
      observer,
      prefectures: enumerableOptions(Prefecture),
      relationships: enumerableOptions(ContactRelationship),
      rules,
      sexes: enumerableOptions(Sex).filter(x => x.value === Sex.female || x.value === Sex.male),
      submit
    }
  }
})
</script>
