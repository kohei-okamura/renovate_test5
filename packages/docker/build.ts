/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as dotenv from 'dotenv'
import { promises as fs } from 'fs'
import Mustache from 'mustache'
import path from 'path'

type HttpParams = {
  httpPort: string | number
}

type HttpsParams = {
  httpsPort: string | number
}

type WebParams = {
  hostname: string
}

const createTemplateFunction = <T> (src: string) => async (dest: string, params: T): Promise<void> => {
  const templateContent = await fs.readFile(path.resolve(__dirname, 'templates', src), 'utf-8')
  return fs.writeFile(dest, Mustache.render(templateContent, params))
}

const nginx = {
  app: createTemplateFunction<HttpParams & WebParams>('app.nginx.conf.mustache'),
  dev: createTemplateFunction<HttpParams & HttpsParams>('dev.nginx.conf.mustache'),
  maildev: createTemplateFunction<HttpParams>('maildev.nginx.conf.mustache')
}

const main = async (): Promise<void> => {
  dotenv.config()

  const httpPort = process.env.NGINX_HTTP_PORT ?? '55080'
  const httpsPort = process.env.NGINX_HTTPS_PORT ?? '55443'
  const webAssets = path.resolve(__dirname, 'dockerfiles', 'web', 'assets')

  await Promise.all([
    nginx.app(path.resolve(webAssets, 'web-staging', 'nginx', 'conf.d', 'app.nginx.conf'), {
      hostname: '.sandbox.careid.net',
      httpPort
    }),
    nginx.app(path.resolve(webAssets, 'web-staging', 'nginx', 'conf.d', 'app.nginx.conf'), {
      hostname: '.staging.careid.net',
      httpPort
    }),
    nginx.app(path.resolve(webAssets, 'web-prod', 'nginx', 'conf.d', 'app.nginx.conf'), {
      hostname: '.careid.jp',
      httpPort
    }),
    nginx.dev(path.resolve(webAssets, 'web-dev', 'nginx', 'conf.d', 'dev.nginx.conf'), {
      httpPort,
      httpsPort
    }),
    nginx.maildev(path.resolve(webAssets, 'web-dev', 'nginx', 'conf.d', 'maildev.nginx.conf'), {
      httpPort
    })
  ])
}

// noinspection JSIgnoredPromiseFromCall
main()
