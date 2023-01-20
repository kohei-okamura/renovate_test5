/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stubs, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { staffsStoreKey } from '~/composables/stores/use-staffs-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import StaffsIndexPage from '~/pages/staffs/index.vue'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { ROLE_ID_MAX } from '~~/stubs/create-role-stub'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createStaffsStoreStub } from '~~/stubs/create-staffs-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { createParamsToQuery } from '~~/test/helpers/create-params-to-query'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')

describe('pages/staffs/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $api = createMockedApi('invitations')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const mocks = {
    $api,
    $router,
    $snackbar
  }
  const staffs = createStaffStubs(20)
  const staffsStore = createStaffsStoreStub({ staffs })

  let wrapper: Wrapper<Vue>

  function mountComponent (query: RouteQuery = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    const $routes = createMockedRoutes({ query })
    const stubs: Stubs = {
      'z-invitation-form': true
    }
    wrapper = mount(StaffsIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [staffsStoreKey, staffsStore]
      ),
      mocks: {
        ...mocks,
        $routes
      },
      stubs
    })
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

  beforeEach(() => {
    mocked(staffsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call staffsStore.getIndex', () => {
    mountComponent({ page: '1' })

    expect(staffsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(staffsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))

    unmountComponent()
  })

  it.each([
    [{}, { officeId: '', status: [2], q: '' }],
    [{ officeId: 2, status: [1], q: '' }],
    [{ officeId: 2, status: [2], q: '' }],
    [{ officeId: 2, status: [9], q: '' }],
    [{ officeId: 2, status: [1, 2, 9], q: '' }],
    [{ officeId: 2, q: 'keyword' }, { officeId: 2, status: [2], q: 'keyword' }],
    [{ officeId: 2, q: 'keyword', status: [1, 2, 9] }]
  ])('should call staffsStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = createParamsToQuery(params)
    mountComponent(query)

    expect(staffsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(staffsStore.getIndex).toHaveBeenCalledWith(createFormData(expected))

    unmountComponent()
  })

  describe('invitation form', () => {
    beforeEach(() => {
      jest.spyOn($api.invitations, 'create').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
      mocked($snackbar.success).mockReset()
      mocked($api.invitations.create).mockReset()
    })

    it('should not be displayed until click the FAB', () => {
      const formWrapper = wrapper.findComponent({ ref: 'invitationForm' })
      expect(formWrapper.attributes()).not.toHaveProperty('dialog')
    })

    it('should be displayed after click the FAB', async () => {
      const formWrapper = wrapper.findComponent({ ref: 'invitationForm' })
      await click(() => wrapper.findComponent({ ref: 'fab' }))
      expect(formWrapper.attributes().dialog).toBe('true')
    })

    it('should call $api.invitations.create when submit event emitted', () => {
      const formWrapper = wrapper.findComponent({ ref: 'invitationForm' })
      const form = {
        emails: ['john@example.com', 'mary@example.com'],
        officeIds: [OFFICE_ID_MIN],
        roleIds: [ROLE_ID_MAX]
      }
      expect($api.invitations.create).not.toHaveBeenCalled()

      formWrapper.vm.$emit('submit', form)

      expect($api.invitations.create).toHaveBeenCalledTimes(1)
      expect($api.invitations.create).toHaveBeenCalledWith({ form })
    })
  })
})
