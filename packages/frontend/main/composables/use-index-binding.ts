/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, Ref, watch } from '@nuxtjs/composition-api'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { normalizeQuery } from '~/support/router/normalize-query'

type P = Record<string, Primitive | Primitive[]>
type Q = Record<string, string | (string | null)[]>
type MutablePaginationParameters = Partial<Pick<Pagination, 'page' | 'itemsPerPage'>>

const createGetIndexParams = <T extends P> (
  form: T,
  pagination: Pagination,
  { page, itemsPerPage }: MutablePaginationParameters
): Q => {
  return normalizeQuery({
    ...form,
    desc: pagination.desc,
    page,
    itemsPerPage: itemsPerPage ?? pagination.itemsPerPage,
    sortBy: pagination.sortBy
  })
}

type Params<T extends P> = {
  pagination: Ref<Pagination>
  onQueryChange: (params: T) => void | Promise<void>
  parseQuery: (query: Q) => T
  restoreQueryParams: () => T | undefined
}

export const useIndexBindings = <T extends P> (params: Params<T>) => {
  const { $route, $router, $routes } = usePlugins()
  const form = reactive({
    ...params.parseQuery($routes.query.value)
  })
  watch(
    $routes.query,
    async q => {
      if (q.restore) {
        const query = normalizeQuery(params.restoreQueryParams() ?? {})
        await catchErrorStack(() => $router.replace({ query }))
      } else {
        const parsedQuery = params.parseQuery(q)
        Object.assign(form, parsedQuery)
        await params.onQueryChange(parsedQuery)
      }
    },
    { immediate: true }
  )
  const request = async (query: Q) => {
    const hash = $route?.hash
    await catchErrorStack(() => $router.push({ query, hash }))
  }
  return {
    form,
    paginate: async (page: number) => {
      await request(createGetIndexParams(form, params.pagination.value, { page }))
    },
    changeItemsPerPage: async (itemsPerPage: number) => {
      // 表示件数が変更された場合は1ページに戻す
      await request(createGetIndexParams(form, params.pagination.value, { page: 1, itemsPerPage }))
    },
    refresh: async () => {
      const p = params.pagination.value
      await request({
        ...createGetIndexParams(form, p, { page: p.page ?? 1 }),
        refresh: `${(new Date()).getTime()}`
      })
    },
    submit: async () => {
      await request(createGetIndexParams(form, params.pagination.value, { page: 1 }))
    }
  }
}
