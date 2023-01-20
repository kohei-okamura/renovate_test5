/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { Permission } from '@zinger/enums/lib/permission'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { ltcsInsCardStateKey, ltcsInsCardStoreKey } from '~/composables/stores/use-ltcs-ins-card-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsInsCardEditPage from '~/pages/users/_id/ltcs-ins-cards/_cardId/edit.vue'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsInsCardResponseStub } from '~~/stubs/create-ltcs-ins-card-response-stub'
import { createLtcsInsCardStoreStub } from '~~/stubs/create-ltcs-ins-card-store-stub'
import { createLtcsInsCardStub } from '~~/stubs/create-ltcs-ins-card-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createFaker } from '~~/stubs/fake'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/ltcs-ins-cards/_cardId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const faker = createFaker('GINGER BREAD')
  const form: LtcsInsCardsApi.Form = {
    effectivatedOn: faker.randomDateString(),
    status: LtcsInsCardStatus.applied,
    insNumber: faker.randomNumericString(10),
    issuedOn: faker.randomDateString(),
    insurerNumber: faker.randomNumericString(8),
    insurerName: '邑楽郡明和町',
    ltcsLevel: LtcsLevel.careLevel2,
    certificatedOn: faker.randomDateString(),
    activatedOn: faker.randomDateString(),
    deactivatedOn: faker.randomDateString(),
    maxBenefitQuotas: [
      {
        ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
        maxBenefitQuota: 280600
      }
    ],
    carePlanAuthorOfficeId: 2,
    copayRate: 30,
    copayActivatedOn: faker.randomDateString(),
    copayDeactivatedOn: faker.randomDateString()
  }
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const stub = createLtcsInsCardStub()
  const { id, userId } = stub
  const ltcsInsCardStore = createLtcsInsCardStoreStub(createLtcsInsCardResponseStub(stub.id))
  const userStore = createUserStoreStub(createUserResponseStub(stub.userId))

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
  }

  function mountComponent ({ auth, ...options }: MountComponentArguments = {}) {
    wrapper = mount(LtcsInsCardEditPage, () => ({
      ...provides(
        [ltcsInsCardStateKey, ltcsInsCardStore.state],
        [ltcsInsCardStoreKey, ltcsInsCardStore],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      ...options,
      mocks: {
        ...mocks,
        ...options?.mocks
      }
    }))
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  describe('display', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should be rendered correctly', () => {
      expect(wrapper).toMatchSnapshot()
    })

    it('should not display message and form when no option selected', async () => {
      await setData(wrapper, { reason: '' })

      expect(wrapper).not.toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display message when the first option selected', async () => {
      await setData(wrapper, { reason: 'A' })

      expect(wrapper).toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display message when the second option selected', async () => {
      await setData(wrapper, { reason: 'B' })

      expect(wrapper).toContainElement('[data-message]')
      expect(wrapper).not.toContainElement('[data-form]')
    })

    it('should display form when the third option selected', async () => {
      await setData(wrapper, { reason: 'C' })

      expect(wrapper).not.toContainElement('[data-message]')
      expect(wrapper).toContainElement('[data-form]')
    })
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(async () => {
      jest.spyOn(ltcsInsCardStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      await mountComponent()
      await setData(wrapper, { reason: 'C' })
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(ltcsInsCardStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(ltcsInsCardStore.update).mockClear()
    })

    it('should call ltcsInsCardStore.update when pass the validation', async () => {
      const expected = {
        form,
        id,
        userId
      }

      await wrapper.vm.submit(form)

      expect(ltcsInsCardStore.update).toHaveBeenCalledTimes(1)
      expect(ltcsInsCardStore.update).toHaveBeenCalledWith(expected)
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('被保険者証情報を編集しました。')
    })

    it.each([
      ['effectivatedOn', '適用日を入力してください。'],
      ['insNumber', '被保険者証番号を入力してください。'],
      ['status', '認定区分を入力してください。', 'ltcs-ins-card-status'],
      ['issuedOn', '交付年月日を入力してください。'],
      ['insurerName', '保険者の名称を入力してください。'],
      ['insurerNumber', '保険者番号を入力してください。'],
      ['ltcsLevel', '要介護状態区分等を入力してください。'],
      ['certificatedOn', '認定年月日を入力してください。'],
      ['activatedOn', '認定の有効期間（開始）を入力してください。'],
      ['deactivatedOn', '認定の有効期間（終了）を入力してください。'],
      ['copayRate', '利用者負担の割合を入力してください。'],
      ['copayActivatedOn', '利用者負担適用期間（開始）を入力してください。'],
      ['copayDeactivatedOn', '利用者負担適用期間（終了）を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        mocked(ltcsInsCardStore.update).mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )

    /**
     * @TODO こちらの項目は vid, data属性 がユニークになっていないため、暫定版として最後の要素で検証している
     */
    it.each([
      ['ltcsInsCardServiceType', 'サービスの種類を入力してください。'],
      ['maxBenefitQuota', '種類支給限度基準額を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(ltcsInsCardStore.update).mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await jest.runAllTimers()

        const lastTargetWrapper = wrapper.findAll(`[data-${camelToKebab(key)}]`).at(-1)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect(lastTargetWrapper.find(`[data-${camelToKebab(key)}]`).text()).toContain(message)
        expect(lastTargetWrapper).toMatchSnapshot()
      }
    )
  })

  describe('card-actions', () => {
    const requiredPermissions: Permission[] = [Permission.createLtcsInsCards]
    const stubs = ['v-radio-group', 'z-ltcs-ins-card-form']

    it('should be rendered when session auth is system admin', async () => {
      mountComponent({ stubs })

      await setData(wrapper, { reason: 'A' })

      expect(wrapper).toContainElement('[data-card-actions]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, async () => {
      const permissions = requiredPermissions
      mountComponent({ auth: { permissions }, stubs })

      await setData(wrapper, { reason: 'A' })
      expect(wrapper).toContainElement('[data-card-actions]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, async () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, stubs })

      await setData(wrapper, { reason: 'A' })

      expect(wrapper).not.toContainElement('[data-card-actions]')
      unmountComponent()
    })
  })

  describe('error text in card-text', () => {
    const requiredPermissions: Permission[] = [Permission.createLtcsInsCards]
    const stubs = ['v-radio-group', 'z-ltcs-ins-card-form']

    it('should be not rendered when session auth is system admin', async () => {
      mountComponent({ stubs })

      await setData(wrapper, { reason: 'A' })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should be not rendered when the staff has permissions: ${requiredPermissions}`, async () => {
      const permissions = requiredPermissions
      mountComponent({ auth: { permissions }, stubs })

      await setData(wrapper, { reason: 'A' })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should be rendered when the staff does not have permissions: ${requiredPermissions}`, async () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, stubs })

      await setData(wrapper, { reason: 'A' })

      expect(wrapper.find('[data-card-text]')).toMatchSnapshot()
      unmountComponent()
    })
  })
})
