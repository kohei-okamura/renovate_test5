/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import { NuxtContext } from '~/models/nuxt'
import { Refs } from '~/support/reactive'

type Colors = {
  error: undefined
  info: undefined
  success: undefined
  warning: undefined
}

type Color = keyof Colors

export type AlertConfig = {
  color?: Color
  title?: string
  text?: string
}

type Data = {
  config: AlertConfig
  alertShow: boolean
}

type AlertSugars = {
  readonly [K in keyof Colors]: (title: string, text?: string) => void
}

export type AlertService = Refs<Data> & AlertSugars & {
  readonly show: (config: AlertConfig) => void
}

export function createAlertService ({ app }: NuxtContext): AlertService {
  const colors: Color[] = ['error', 'info', 'success', 'warning']
  const defaultConfig: AlertConfig = {
    color: 'info',
    title: '',
    text: ''
  }
  const data = reactive<Data>({
    config: { ...defaultConfig },
    alertShow: false
  })
  app.router!.afterEach(() => {
    data.alertShow = false
  })
  const show = (config: AlertConfig): void => {
    data.config = { ...defaultConfig, ...config }
    data.alertShow = true
  }
  const sugars =
    Object.fromEntries(colors.map(color => [color, (title: string, text: string) => show({ color, title, text })]))
  return {
    ...toRefs(data),
    ...sugars as AlertSugars,
    show
  }
}
