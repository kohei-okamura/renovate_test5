/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import * as path from 'path'
import { PathsDir, readdir, saveYaml } from './utils'

const main = async () => {
  const files = await readdir(PathsDir)
  const entries = files
    .filter(filepath => filepath !== 'index.yaml')
    .map(filepath => {
      const name = '/' + filepath.replace(/(\/index)?\.yaml$/, '').replace(/_([a-zA-Z]+)/g, '{$1}')
      const $ref = './' + filepath
      return [name, { $ref }]
    })
  await saveYaml(path.join(PathsDir, 'index.yaml'), Object.fromEntries(entries))
}

main().then(noop)
