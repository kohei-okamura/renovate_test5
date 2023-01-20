/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import { camelCase, pascalCase } from 'change-case'
import * as path from 'path'
import { ComponentsDir, loadYaml, ParametersDir, QueryParametersDir, readdir, saveYaml, SchemasDir } from './utils'

type OptionalParams = {
  required?: boolean
}
const generateIdDefinitions = async (dir: string, target: 'path' | 'query', params: OptionalParams = {}) => {
  const files = await readdir(SchemasDir)
  const xs = files
    .filter(x => x.endsWith('-id.yaml'))
    .map(async filename => {
      const name = filename.replace(/\.yaml$/, '')
      const data = await loadYaml(path.join(SchemasDir, filename)) as Record<string, any>
      await saveYaml(path.join(dir, filename), {
        in: target,
        name: camelCase(name),
        description: data.description,
        ...params,
        schema: {
          $ref: `#/components/schemas/${pascalCase(name)}`
        }
      })
    })
  await Promise.all(xs)
}

const updateIndex = async (dir: string, target: string) => {
  const files = await readdir(dir)
  const entries = files.map(filepath => {
    const name = camelCase(path.basename(filepath, '.yaml'))
    const $ref = `./${target}/` + filepath
    return [name, { $ref }]
  })
  await saveYaml(path.join(ComponentsDir, `${target}.yaml`), Object.fromEntries(entries))
}

const main = async () => {
  await generateIdDefinitions(ParametersDir, 'path', { required: true })
  await generateIdDefinitions(QueryParametersDir, 'query')
  await updateIndex(ParametersDir, 'parameters')
  await updateIndex(QueryParametersDir, 'query-parameters')
}

main().then(noop)
