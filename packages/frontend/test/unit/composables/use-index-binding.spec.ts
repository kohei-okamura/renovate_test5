/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import MockDate from 'mockdate'
import { useIndexBindings } from '~/composables/use-index-binding'
import { usePlugins } from '~/composables/use-plugins'
import { Api } from '~/services/api/core'
import { StaffsApi } from '~/services/api/staffs-api'
import { normalizeQuery } from '~/support/router/normalize-query'
import { parseRouteQuery } from '~/support/router/parse-route-query'
import { createStaffsStoreStub } from '~~/stubs/create-staffs-store-stub'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-index-binding', () => {
  const $router = createMockedRouter()
  let plugins: ReturnType<typeof usePlugins>
  let pagination: any

  function createIndexBindings (params?: Partial<Parameters<typeof useIndexBindings>[0]>) {
    const store = createStaffsStoreStub()
    pagination = store.state.pagination
    return useIndexBindings({
      ...{
        onQueryChange: params => store.getIndex(params),
        pagination,
        parseQuery: query => parseRouteQuery<Required<StaffsApi.GetIndexParams>>(query, {
          ...Api.getIndexParamOptions,
          officeId: { type: Number, default: '' },
          q: { type: String, default: '' },
          status: { type: Array, default: [StaffStatus.active] }
        }),
        restoreQueryParams: () => store.state.queryParams.value
      },
      ...params
    })
  }

  beforeAll(() => {
    setupComposableTest()
    const query = {
      restore: 'true'
    }
    plugins = createMockedPlugins({
      $router,
      $routes: createMockedRoutes({ query })
    })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  afterEach(() => {
    mocked($router.push).mockClear()
    mocked($router.replace).mockClear()
  })

  describe('$router.push should be called with expected parameter', () => {
    let indexBindings: {
      form: any
      paginate: (page: number) => any
      changeItemsPerPage: (itemsPerPage: number) => any
      refresh: () => any
      submit: () => any
    }

    beforeAll(() => {
      indexBindings = createIndexBindings()
    })

    it('when paginate is called', async () => {
      const { form, paginate } = indexBindings
      const page = 2
      const expected = normalizeQuery({ ...form, ...pagination.value, ...{ page } })
      await paginate(page)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ query: expected })
    })

    it('when changeItemsPerPage is called', async () => {
      const { form, changeItemsPerPage } = indexBindings
      const itemsPerPage = 30
      const expected = normalizeQuery({ ...form, ...pagination.value, ...{ page: 1, itemsPerPage } })
      await changeItemsPerPage(itemsPerPage)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ query: expected })
    })

    it('when submit is called', async () => {
      const { form, submit } = indexBindings
      const expected = normalizeQuery({ ...form, ...pagination.value, ...{ page: 1 } })
      await submit()
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ query: expected })
    })

    it('when refresh is called and does not have page', async () => {
      const dateStr = '2020/5/1 10:00:00'
      const timeStr = `${new Date(dateStr).getTime()}`
      MockDate.set(dateStr)
      const pagination = ref({
        desc: false,
        itemsPerPage: 10,
        sortBy: 'name'
      })
      const { form, refresh } = createIndexBindings({ pagination })
      const expected = {
        ...normalizeQuery({ ...form, ...pagination.value, ...{ page: 1 } }),
        ...{ refresh: timeStr }
      }
      await refresh()
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ query: expected })
      MockDate.reset()
    })

    it('when refresh is called and has page', async () => {
      const dateStr = '2020/5/1 19:00:00'
      const timeStr = `${new Date(dateStr).getTime()}`
      MockDate.set(dateStr)
      const page = 10
      const pagination = ref({
        desc: false,
        page,
        itemsPerPage: 10,
        sortBy: 'name'
      })
      const { form, refresh } = createIndexBindings({ pagination })
      const expected = {
        ...normalizeQuery({ ...form, ...pagination.value, ...{ page } }),
        ...{ refresh: timeStr }
      }
      await refresh()
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ query: expected })
      MockDate.reset()
    })
  })

  describe('$router.replace should be called with expected parameter', () => {
    it('when query.restore is truthy and does not have restore query', () => {
      createIndexBindings({ restoreQueryParams: () => undefined })
      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith({ query: {} })
    })

    it('when query.restore is truthy and has restore query', () => {
      const restoreQuery = { page: 1, itemsPerPage: 10 }
      createIndexBindings({ restoreQueryParams: () => restoreQuery })
      const expected = normalizeQuery(restoreQuery)
      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith({ query: expected })
    })
  })
})
