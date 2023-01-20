/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assign } from '@zinger/helpers'

export type CssProps = {
  style?: Record<string, string>
  class?: Record<string, boolean>
}

export const isCssColor = (color?: string | false): boolean => {
  return !!color && !!color.match(/^(#|var\(--|(rgb|hsl)a?\()/)
}

export const createVuetifyStyleColorProps = (color?: string) => {
  const object: CssProps = {}
  if (isCssColor(color)) {
    object.style = { color: `${color}` }
  } else if (color) {
    const [colorName, colorModifier] = color.toString().trim().split(' ', 2) as (string | undefined)[]
    object.class = { [`${colorName}--text`]: true }
    if (colorModifier) {
      assign(object.class, { [`text--${colorModifier}`]: true })
    }
  }
  return object
}
