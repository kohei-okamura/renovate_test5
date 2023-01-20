/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NuxtConfig } from '@nuxt/types'
import { Options as TypescriptBuildOptions } from '@nuxt/typescript-build'
import { AxiosOptions } from '@nuxtjs/axios'
import { ModuleOptions as GoogleFontsOptions } from '@nuxtjs/google-fonts'
import { Options as VuetifyOptions } from '@nuxtjs/vuetify'
import * as glob from 'glob'
import * as path from 'path'

type Environment = 'development' | 'local' | 'production'

const getEnvironment = () => (process.env.NODE_ENV ?? 'production') as Environment
const environment = getEnvironment()
const isProduction = environment === 'production'
const isLocal = environment === 'local'
const isDevelopment = environment === 'development'

/**
 * Plugins.
 */
const basePlugins = [
  '~/plugins/axios',
  '~/plugins/back',
  '~/plugins/components',
  '~/plugins/google-maps',
  '~/plugins/icons',
  '~/plugins/luxon',
  '~/plugins/nuxt-composition-api',
  '~/plugins/services',
  '~/plugins/stores',
  '~/plugins/vee-validate'
]
const developmentPlugins = [
  '~/plugins/axios-dev'
]

/**
 * fork-ts-checker-webpack-plugin configuration.
 *
 * @see https://github.com/TypeStrong/fork-ts-checker-webpack-plugin
 */
const typeCheck: TypescriptBuildOptions['typeCheck'] = {
  async: true,
  eslint: {
    enabled: true,
    files: '**/*.{js,ts,vue}'
  },
  typescript: {
    configFile: path.resolve(__dirname, 'tsconfig.json'),
    enabled: true,
    extensions: {
      vue: true
    },
    memoryLimit: 4096
  }
}

const googleFonts: GoogleFontsOptions = {
  families: { Rajdhani: [700], Roboto: [300, 400, 500, 700] },
  display: 'swap',
  download: true,
  inject: true
}

/**
 * Nuxt configuration.
 */
type Config = NuxtConfig & {
  axios: AxiosOptions
  vuetify: VuetifyOptions
}
const config: Config = {
  build: {
    babel: {
      presets: [
        ['@nuxt/babel-preset-app', {
          corejs: { version: 3 },
          modern: isLocal || isDevelopment
        }]
      ]
    },
    extend: config => {
      config.devtool = isProduction ? undefined : 'source-map'
      // Overwrite aliases
      Object.assign(config.resolve!.alias!, {
        '~~': __dirname
      })
    },
    cache: isLocal || isDevelopment,
    extractCSS: isProduction,
    hardSource: isLocal || isDevelopment,
    parallel: isLocal || isDevelopment
  },
  buildModules: [
    ['@nuxt/typescript-build', { typeCheck }],
    ['@nuxtjs/google-fonts', googleFonts],
    '@nuxtjs/composition-api/module',
    '@nuxtjs/vuetify'
  ],
  css: [
    '~/assets/style/app.scss'
  ],
  env: {
    apartmentMaxLength: '200',
    cityMaxLength: '200',
    consumptionTax: '1.1',
    emailMaxLength: '255',
    employeeNumberMaxLength: '20',
    familyNameMaxLength: '100',
    givenNameMaxLength: '100',
    googleMapsApiKey: process.env.GOOGLE_MAPS_API_KEY || 'AIzaSyDw2iJEyI1EuNt7XRCNfyDc_SzHvYyAk38',
    googleMapsApiVersion: process.env.GOOGLE_MAPS_API_VERSION || 'quarterly',
    passwordMinLength: '8',
    phoneticFamilyNameMaxLength: '100',
    phoneticGivenNameMaxLength: '100',
    reducedConsumptionTax: '1.08',
    streetMaxLength: '200',
    postcodeResolverURL: isDevelopment ? '/postcode/' : 'https://postcode.eustylelab.ninja/'
  },
  generate: {
    dir: './public'
  },
  head: {
    titleTemplate: titleChunk => titleChunk ? `${titleChunk} - careid` : 'careid',
    htmlAttrs: { lang: 'ja', dir: 'ltr' },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: 'Next generation esl system' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    ]
  },
  ignore: [
    '**/*.spec.*'
  ],
  loading: { color: '#fff' },
  loadingIndicator: {
    name: 'rectangle-bounce',
    color: '#5c90be',
    background: '#f5f5f5'
  },
  modules: [
    '@nuxtjs/axios',
    ['@nuxtjs/pwa', { onesignal: false }],
    '@nuxtjs/style-resources'
  ],
  plugins: isDevelopment ? [...basePlugins, ...developmentPlugins] : basePlugins,
  srcDir: './main',
  ssr: false,

  // Axios Module
  axios: {
    baseURL: '/',
    https: true,
    // See https://github.com/nuxt-community/axios-module/blob/dev/docs/options.md
    retry: {
      retries: 3,
      retryDelay: retryCount => 2 ** retryCount * 100
    }
  },

  // PWA Module
  pwa: {
    meta: {
      name: ''
    },
    manifest: {
      name: 'careid',
      short_name: 'careid',
      lang: 'ja',
      icons: [
        { src: '/icon.png', sizes: '512x512', type: 'image/png' }
      ]
    },
    workbox: {
      // ローカル環境で確認したいときはコメントアウトを外す.
      // dev: true,
      offline: false,
      runtimeCaching: [
        {
          urlPattern: '/api/options/.*',
          handler: 'NetworkFirst',
          method: 'GET'
        },
        {
          urlPattern: '/api/(ltcs|dws)-project-service-menus.*',
          handler: 'StaleWhileRevalidate',
          method: 'GET',
          strategyOptions: {
            cacheExpiration: {
              maxAgeSeconds: 60 * 60 // 1時間
            },
            cacheableResponse: {
              statuses: [200]
            }
          }
        }
      ]
    }
  },

  // Vuetify Module
  vuetify: {
    customVariables: ['~/assets/style/variables.scss'],
    defaultAssets: false,
    optionsPath: '~/vuetify.options.ts',
    treeShake: {
      components: [
        'VBtn',
        'VCard',
        'VChip',
        'VFabTransition',
        'VIcon',
        'VMenu',
        'VList',
        'VSubheader'
      ],
      loaderOptions: {
        // TODO 本来は @nuxtjs/vuetify/dist/options.d.ts で定義しているので不要なはずなのだが、
        // CI で `implicitly has an 'any' type.` エラーになるので回避策として型を明示する
        match (_: string, { camelTag, kebabTag }: { camelTag: string, kebabTag: string }) {
          if (kebabTag.startsWith('z-')) {
            const m = glob.sync(`components/**/${kebabTag}.{ts,vue}`, {
              cwd: path.join(__dirname, 'main')
            })
            if (m.length > 0) {
              return [camelTag, 'imp' + 'ort ' + camelTag + ` from '~/${m[0]}'`]
            }
          }
        }
      }
    }
  }
}

export default config
