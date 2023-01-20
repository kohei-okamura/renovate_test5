/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, ref, toRefs } from '@nuxtjs/composition-api'
import { Deferred } from 'ts-deferred'
import { Refs } from '~/support/reactive'

type ConfirmDialogOptions = {
  color: string
  message: string
  negative: string
  positive: string
  title: string
}

export type ConfirmDialogParams = {
  color?: string
  message: string
  negative?: string
  positive: string
  title?: string
}

type Data = {
  active: boolean
  options: ConfirmDialogOptions
}

export type ConfirmDialogService = Refs<Data> & Readonly<{
  resolve (result: boolean): void
  show (params: ConfirmDialogParams): Promise<boolean>
  hide (): void
}>

const DEFAULT_OPTIONS: ConfirmDialogOptions = {
  color: 'primary',
  message: '',
  negative: 'キャンセル',
  positive: '',
  title: ''
}

export function createConfirmDialogService (): ConfirmDialogService {
  const data = reactive<Data>({
    active: false,
    options: { ...DEFAULT_OPTIONS }
  })
  const deferred = ref(new Deferred<boolean>())
  return {
    ...toRefs(data),
    resolve (result) {
      deferred.value.resolve(result)
    },
    show (params) {
      const x = new Deferred<boolean>()
      deferred.value = x
      data.options = { ...DEFAULT_OPTIONS, ...params }
      data.active = true
      return x.promise
    },
    hide () {
      data.active = false
    }
  }
}
