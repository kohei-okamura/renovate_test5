/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZInvitationForm from '~/components/domain/staff/z-invitation-form.vue'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOffices } from '~/composables/use-offices'
import { useRoles } from '~/composables/use-roles'
import { InvitationsApi } from '~/services/api/invitations-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createInvitationStub } from '~~/stubs/create-invitation-stub'
import { OFFICE_GROUP_ID_MIN } from '~~/stubs/create-office-group-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { ROLE_ID_MIN } from '~~/stubs/create-role-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseRolesStub } from '~~/stubs/create-use-roles-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-office-groups')
jest.mock('~/composables/use-roles')

describe('z-invitation-form.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('invitations')
  const $form = createMockedFormService()
  const form: InvitationsApi.Form = {
    emails: ['john@example.com'],
    officeIds: [OFFICE_ID_MIN],
    officeGroupIds: [OFFICE_GROUP_ID_MIN],
    roleIds: [ROLE_ID_MIN]
  }
  const mocks = {
    $api,
    $form
  }
  const propsData = {
    dialog: true,
    errors: {},
    progress: false,
    value: form
  }
  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZInvitationForm, {
      mocks,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
    mocked(useRoles).mockReturnValue(createUseRolesStub())
  })

  afterAll(() => {
    mocked(useRoles).mockReset()
    mocked(useOfficeGroups).mockReset()
    mocked(useOffices).mockReset()
  })

  beforeEach(() => {
    const invitation = createInvitationStub(1, 'x'.repeat(60))
    jest.spyOn($api.invitations, 'get').mockResolvedValue({ invitation })
  })

  afterEach(() => {
    mocked(useRoles).mockClear()
    mocked($api.invitations.get).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<InvitationsApi.Form> = {}): Promise<void> {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when emails is empty', async () => {
      await validate({
        emails: ['']
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-emails] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when emails contains an invalid email address', async () => {
      await validate({
        emails: ['hoge@example.com', 'this is not an email address']
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-emails] .v-messages').text()).toBe('有効なメールアドレスを入力してください。')
    })

    it('should fail when name is longer than 255 characters', async () => {
      await validate({
        emails: [
          'hoge@example.com',
          'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'
        ]
      })
      expect(observer).toBePassed()

      await validate({
        emails: [
          'hoge@example.com',
          'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'
        ]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-emails] .v-messages').text()).toBe('255文字以内で入力してください。')
    })

    it('should fail when officeIds is empty', async () => {
      await validate({
        officeIds: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-office-ids] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when roleIds is empty', async () => {
      await validate({
        roleIds: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-role-ids] .v-messages').text()).toBe('入力してください。')
    })
  })

  describe('close', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should close dialog when cancel button clicked', async () => {
      await click(() => wrapper.find('[data-cancel]'))
      const events = wrapper.emitted('update:dialog') ?? []
      expect(events).toHaveLength(1)
      expect(events[0]).toStrictEqual([false])
    })
  })
})
