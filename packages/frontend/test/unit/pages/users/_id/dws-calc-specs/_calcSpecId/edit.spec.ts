/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userDwsCalcSpecStateKey } from '~/composables/stores/use-user-dws-calc-spec-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { Auth } from '~/models/auth'
import UserDwsCalcSpecsEditPage from '~/pages/users/_id/dws-calc-specs/_calcSpecId/edit.vue'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUserDwsCalcSpecResponseStub } from '~~/stubs/create-user-dws-calc-spec-response-stub'
import { createUserDwsCalcSpecStoreStub } from '~~/stubs/create-user-dws-calc-spec-store-stub'
import { createUserDwsCalcSpecStub } from '~~/stubs/create-user-dws-calc-spec-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-calc-specs/_calcSpecId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('userDwsCalcSpecs', 'users')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: UserDwsCalcSpecsApi.Form = {
    effectivatedOn: '2020-10-26',
    locationAddition: DwsUserLocationAddition.specifiedArea
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const stub = createUserDwsCalcSpecStub()
  const userDwsCalcSpecResponse = createUserDwsCalcSpecResponseStub(stub.id)
  const userDwsCalcSpecStore = createUserDwsCalcSpecStoreStub(userDwsCalcSpecResponse)
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    auth?: Partial<Auth>
    data?: Record<string, any>
    options?: MountOptions<Vue>
  }

  async function mountComponent ({ auth, data, options }: MountComponentParams = {}) {
    wrapper = mount(UserDwsCalcSpecsEditPage, {
      ...provides(
        [userDwsCalcSpecStateKey, userDwsCalcSpecStore.state],
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
    jest.spyOn($api.userDwsCalcSpecs, 'get').mockResolvedValue(createUserDwsCalcSpecResponseStub(stub.id))
  })

  afterAll(() => {
    mocked($api.userDwsCalcSpecs.get).mockReset()
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
      jest.spyOn($api.userDwsCalcSpecs, 'update').mockResolvedValue(userDwsCalcSpecResponse)
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($api.userDwsCalcSpecs.update).mockReset()
    })

    it('should call $api.userDwsCalcSpecs.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.userDwsCalcSpecs.update).toHaveBeenCalledTimes(1)
      expect($api.userDwsCalcSpecs.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        userId: stub.userId
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス利用者別算定情報を編集しました。')
    })
  })
})
