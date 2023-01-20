/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { camelToKebab, noop } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  billingBulkUpdateStoreKey,
  useBillingBulkUpdateStore
} from '~/composables/stores/use-billing-bulk-update-store'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { HttpStatusCode } from '~/models/http-status-code'
import UserBillingBulkUpdateNewPage from '~/pages/user-billing-bulk-update/new.vue'
import { SnackbarService } from '~/services/snackbar-service'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')

describe('pages/user-billing-bulk-update/new.vue', () => {
  // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
  Element.prototype.scrollIntoView = noop
  type Component = Vue & {
    upload: (args: any) => void
  }

  const { mount } = setupComponentTest()
  const $api = createMockedApi('withdrawalTransactions')
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $api,
    $form,
    $snackbar
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const userBillingBulkUpdateStore = useBillingBulkUpdateStore()

  let wrapper: Wrapper<Component>

  function mountComponent (options: Partial<MountOptions<Component>> = {}) {
    wrapper = mount(UserBillingBulkUpdateNewPage, {
      ...options,
      ...provides(
        [billingBulkUpdateStoreKey, userBillingBulkUpdateStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useJobWithNotification).mockReturnValue({ execute })
  })

  afterAll(() => {
    mocked(useJobWithNotification).mockRestore()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('upload', () => {
    const form: any = {}
    const job = createJobStub('20', JobStatus.waiting)

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($api.withdrawalTransactions.import).mockReset()
    })

    it('should call useJobWithNotification.execute', async () => {
      jest.spyOn($api.withdrawalTransactions, 'import').mockResolvedValue({ job })
      await wrapper.vm.upload(form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          featureName: '全銀ファイルアップロード',
          linkToOnFailure: '/user-billing-bulk-update/new'
        }),
        process: expect.any(Function),
        success: expect.any(Function),
        failure: expect.any(Function)
      }))
    })

    it.each([
      ['file', 'ファイルが不正です。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(execute).mockImplementation(async ({ process }) => {
          await process()
        })
        jest.spyOn($api.withdrawalTransactions, 'import')
          .mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          }))

        await wrapper.vm.upload({ form: { file: 'test' } })
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)
        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
        mocked(execute).mockReset()
      }
    )

    it('should call $api.withdrawalTransactions.import', async () => {
      jest.spyOn($api.withdrawalTransactions, 'import').mockResolvedValue({ job })

      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await wrapper.vm.upload(form)

      expect($api.withdrawalTransactions.import).toHaveBeenCalledTimes(1)
      expect($api.withdrawalTransactions.import).toHaveBeenCalledWith({ form })
      mocked(execute).mockReset()
    })
  })
})
