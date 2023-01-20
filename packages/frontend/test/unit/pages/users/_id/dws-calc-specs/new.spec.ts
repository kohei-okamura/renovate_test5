/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import UserDwsCalcSpecsNewPage from '~/pages/users/_id/dws-calc-specs/new.vue'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-calc-specs/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('userDwsCalcSpecs')
  const $back = createMockedBack()
  const $route = createMockedRoute({ params: { id: '2' } })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: UserDwsCalcSpecsApi.Form = {
    effectivatedOn: '2020-08-20',
    locationAddition: DwsUserLocationAddition.specifiedArea
  }
  const mocks = {
    $api,
    $back,
    $form,
    $route,
    $router,
    $snackbar
  }
  const userId = 1
  const userStore = createUserStoreStub(createUserResponseStub(userId))

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(UserDwsCalcSpecsNewPage, {
      ...provides(
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    beforeAll(() => {
      jest.spyOn($api.userDwsCalcSpecs, 'create').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    beforeEach(() => {
      jest.clearAllMocks()
    })

    it('should call $api.userDwsCalcSpecs.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.userDwsCalcSpecs.create).toHaveBeenCalledTimes(1)
      expect($api.userDwsCalcSpecs.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス利用者別算定情報を登録しました。')
    })
  })
})
