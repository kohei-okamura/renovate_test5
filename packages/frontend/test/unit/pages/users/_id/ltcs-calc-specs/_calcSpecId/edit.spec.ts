/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userLtcsCalcSpecStateKey } from '~/composables/stores/use-user-ltcs-calc-spec-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { Auth } from '~/models/auth'
import UserLtcsCalcSpecsEditPage from '~/pages/users/_id/ltcs-calc-specs/_calcSpecId/edit.vue'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUserLtcsCalcSpecResponseStub } from '~~/stubs/create-user-ltcs-calc-spec-response-stub'
import { createUserLtcsCalcSpecStoreStub } from '~~/stubs/create-user-ltcs-calc-spec-store-stub'
import { createUserLtcsCalcSpecStub } from '~~/stubs/create-user-ltcs-calc-spec-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/ltcs-calc-specs/_calcSpecId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('userLtcsCalcSpecs', 'users')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: UserLtcsCalcSpecsApi.Form = {
    effectivatedOn: '2020-10-26',
    locationAddition: LtcsUserLocationAddition.mountainousArea
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const stub = createUserLtcsCalcSpecStub()
  const userLtcsCalcSpecResponse = createUserLtcsCalcSpecResponseStub(stub.id)
  const userLtcsCalcSpecStore = createUserLtcsCalcSpecStoreStub(userLtcsCalcSpecResponse)
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    auth?: Partial<Auth>
    data?: Record<string, any>
    options?: MountOptions<Vue>
  }

  async function mountComponent ({ auth, data, options }: MountComponentParams = {}) {
    wrapper = mount(UserLtcsCalcSpecsEditPage, {
      ...provides(
        [userLtcsCalcSpecStateKey, userLtcsCalcSpecStore.state],
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
    jest.spyOn($api.userLtcsCalcSpecs, 'get').mockResolvedValue(createUserLtcsCalcSpecResponseStub(stub.id))
  })

  afterAll(() => {
    mocked($api.userLtcsCalcSpecs.get).mockReset()
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    beforeAll(async () => {
      await mountComponent({
        options: {
          stubs: ['v-card']
        }
      })
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.userLtcsCalcSpecs, 'update').mockResolvedValue(userLtcsCalcSpecResponse)
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($api.userLtcsCalcSpecs.update).mockReset()
    })

    it('should call $api.userLtcsCalcSpecs.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.userLtcsCalcSpecs.update).toHaveBeenCalledTimes(1)
      expect($api.userLtcsCalcSpecs.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        userId: stub.userId
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('介護保険サービス利用者別算定情報を編集しました。')
    })
  })
})
