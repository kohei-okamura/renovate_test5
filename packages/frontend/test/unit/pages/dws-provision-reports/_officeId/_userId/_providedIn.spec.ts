/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { DateTime } from 'luxon'
import Vue from 'vue'
import { DwsProvisionReportStore } from '~/composables/stores/use-dws-provision-report-store'
import { dwsProvisionReportsStoreKey } from '~/composables/stores/use-dws-provision-reports-store'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { NuxtContext } from '~/models/nuxt'
import DwsProvisionReportStoreProviderPage from '~/pages/dws-provision-reports/_officeId/_userId/_providedIn.vue'
import { createDwsProvisionReportDigestStubs } from '~~/stubs/create-dws-provision-report-digest-stub'
import { createDwsProvisionReportStoreStub } from '~~/stubs/create-dws-provision-report-store-stub'
import { createDwsProvisionReportsStoreStub } from '~~/stubs/create-dws-provision-reports-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-provision-reports/_officeId/_userId/_providedIn.vue', () => {
  type RouterParameters = {
    officeId: string
    userId: string
    providedIn: string
  }
  const createRouterParams = (params?: Partial<RouterParameters>): RouterParameters => {
    return {
      ...{
        officeId: '10',
        userId: '20',
        providedIn: '2021-02'
      },
      ...params
    }
  }
  const createApiParams = (params: RouterParameters) => {
    return {
      officeId: +params.officeId,
      userId: +params.userId,
      providedIn: params.providedIn
    }
  }
  const { mount } = setupComponentTest()
  const dwsProvisionReports = createDwsProvisionReportDigestStubs(20)
  const dwsProvisionReportsStore = createDwsProvisionReportsStoreStub({ dwsProvisionReports })
  const params = createRouterParams()
  const $route = createMockedRoute({ params })
  const mocks = {
    $route
  }
  let reportStore: DwsProvisionReportStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsProvisionReportStoreProviderPage, {
      ...options,
      ...provides(
        [dwsProvisionReportsStoreKey, dwsProvisionReportsStore]
      ),
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    reportStore = createDwsProvisionReportStoreStub()
    jest.spyOn(reportStore, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call dwsProvisionReportStore.get', () => {
    expect(reportStore.get).toHaveBeenCalledTimes(1)
    expect(reportStore.get).toHaveBeenCalledWith(createApiParams(params))
  })

  it('should call dwsProvisionReportsStore.getIndex', () => {
    const { officeId, providedIn } = createApiParams(params)
    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledWith({ officeId, providedIn })
  })

  describe('validate', () => {
    beforeAll(() => {
      jest.spyOn(DateTime, 'local').mockReturnValue(DateTime.fromISO('2021-01-01', { locale: 'ja' }))
    })

    it('should return true when valid parameters given', () => {
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric officeId given', () => {
      const params = createRouterParams({ officeId: 'abc' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when officeId not given', () => {
      const params = {
        userId: '20',
        providedIn: '2021-02'
      }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when non-numeric userId given', () => {
      const params = createRouterParams({ userId: 'abc' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when userId not given', () => {
      const params = {
        officeId: '10',
        providedIn: '2021-02'
      }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when non-date providedIn given', () => {
      const params = createRouterParams({ providedIn: '2021-00' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when 4 months or more ahead providedIn given', () => {
      const providedIn = DateTime.local().plus({ months: 4 }).toFormat(ISO_MONTH_FORMAT)
      const params = createRouterParams({ providedIn })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when providedIn not given', () => {
      const params = {
        officeId: '10',
        userId: '20'
      }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
