/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NuxtAxiosInstance } from '@nuxtjs/axios'
import MockAdapter from 'axios-mock-adapter'
import { SEEDS } from '~~/stubs'
import { createFaker } from '~~/stubs/fake'

/**
 * axios-mock-adapter 設定関数.
 */
export type StubFunction = (mockAdapter: MockAdapter) => void | MockAdapter

/**
 * axios-mock-adapter のインスタンスを生成する.
 */
export const createMockAdapter = ($axios: NuxtAxiosInstance) => {
  const options = {
    delayResponse: 200
  }
  return new MockAdapter($axios, options)
}

/**
 * 非同期ジョブのトークン長.
 */
const JOB_TOKEN_LENGTH = 60

/**
 * 非同期ジョブ API スタブ用定義.
 */
type JobStubState = {
  id: number
  token: string
  readonly prefix: string
  readonly regex: RegExp
}
export const createJobStubState = (prefix: string, regex: RegExp): JobStubState => ({
  id: 1,
  token: '',
  prefix,
  regex
})
export const updateJobStubState = (state: JobStubState) => {
  const length = JOB_TOKEN_LENGTH - state.prefix.length - 1 // プレフィクス + ハイフン + ランダム文字列
  const faker = createFaker(SEEDS[state.id - 1])
  state.id = ++state.id
  state.token = `${state.prefix}-${faker.randomString(length)}`
}
