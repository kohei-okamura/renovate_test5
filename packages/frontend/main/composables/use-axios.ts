/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import { HttpStatusCode } from '~/models/http-status-code'
import { isAxiosError } from '~/support'
import { Refs } from '~/support/reactive'

type State = {
  errors: Record<string, string[]>
  progress: boolean
}

type AxiosUtils = Refs<State> & {
  withAxios: (f: () => Promise<void>, onError?: (error: Error) => void) => Promise<void>
}

export function useAxios (): AxiosUtils {
  const data = reactive<State>({
    errors: {},
    progress: false
  })
  const withAxios = async (f: () => Promise<void>, onError?: (error: Error) => void): Promise<void> => {
    try {
      data.progress = true
      return await f()
    } catch (error) {
      if (isAxiosError(error) && error.response?.status === HttpStatusCode.BadRequest) {
        data.errors = error.response.data?.errors ?? {}
      } else {
        const f = onError ?? ((error: Error) => { throw error })
        if (error instanceof Error) {
          f(error)
        }
      }
    } finally {
      data.progress = false
    }
  }
  return {
    ...toRefs(data),
    withAxios
  }
}
