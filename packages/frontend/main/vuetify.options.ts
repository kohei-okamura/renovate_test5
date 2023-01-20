/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Options } from '@nuxtjs/vuetify'
import ja from 'vuetify/src/locale/ja'
import { colors } from '~/colors'

const options: DeepPartial<Options> = {
  icons: {
    iconfont: 'mdiSvg'
  },
  lang: {
    locales: { ja },
    current: 'ja'
  },
  theme: {
    dark: false,
    themes: {
      light: {
        primary: colors.primary,
        secondary: colors.secondary,
        accent: colors.accent,
        info: colors.info,
        warning: colors.warning,
        error: colors.error,
        success: colors.success,
        danger: colors.danger
      }
    }
  }
}

export default options
