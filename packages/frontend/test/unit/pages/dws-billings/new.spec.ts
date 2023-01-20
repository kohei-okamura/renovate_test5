/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import DwsBillingsNewPage from '~/pages/dws-billings/new.vue'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { createJobStub } from '~~/stubs/create-job-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')

describe('pages/dws-billings/new.vue', () => {
  type Form = DwsBillingsApi.CreateForm

  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsBillings')
  const $form = createMockedFormService()
  const mocks = {
    $api,
    $form
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(DwsBillingsNewPage, {
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
    mocked(useOffices).mockReset()
    mocked(useJobWithNotification).mockReset()
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
      jest.spyOn($api.dwsBillings, 'create').mockResolvedValue({ job })
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })
    })

    afterEach(() => {
      mocked(execute).mockReset()
      mocked($api.dwsBillings.create).mockReset()
    })

    it('should call useJobWithNotification.execute', async () => {
      await wrapper.vm.submit(form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          text: expect.objectContaining({
            progress: '障害福祉サービス請求の作成を開始します',
            success: '障害福祉サービス請求の作成に成功しました',
            failure: '障害福祉サービス請求の作成に失敗しました'
          })
        }),
        process: expect.any(Function)
      }))
    })

    it('should call $api.dwsBillings.create', async () => {
      await wrapper.vm.submit(form)

      expect($api.dwsBillings.create).toHaveBeenCalledTimes(1)
      expect($api.dwsBillings.create).toHaveBeenCalledWith({ form })
    })
  })
})
