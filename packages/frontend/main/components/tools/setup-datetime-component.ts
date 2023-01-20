/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { h, SetupFunction } from '@nuxtjs/composition-api'
import { isEmpty } from '@zinger/helpers'
import { DateTime } from 'luxon'
import { usePlugins } from '~/composables/use-plugins'

export type DatetimeComponentProps = {
  value: string | DateTime
  alternative: string
}

type Params<T extends DatetimeComponentProps> = {
  format: string
  displayFormat: string | ((value: DateTime | undefined, props: T) => string)
}

export const DatetimeComponentPropDefs = {
  value: { type: [String, Object], default: undefined },
  alternative: { type: String, default: '-' }
}

/**
 * 指定したフォーマットで日時（or 日付 or 時刻）を表示するコンポーネント用
 */
export const setupDatetimeComponent = <T extends DatetimeComponentProps = DatetimeComponentProps> (
  params: Params<T>
): SetupFunction<T> => {
  const { format, displayFormat } = params
  return (props: T) => {
    const { $datetime } = usePlugins()
    const f = typeof displayFormat === 'string' ? (value: DateTime) => value.toFormat(displayFormat) : displayFormat
    return () => {
      const value = isEmpty(props.value) ? undefined : $datetime.parse(props.value)
      if (value) {
        const datetime = value?.toFormat(format) ?? ''
        const attrs = { datetime }
        return h('time', { attrs }, [f(value, props)])
      } else {
        return h('span', [props.alternative])
      }
    }
  }
}
