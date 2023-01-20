<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-container v-if="invitation.isResolved.value" data-page-sign-up :class="$style.root" fill-height>
    <v-row align-content="center" justify="center" no-gutters>
      <v-col v-if="forbidden" cols="12" lg="6" md="8" sm="10" xl="4">
        <v-card>
          <z-card-titlebar color="primary">URL の有効期限が切れています</z-card-titlebar>
          <v-card-text>お手数ですが、もう一度最初からお手続きください。</v-card-text>
        </v-card>
      </v-col>
      <v-col v-else cols="12" lg="6" md="8" sm="10" xl="4">
        <transition mode="out-in" :name="transition">
          <div v-if="step < steps" key="form">
            <v-card>
              <z-card-titlebar color="primary">新規登録</z-card-titlebar>
              <validation-observer ref="observer" tag="div">
                <v-stepper vertical :value="step">
                  <v-stepper-step step="1" :complete="step > 1">メールアドレスとパスワード</v-stepper-step>
                  <v-stepper-content data-step-1 step="1">
                    <v-form data-form-1 @submit.prevent="next(observer1)">
                      <validation-observer ref="observer1" tag="div">
                        <v-container class="pa-0">
                          <v-row dense>
                            <v-col cols="12" data-email>
                              <div class="ml-3">
                                <div class="text-caption">メールアドレス</div>
                                <div class="text--primary">{{ email }}</div>
                              </div>
                            </v-col>
                            <v-col cols="12" data-password>
                              <validation-provider v-slot="{ errors }" vid="password" tag="div" :rules="rules.password">
                                <z-text-field
                                  v-model.trim="form.password"
                                  label="パスワード *"
                                  :append-icon="passwordVisibility ? $icons.visible : $icons.invisible"
                                  :error-messages="errors"
                                  :type="passwordVisibility ? 'text' : 'password'"
                                  @click:append="togglePasswordVisibility"
                                />
                              </validation-provider>
                            </v-col>
                          </v-row>
                        </v-container>
                        <v-row dense>
                          <v-spacer />
                          <v-btn color="primary" data-next-1 text type="submit">
                            <span>次へ</span>
                            <v-icon right>{{ $icons.forward }}</v-icon>
                          </v-btn>
                        </v-row>
                      </validation-observer>
                    </v-form>
                  </v-stepper-content>
                  <v-stepper-step step="2" :complete="step > 2">氏名・性別・生年月日</v-stepper-step>
                  <v-stepper-content data-step-2 step="2">
                    <v-form data-form-2 @submit.prevent="next(observer2)">
                      <validation-observer ref="observer2" tag="div">
                        <v-container class="pa-0">
                          <v-row dense>
                            <v-col cols="6" data-family-name>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="familyName"
                                tag="div"
                                :rules="rules.familyName"
                              >
                                <z-text-field
                                  v-model.trim="form.familyName"
                                  data-family-name-input
                                  label="姓 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="6" data-given-name>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="givenName"
                                tag="div"
                                :rules="rules.givenName"
                              >
                                <z-text-field
                                  v-model.trim="form.givenName"
                                  data-given-name-input
                                  label="名 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="6" data-phonetic-family-name>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="phoneticFamilyName"
                                tag="div"
                                :rules="rules.phoneticFamilyName"
                              >
                                <z-text-field
                                  v-model.trim="form.phoneticFamilyName"
                                  v-auto-kana
                                  data-phonetic-family-name-input
                                  label="フリガナ：姓 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="6" data-phonetic-given-name>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="phoneticGivenName"
                                tag="div"
                                :rules="rules.phoneticGivenName"
                              >
                                <z-text-field
                                  v-model.trim="form.phoneticGivenName"
                                  v-auto-kana
                                  data-phonetic-given-name-input
                                  label="フリガナ：名 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                          </v-row>
                          <v-row dense>
                            <v-col cols="12" data-sex sm="6">
                              <validation-provider v-slot="{ errors }" vid="sex" tag="div" :rules="rules.sex">
                                <z-select
                                  v-model="form.sex"
                                  label="性別 *"
                                  :error-messages="errors"
                                  :items="sexes"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-birthday sm="6">
                              <validation-provider v-slot="{ errors }" vid="birthday" tag="div" :rules="rules.birthday">
                                <z-date-field
                                  v-model="form.birthday"
                                  birthday
                                  label="生年月日 *"
                                  :error-messages="errors"
                                  :max="$datetime.now.toISODate()"
                                />
                              </validation-provider>
                            </v-col>
                          </v-row>
                        </v-container>
                        <v-row dense>
                          <v-spacer />
                          <v-btn color="secondary" data-prev-2 text @click="prev">戻る</v-btn>
                          <v-btn color="primary" data-next-2 text type="submit">
                            <span>次へ</span>
                            <v-icon right>{{ $icons.forward }}</v-icon>
                          </v-btn>
                        </v-row>
                      </validation-observer>
                    </v-form>
                  </v-stepper-content>
                  <v-stepper-step step="3" :complete="step > 3">住所・電話番号・FAX番号</v-stepper-step>
                  <v-stepper-content data-step-3 step="3">
                    <v-form data-form-3 @submit.prevent="next(observer3)">
                      <validation-observer ref="observer3" tag="div">
                        <v-container class="pa-0">
                          <v-row dense>
                            <v-col cols="12" data-postcode sm="6">
                              <validation-provider v-slot="{ errors }" vid="postcode" tag="div" :rules="rules.postcode">
                                <z-text-field
                                  v-model.trim="form.postcode"
                                  v-mask="'###-####'"
                                  type="tel"
                                  label="郵便番号 *"
                                  :error-messages="errors"
                                >
                                  <template #append-outer>
                                    <z-postcode-resolver :postcode="form.postcode" @update="onPostcodeResolved" />
                                  </template>
                                </z-text-field>
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-prefecture sm="6">
                              <validation-provider
                                v-slot="{ errors }"
                                vid="prefecture"
                                tag="div"
                                :rules="rules.prefecture"
                              >
                                <z-select
                                  v-model="form.prefecture"
                                  label="都道府県 *"
                                  :error-messages="errors"
                                  :items="prefectures"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-city>
                              <validation-provider v-slot="{ errors }" vid="city" tag="div" :rules="rules.city">
                                <z-text-field
                                  v-model.trim="form.city"
                                  label="市区町村 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-street>
                              <validation-provider v-slot="{ errors }" vid="street" tag="div" :rules="rules.street">
                                <z-text-field
                                  ref="streetInput"
                                  v-model.trim="form.street"
                                  label="町名・番地 *"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-apartment>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="apartment"
                                tag="div"
                                :rules="rules.apartment"
                              >
                                <z-text-field
                                  v-model.trim="form.apartment"
                                  label="建物名など"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-tel sm="6">
                              <validation-provider v-slot="{ errors }" vid="tel" tag="div" :rules="rules.tel">
                                <z-text-field
                                  v-model.trim="form.tel"
                                  v-phone-number
                                  data-tel-input
                                  label="電話番号 *"
                                  type="tel"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                            <v-col cols="12" data-fax sm="6">
                              <validation-provider v-slot="{ errors }" vid="fax" tag="div" :rules="rules.fax">
                                <z-text-field
                                  v-model.trim="form.fax"
                                  v-phone-number
                                  data-fax-input
                                  label="FAX番号"
                                  type="tel"
                                  :error-messages="errors"
                                />
                              </validation-provider>
                            </v-col>
                          </v-row>
                          <v-row dense>
                            <v-spacer />
                            <v-btn color="secondary" data-prev-3 text @click="prev">戻る</v-btn>
                            <v-btn color="primary" data-next-3 text type="submit">
                              <span>次へ</span>
                              <v-icon right>{{ $icons.forward }}</v-icon>
                            </v-btn>
                          </v-row>
                        </v-container>
                      </validation-observer>
                    </v-form>
                  </v-stepper-content>
                  <v-stepper-step step="4" :complete="step > 4">資格</v-stepper-step>
                  <v-stepper-content data-step-4 step="4">
                    <v-form data-form-4 @submit.prevent="next(observer4)">
                      <validation-observer ref="observer4" tag="div">
                        <v-container class="pa-0">
                          <v-row dense>
                            <v-col cols="12" data-certifications>
                              <validation-provider
                                v-slot="{ errors }"
                                vid="certifications"
                                tag="div"
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
                              </validation-provider>
                            </v-col>
                          </v-row>
                          <v-row dense>
                            <v-spacer />
                            <v-btn color="secondary" data-prev-4 text @click="prev">戻る</v-btn>
                            <v-btn color="primary" data-next-4 text type="submit">
                              <span>次へ</span>
                              <v-icon right>{{ $icons.forward }}</v-icon>
                            </v-btn>
                          </v-row>
                        </v-container>
                      </validation-observer>
                    </v-form>
                  </v-stepper-content>
                </v-stepper>
              </validation-observer>
            </v-card>
          </div>
          <div v-else-if="step === steps" key="confirming">
            <v-card>
              <z-card-titlebar color="primary">登録内容確認</z-card-titlebar>
              <v-card-text>
                <v-alert
                  v-if="axiosErrors"
                  class="text-sm-body-2 mb-3"
                  data-alert
                  dense
                  type="error"
                >
                  <template v-for="(error, i) in axiosErrors">
                    {{ error }}<br :key="i">
                  </template>
                </v-alert>
                <p>内容に間違いがないか確認し《登録》ボタンを押してください。</p>
                <z-data-card-item label="メールアドレス" :icon="$icons.email" :value="email" />
                <z-data-card-item label="パスワード" value="********" :icon="$icons.password" />
                <z-data-card-item label="氏名（フリガナ）" :icon="$icons.staff">
                  {{ form.familyName }} {{ form.givenName }}
                  （{{ form.phoneticFamilyName }} {{ form.phoneticGivenName }}）
                </z-data-card-item>
                <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(form.sex)" />
                <z-data-card-item label="生年月日" :icon="$icons.birthday">
                  <z-era-date :value="form.birthday" />
                  <span>（{{ age(form.birthday) }}歳）</span>
                </z-data-card-item>
                <z-data-card-item label="住所" :icon="$icons.addr">
                  〒{{ form.postcode }}<br>{{ resolvePrefecture(form.prefecture) }}{{ form.city }}{{ form.street }}
                  <template v-if="form.apartment"><br>{{ form.apartment }}</template>
                </z-data-card-item>
                <z-data-card-item label="電話番号" :icon="$icons.tel" :value="form.tel" />
                <z-data-card-item label="FAX番号" :value="form.fax || '-'" />
                <z-data-card-item label="資格" :icon="$icons.certification">
                  <span v-if="form.certifications.length === 0">-</span>
                  <template v-else>
                    <v-chip v-for="x in selectedCertifications" :key="x.id" label small>
                      {{ x.text }}
                    </v-chip>
                  </template>
                </z-data-card-item>
              </v-card-text>
              <v-card-actions>
                <v-spacer />
                <v-btn data-prev-5 text :disabled="progress" @click="prev">戻る</v-btn>
                <v-btn color="primary" data-submit :disabled="progress" :loading="progress" @click="submit">登録</v-btn>
              </v-card-actions>
            </v-card>
          </div>
          <div v-else key="created">
            <v-card>
              <z-card-titlebar color="primary">登録完了</z-card-titlebar>
              <v-card-text>
                <p>スタッフ登録が完了しました。</p>
                <p>ログイン画面へ移動し、登録したメールアドレス・パスワードでログインしてください。</p>
              </v-card-text>
              <v-card-actions class="pa-4">
                <v-btn block color="primary" nuxt text to="/">ログイン画面へ</v-btn>
              </v-card-actions>
            </v-card>
          </div>
        </transition>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { Certification } from '@zinger/enums/lib/certification'
