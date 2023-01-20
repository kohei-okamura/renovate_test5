/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance, AxiosRequestConfig } from 'axios'
import MockAdapter from 'axios-mock-adapter'

type Method = 'any' | 'get' | 'post' | 'head' | 'delete' | 'patch' | 'put' | 'options' | 'list'

type GetRequests = (method?: Method) => AxiosRequestConfig[]

type GetLastRequest = (method?: Method) => AxiosRequestConfig

type Setup = (f: (adapter: MockAdapter) => void) => void

type MockAdapterWrapper = {
  getLastRequest: GetLastRequest
  getRequests: GetRequests
  setup: Setup
}

/**
 * {@link MockAdapter} を生成する.
 */
export function createAxiosMockAdapter (axios: AxiosInstance): MockAdapterWrapper {
  const adapter = new MockAdapter(axios)
  const getRequests: GetRequests = (method = 'any') => method === 'any'
    ? Object.keys(adapter.history).flatMap(key => adapter.history[key])
    : adapter.history[method]
  const getLastRequest: GetLastRequest = (method = 'any') => {
    const requests = getRequests(method)
    if (requests.length <= 0) {
      throw new Error('history is empty')
    }
    return requests.pop()!
  }
  const setup: Setup = f => {
    adapter.reset()
    f(adapter)
    adapter.onAny().reply(({ method, url }) => Promise.reject(new Error(`Unexpected request: ${method} ${url}`)))
  }
  return {
    getLastRequest,
    getRequests,
    setup
  }
}
