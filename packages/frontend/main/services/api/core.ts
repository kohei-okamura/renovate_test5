/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosResponse } from 'axios'
import { Pagination } from '~/models/pagination'
import { ParamOptions } from '~/support/router/parse-route-query'

const endpoint = (...segments: (string | number | undefined)[]): string => {
  return '/api/' + segments.filter(x => x !== undefined).join('/')
}

const extract = async <T> (response: Promise<AxiosResponse<T>>): Promise<T> => {
  const { data } = await response
  return data
}

export const api = {
  endpoint,
  extract
}

export namespace Api {
  export type Form<T> = {
    form: DeepPartial<T>
  }

  export type Id = {
    id: number
  }

  export type Token = {
    token: string
  }

  export type Url = {
    url: string
  }

  export type CreateParams<T> = Form<T>

  export type DeleteParams = Id

  export type GetParams = Id

  export type GetIndexParams = {
    all?: boolean | ''
    desc?: boolean | ''
    itemsPerPage?: number | ''
    page?: number | ''
    sortBy?: string | ''
  }

  export const getIndexParamOptions: ParamOptions<Required<GetIndexParams>> = {
    all: { type: Boolean },
    desc: { type: Boolean },
    itemsPerPage: { type: Number },
    page: { type: Number, default: 1 },
    sortBy: { type: String }
  }

  export type GetIndexResponse<T> = {
    list: T[]
    pagination: Pagination
  }

  export type UpdateParams<T> = Id & Form<T>

  export type Create<Form, Params = CreateParams<Form>, Response = void> = {
    create (params: Params): Promise<Response>
  }

  export type Delete<Params = DeleteParams> = {
    delete (params: Params): Promise<void>
  }

  export type Get<Response, Params = GetParams> = {
    get (params: Params): Promise<Response>
  }

  export type GetIndex<Params, Response> = {
    getIndex (params?: Params): Promise<Response>
  }

  export type Update<Form, Params = UpdateParams<Form>, Response = void> = {
    update (params: Params): Promise<Response>
  }
}
