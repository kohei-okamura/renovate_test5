/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers/lib/keys'
import { Api } from '~/services/api/core'
import { CreateStubs } from '~~/stubs/index'

function convertForJson<T extends Record<string, any>> (x: T): any {
  if (typeof x === 'undefined' || x === null) {
    return null
  }
  if (typeof x === 'object') {
    keys(x).forEach(key => Object.assign(x, { [key]: convertForJson(x[key]) }))
  }
  return x
}

const filtered = <T> (origin: T[], fn?: (v: T) => boolean) => {
  return fn ? origin.filter(fn) : origin
}

export function createIndexResponse<T> (
  params: Api.GetIndexParams,
  count: number,
  createStubs: CreateStubs<T>,
  filter?: (v: T) => boolean
): Api.GetIndexResponse<T> {
  if (params.all ?? false) {
    const list = filtered(createStubs(), filter)
    return convertForJson({
      list,
      pagination: {
        count,
        desc: params.desc ?? false,
        itemsPerPage: count,
        page: 1,
        pages: 1,
        sortBy: params.sortBy ?? ''
      }
    })
  } else {
    const itemsPerPage = +(params.itemsPerPage ?? 10)
    const page = +(params.page ?? 1)
    const pages = Math.ceil(count / itemsPerPage)
    const desc = params.desc ?? false
    const sortBy = params.sortBy ?? ''
    const skip = (page - 1) * itemsPerPage
    const list = filtered(createStubs(itemsPerPage, skip), filter)
    return convertForJson({ list, pagination: { count, desc, itemsPerPage, page, pages, sortBy } })
  }
}