import { Prefecture, resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { resolveSex, Sex } from '@zinger/enums/lib/sex'
import { mask } from 'vue-the-mask'
import { age } from '~/composables/age'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useAsync } from '~/composables/use-async'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { usePostcodeResolver } from '~/composables/use-postcode-resolver'
import { autoKana } from '~/directives/auto-kana'
import { phoneNumber } from '~/directives/phone-number'
import { NuxtContext } from '~/models/nuxt'
import { StaffsApi } from '~/services/api/staffs-api'
import { wait } from '~/support'
import { observerRef } from '~/support/reactive'
import { fax, katakana, postcode, required, tel } from '~/support/validation/rules'
import { ValidationObserverInstance } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

const STEPS = 5

export default defineComponent({
  name: 'SignUpPage',
  directives: {
    autoKana,
    mask,
    phoneNumber
  },
  layout: 'login',
  validate ({ params }: NuxtContext) {
    return /^[a-zA-Z0-9]{60}$/.test(params.token)
  },
  setup () {
    const { $api, $route } = usePlugins()
    const { errors: axiosErrors, progress, withAxios } = useAxios()
    const certifications = enumerableOptions(Certification)

    const observer = observerRef()
    const observer1 = observerRef()
    const observer2 = observerRef()
    const observer3 = observerRef()
    const observer4 = observerRef()

    const data = reactive({
      forbidden: false,
      form: {
        certifications: [] as Certification[]
      } as StaffsApi.CreateForm,
      passwordVisibility: false,
      step: 1,
      transition: ''
    })
    const selectedCertifications = computed(() => {
      const xs = data.form.certifications ?? []
      return certifications.filter(x => xs.includes(x.value))
    })
    const invitation = useAsync(async () => {
      const { token } = $route.params
      try {
        return await $api.invitations.get({ token })
      } catch (reason) {
        data.forbidden = true
      }
    })
    const email = computed(() => invitation.resolvedValue.value?.invitation.email ?? '')

    const rules = validationRules({
      password: { required, min: process.env.passwordMinLength },
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
      certifications: {}
    })

    const next = async (observerRef: ValidationObserverInstance | undefined) => {
      if (await observerRef?.validate()) {
        // v-stepper が v-if で消えた後に setTimeout 内で既に存在しない要素を参照してエラーを起こす
        // 場合があるため setTimeout を使って処理を遅延することでそれを回避する
        await wait(10)
        data.transition = 'slide'
        data.step += 1
      }
    }
    const prev = () => {
      data.transition = 'slide-reverse'
      data.step -= 1
    }
    const submit = () => withAxios(async () => {
      await $api.staffs.create({
        form: {
          ...data.form,
          invitationId: invitation.resolvedValue.value?.invitation.id,
          token: invitation.resolvedValue.value?.invitation.token
        }
      })
      data.transition = 'slide'
      data.step += 1
    })
    const togglePasswordVisibility = (): void => {
      data.passwordVisibility = !data.passwordVisibility
    }

    return {
      ...usePostcodeResolver(data.form),
      ...toRefs(data),
      age,
      axiosErrors: computed(() => {
        return Object.keys(axiosErrors.value).length >= 1
          ? Object.values(axiosErrors.value).flat()
          : null
      }),
      certifications,
      email,
      invitation,
      next,
      observer,
      observer1,
      observer2,
      observer3,
      observer4,
      prefectures: enumerableOptions(Prefecture),
      prev,
      progress,
      resolvePrefecture,
      resolveSex,
      rules,
      selectedCertifications,
      sexes: enumerableOptions(Sex).filter(x => x.value === Sex.female || x.value === Sex.male),
      steps: STEPS,
      submit,
      togglePasswordVisibility
    }
  },
  head: () => ({
    title: 'スタッフ登録'
  })
})
</script>

<style lang="scss" module>
.root {
  :global {
    .v-btn + .v-btn {
      margin-left: 8px;
    }

    .v-card__actions {
      padding: 16px;
    }
  }
}
</style>
