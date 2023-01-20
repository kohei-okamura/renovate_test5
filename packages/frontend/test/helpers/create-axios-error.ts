/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios'

/**
 * AxiosError を生成する.
 */
type CreateAxiosError = {
  (statusCode: number): AxiosError
  <T> (statusCode: number, data: T): AxiosError<T>
}
export const createAxiosError: CreateAxiosError = <T> (statusCode: number, data?: T) => {
  const config: AxiosRequestConfig = {}
  const response: AxiosResponse<T | undefined> = {
    config,
    data,
    headers: {},
    status: statusCode,
    statusText: ''
  }
  return Object.assign(new Error(`Request failed with status code ${statusCode}`), {
    config,
    isAxiosError: true,
    response,
    toJSON: () => ({})
  })
}
