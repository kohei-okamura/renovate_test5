/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { shiftImportsStoreKey } from '~/composables/stores/use-shift-imports-store'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import ShiftImportsNewPage from '~/pages/shift-imports/new.vue'
import { DownloadService } from '~/services/download-service'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createShiftImportsStoreStub } from '~~/stubs/create-shift-imports-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')

describe('pages/shift-imports/new.vue', () => {
  // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
  // 参考:https://github.com/jsdom/jsdom/issues/1695
  Element.prototype.scrollIntoView = noop
  type Component = Vue & {
    download: (args: any) => void
    upload: (args: any) => void
  }

  const { mount } = setupComponentTest()
  const $api = createMockedApi('shifts')
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const mocks = {
    $api,
    $download,
    $form
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const shiftImportsStore = createShiftImportsStoreStub()

  let wrapper: Wrapper<Component>

  function mountComponent (options: Partial<MountOptions<Component>> = {}) {
    wrapper = mount(ShiftImportsNewPage, {
      ...options,
      ...provides(
        [shiftImportsStoreKey, shiftImportsStore]
      ),
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

  describe('download', () => {
    const form: any = {}
    const job = createJobStub('10', JobStatus.waiting)

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.shifts, 'createTemplate').mockResolvedValue({ job })
      jest.spyOn($download, 'uri').mockResolvedValue()
    })

    afterEach(() => {
      mocked($download.uri).mockReset()
      mocked($api.shifts.createTemplate).mockReset()
    })

    it('should call useJobWithNotification.execute', async () => {
      await wrapper.vm.download(form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          linkToOnFailure: '/shift-imports/new',
          text: expect.objectContaining({
            progress: '勤務シフト一括登録用ファイルのダウンロードを準備中です...',
            success: '勤務シフト一括登録用ファイルのダウンロードを開始します',
            failure: '勤務シフト一括登録用ファイルのダウンロードに失敗しました'
          })
        }),
        process: expect.any(Function),
        success: expect.any(Function)
      }))
    })

    it('should call $api.shifts.createTemplate', async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await wrapper.vm.download(form)

      expect($api.shifts.createTemplate).toHaveBeenCalledTimes(1)
      expect($api.shifts.createTemplate).toHaveBeenCalledWith({ form })
      mocked(execute).mockReset()
    })

    it('should start downloading when process completed successfully', async () => {
      const localJob = createJobStub(job.token, JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)(localJob)
      })

      await wrapper.vm.download(form)

      expect($download.uri).toHaveBeenCalledTimes(1)
      expect($download.uri).toHaveBeenCalledWith(localJob.data.uri, localJob.data.filename)
      mocked(execute).mockReset()
    })
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
      jest.spyOn($api.shifts, 'import').mockResolvedValue({ job })
      jest.spyOn($download, 'uri').mockResolvedValue()
    })

    afterEach(() => {
      mocked($download.uri).mockReset()
      mocked($api.shifts.import).mockReset()
    })

    it('should call useJobWithNotification.execute', async () => {
      await wrapper.vm.upload(form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          featureName: '勤務シフト一括登録',
          linkToOnFailure: '/shift-imports/new'
        }),
        process: expect.any(Function),
        success: expect.any(Function),
        failure: expect.any(Function)
      }))
    })

    it('should call $api.shifts.import', async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await wrapper.vm.upload(form)

      expect($api.shifts.import).toHaveBeenCalledTimes(1)
      expect($api.shifts.import).toHaveBeenCalledWith({ form })
      mocked(execute).mockReset()
    })
  })
})
