/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { reactive, toRefs } from '@nuxtjs/composition-api'
import { Refs } from '~/support/reactive'

type Colors = {
  error: undefined
  info: undefined
  success: undefined
  warning: undefined
}

type Color = keyof Colors

export type SnackbarConfig = {
  color?: Color
  text: string
}

type Data = {
  config: SnackbarConfig
}

type SnackbarSugars = {
  readonly [K in keyof Colors]: (text: string) => void
}

export type SnackbarService = Refs<Data> & SnackbarSugars & {
  readonly show: (config: SnackbarConfig) => void
}

export function createSnackbarService (): SnackbarService {
  const colors: Color[] = ['error', 'info', 'success', 'warning']
  const defaultConfig: SnackbarConfig = {
    color: 'info',
    text: ''
  }
  const data = reactive<Data>({
    config: { ...defaultConfig }
  })
  const show = (config: SnackbarConfig): void => {
    data.config = { ...defaultConfig, ...config }
  }
  const sugars = Object.fromEntries(colors.map(color => [color, (text: string) => show({ color, text })]))
  return {
    ...toRefs(data),
    ...sugars as SnackbarSugars,
    show
  }
}
