/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import materialColors from 'vuetify/es5/util/colors'

const primary = {
  base: '#335c81',
  lighten1: '#4176a6',
  lighten2: '#5c90be',
  lighten3: '#80a8cd',
  lighten4: '#a5c1db',
  lighten5: '#c9daea',
  lighten6: '#f1f6fa'
}
const accent = '#f2bb05'
const critical = '#f44336'

const red = materialColors.deepOrange.darken2
const green = materialColors.green.lighten1
const blue = materialColors.blue.darken1
const yellow = materialColors.amber.darken3

const textField = {
  background: primary.lighten6,
  border: primary.lighten4
}

export const colors = {
  primary: primary.base,
  secondary: primary.lighten2,
  accent,
  critical,
  danger: red,
  done: green,
  error: red,
  info: blue,
  inProgress: blue,
  success: green,
  textField,
  unavailable: red,
  warning: yellow
}
