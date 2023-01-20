/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import LtcsBillingsNewPage from '~/pages/ltcs-billings/new.vue'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { createJobStub } from '~~/stubs/create-job-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')

describe('pages/ltcs-billings/new.vue', () => {
  type Form = LtcsBillingsApi.CreateForm

  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsBillings')
  const $form = createMockedFormService()
  const mocks = {
    $api,
    $form
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(LtcsBillingsNewPage, {
      ...options,
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useJobWithNotification).mockReturnValue({ execute })
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockRestore()
    mocked(useJobWithNotification).mockRestore()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    const form: Form = {
      officeId: OFFICE_ID_MIN,
      transactedIn: '2020-10',
      providedIn: '2020-10'
    }
    const job = createJobStub('10', JobStatus.waiting)

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.ltcsBillings, 'create').mockResolvedValue({ job })
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })
    })

    afterEach(() => {
      mocked(execute).mockReset()
      mocked($api.ltcsBillings.create).mockReset()
    })

    it('should call useJobWithNotification.execute', async () => {
      await wrapper.vm.submit(form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          text: expect.objectContaining({
            progress: '介護保険サービス請求の作成を開始します',
            success: '介護保険サービス請求の作成に成功しました',
            failure: '介護保険サービス請求の作成に失敗しました'
          })
        }),
        process: expect.any(Function)
      }))
    })

    it('should call $api.ltcsBillings.create', async () => {
      await wrapper.vm.submit(form)

      expect($api.ltcsBillings.create).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillings.create).toHaveBeenCalledWith({ form })
    })
  })
})
