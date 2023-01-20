/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { Permission } from '@zinger/enums/lib/permission'
import { camelToKebab } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsCertificationStateKey } from '~/composables/stores/use-dws-certification-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsCertificationEditPage from '~/pages/users/_id/dws-certifications/_certificationId/edit.vue'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsCertificationResponseStub } from '~~/stubs/create-dws-certification-response-stub'
import { createDwsCertificationStoreStub } from '~~/stubs/create-dws-certification-store-stub'
import { createDwsCertificationStub } from '~~/stubs/create-dws-certification-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createFaker } from '~~/stubs/fake'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/dws-certifications/_certificationId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsCertifications', 'users')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const faker = createFaker('ICE CREAM SANDWICH')
  const form: DwsCertificationsApi.Form = {
    child: {
      name: {
        familyName: '倉田',
        givenName: '綾',
        displayName: '倉田 綾',
        phoneticFamilyName: 'クラタ',
        phoneticGivenName: 'アヤ',
        phoneticDisplayName: 'クラタ アヤ'
      },
      birthday: '1988-08-23'
    },
    effectivatedOn: faker.randomDateString(),
    status: DwsCertificationStatus.applied,
    dwsNumber: faker.randomNumericString(10),
    dwsTypes: [DwsType.physical],
    issuedOn: faker.randomDateString(),
    cityName: '東伯郡琴浦町',
    cityCode: faker.randomNumericString(5),
    dwsLevel: DwsLevel.level1,
    isSubjectOfComprehensiveSupport: true,
    activatedOn: faker.randomDateString(),
    deactivatedOn: faker.randomDateString(),
    grants: [
      {
        dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
        grantedAmount: 'amount',
        activatedOn: faker.randomDateString(),
        deactivatedOn: faker.randomDateString()
      }
    ],
    copayLimit: 6894,
    copayActivatedOn: faker.randomDateString(),
    copayDeactivatedOn: faker.randomDateString(),
    copayCoordination: {},
    agreements: [
      {
        indexNumber: 2,
        officeId: 3,
        dwsCertificationAgreementType: DwsCertificationAgreementType.accompany,
        paymentAmount: 44520,
        agreedOn: '1995-01-20',
        expiredOn: '1995-01-20'
      }
    ]
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const stub = createDwsCertificationStub()
  const dwsCertificationResponse = createDwsCertificationResponseStub(stub.id)
  const dwsCertificationStore = createDwsCertificationStoreStub(dwsCertificationResponse)
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    auth?: Partial<Auth>
    data?: Record<string, any>
    options?: MountOptions<Vue>
  }

  async function mountComponent ({ auth, data, options }: MountComponentParams = {}) {
    wrapper = mount(DwsCertificationEditPage, {
      ...provides(
        [dwsCertificationStateKey, dwsCertificationStore.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      ...options,
      mocks: {
        ...mocks,
        ...options?.mocks
      }
    })
    if (data) {
      await setData(wrapper, data)
    }
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    jest.spyOn($api.dwsCertifications, 'get').mockResolvedValue(createDwsCertificationResponseStub(stub.id))
  })

  afterAll(() => {
    mocked($api.dwsCertifications.get).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('display contents', () => {
    beforeAll(async () => {
      await mountComponent({ options: { stubs: ['z-dws-certification-form'] } })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should not display message and form when no option selected', () => {
      expect(wrapper).not.toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display message when the first option selected', async () => {
      await wrapper.setData({
        reason: 'A'
      })

      expect(wrapper).toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display message when the second option selected', async () => {
      await wrapper.setData({
        reason: 'B'
      })

      expect(wrapper).toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display form when the third option selected', async () => {
      await wrapper.setData({
        reason: 'C'
      })

      expect(wrapper).not.toContainElement('[data-message]')
      expect(wrapper).toContainElement('[data-form]')
    })
  })

  describe('submit', () => {
    beforeAll(async () => {
      const data = {
        reason: 'C'
      }
      await mountComponent({
        data,
        options: {
          stubs: ['v-card']
        }
      })
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.dwsCertifications, 'update').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($api.dwsCertifications.update).mockReset()
    })

    it('should call $api.dwsCertifications.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.dwsCertifications.update).toHaveBeenCalledTimes(1)
      expect($api.dwsCertifications.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        userId: stub.userId
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('受給者証情報を編集しました。')
    })

    // TODO z-dws-certification-form.vue の詳細についてテストしているため、ここにあるべきではない
    describe('z-dws-certification-form', () => {
      it.each([
        ['childFamilyName', '姓を入力してください。', 'family-name'],
        ['childGivenName', '名を入力してください。', 'given-name'],
        ['childPhoneticFamilyName', 'フリガナ：姓を入力してください。', 'phonetic-family-name'],
        ['childPhoneticGivenName', 'フリガナ：名を入力してください。', 'phonetic-given-name'],
        ['childBirthday', '生年月日を入力してください。', 'birthday'],
        ['effectivatedOn', '適用日を入力してください。'],
        ['status', '認定区分を入力してください。'],
        ['dwsNumber', '受給者証番号を入力してください。'],
        ['dwsTypes', '障害種別を入力してください。'],
        ['issuedOn', '交付年月日を入力してください。'],
        ['cityName', '市町村名を入力してください。'],
        ['cityCode', '市町村番号を入力してください。'],
        ['dwsLevel', '障害支援区分を入力してください。'],
        ['activatedOn', '認定有効期間（開始）を入力してください。'],
        ['deactivatedOn', '認定有効期間（終了）を入力してください。'],
        ['copayLimit', '負担上限月額を入力してください。'],
        ['copayActivatedOn', '利用者負担適用期間（開始）を入力してください。'],
        ['copayDeactivatedOn', '利用者負担適用期間（終了）を入力してください。'],
        ['copayCoordinationType', '上限管理区分を入力してください。'],
        ['copayOfficeId', '上限額管理事業所名を入力してください。']
      ])(
        'should display errors when server responses 400 Bad Request (error occurred in "%s")',
        async (key, message, testId = undefined) => {
          jest.spyOn($api.dwsCertifications, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          }))

          await wrapper.vm.submit(form)
          await wrapper.vm.$nextTick()

          const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

          expect($snackbar.success).not.toHaveBeenCalled()
          expect(targetWrapper.text()).toContain(message)
          expect(targetWrapper).toMatchSnapshot()
        }
      )

      /**
       * TODO こちらの項目は可変長なのでとりあえず 0 番目で検証している
       */
      it.each([
        ['grantedAmount', '支給量等を入力してください。', 'grant-granted-amount'],
        ['dwsCertificationServiceType', 'サービス種別を入力してください。', 'grant-dws-certification-service-type'],
        ['activatedOn', '支給決定期間（開始）を入力してください。', 'grant-activated-on'],
        ['deactivatedOn', '支給決定期間（終了）を入力してください。', 'grant-deactivated-on']
      ])(
        'should display errors when server responses 400 Bad Request (error occurred in "%s")',
        async (key, message, testId) => {
          jest.spyOn($api.dwsCertifications, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [`grants.0.${key}`]: [message]
            }
          }))

          await wrapper.vm.submit(form)
          await wrapper.vm.$nextTick()

          const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}="0"]`)

          expect($snackbar.success).not.toHaveBeenCalled()
          expect(targetWrapper.text()).toContain(message)
          expect(targetWrapper).toMatchSnapshot()
        }
      )

      /**
       * TODO こちらの項目は可変長なのでとりあえず 0 番目で検証している
       */
      it.each([
        ['indexNumber', '番号を入力してください。', 'agreement-index-number'],
        ['officeId', '事業所を入力してください。', 'agreement-office-id'],
        ['dwsCertificationAgreementType', 'サービス内容を入力してください。', 'agreement-dws-certification-agreement-type'],
        ['paymentAmount', '契約支給量を入力してください。', 'agreement-payment-amount'],
        ['agreedOn', '契約日を入力してください。', 'agreement-agreed-on'],
        ['expiredOn', '当該契約支給量によるサービス提供終了日を入力してください。', 'agreement-expired-on']
      ])(
        'should display errors when server responses 400 Bad Request (error occurred in "%s")',
        async (key, message, testId) => {
          jest.spyOn($api.dwsCertifications, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [`agreements.0.${key}`]: [message]
            }
          }))

          await wrapper.vm.submit(form)
          await wrapper.vm.$nextTick()

          const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}="0"]`)
          // @TODO paymentAmount はエラーメッセージがテキストに展開されないため html を使用する（暫定版なので手抜き）
          // FYI: <v-messages-stub color="error" value="契約支給量を入力してください。"></v-messages-stub>
          const targetContent = key === 'paymentAmount' ? targetWrapper.html() : targetWrapper.text()

          expect($snackbar.success).not.toHaveBeenCalled()
          expect(targetContent).toContain(message)
          expect(targetWrapper).toMatchSnapshot()
        }
      )
    })
  })

  describe('card-actions', () => {
    const requiredPermissions: Permission[] = [Permission.createDwsCertifications]
    const data = { reason: 'A' }
    const stubs = ['v-card-title', 'v-card-text', 'v-radio-group']

    it('should be rendered when session auth is system admin', async () => {
      await mountComponent({ data, options: { stubs } })

      expect(wrapper).toContainElement('[data-card-actions]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, async () => {
      const auth = {
        permissions: requiredPermissions
      }
      await mountComponent({ auth, data, options: { stubs } })

      expect(wrapper).toContainElement('[data-card-actions]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, async () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      await mountComponent({ auth, data, options: { stubs } })

      expect(wrapper).not.toContainElement('[data-card-actions]')
      unmountComponent()
    })
  })

  describe('error text in card-text', () => {
    const requiredPermissions: Permission[] = [Permission.createDwsCertifications]
    const data = { reason: 'A' }
    const stubs = ['v-card-title', 'v-card-actions', 'v-radio-group']

    it('should not be rendered when session auth is system admin', async () => {
      await mountComponent({ data, options: { stubs } })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff has permissions: ${requiredPermissions}`, async () => {
      const auth = {
        permissions: requiredPermissions
      }
      await mountComponent({ auth, data, options: { stubs } })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should be rendered when the staff does not have permissions: ${requiredPermissions}`, async () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      await mountComponent({ auth, data, options: { stubs } })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })
  })
})
