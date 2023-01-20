/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { promises as fs, Stats } from 'fs'
import * as yaml from 'js-yaml'
import * as path from 'path'

export const ProjectRootDir = path.resolve(__dirname, '..')
export const SrcDir = path.join(ProjectRootDir, 'src')
export const ComponentsDir = path.join(SrcDir, 'components')
export const PathsDir = path.join(SrcDir, 'paths')
export const ParametersDir = path.join(ComponentsDir, 'parameters')
export const QueryParametersDir = path.join(ComponentsDir, 'query-parameters')
export const SchemasDir = path.join(ComponentsDir, 'schemas')

const readdirRecursive = async (dir: string): Promise<string[]> => {
  const paths = await fs.readdir(dir)
  const entries = await Promise.all(paths.map<Promise<[string, Stats]>>(async x => {
    const p = path.join(dir, x)
    return [p, await fs.stat(p)]
  }))
  const xs = await Promise.all(entries.map(([path, stats]) => {
    return stats.isDirectory() ? readdirRecursive(path) : Promise.resolve([path])
  }))
  return xs.flat()
}

/**
 * 指定したディレクトリ以下にあるファイルの一覧を再帰的に取得する.
 */
export const readdir = async (dir: string) => (await readdirRecursive(dir)).map(x => path.relative(dir, x))

/**
 * YAML ファイルを保存する.
 */
export const saveYaml = async (file: string, data: any): Promise<void> => {
  const year = (new Date()).getFullYear()
  const content = [
    `# Copyright © ${year} EUSTYLE LABORATORY - ALL RIGHTS RESERVED.`,
    '# UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.',
    '#',
    '# THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.',
    '---',
    yaml.dump(data)
  ]
  await fs.writeFile(file, content.join('\n'))
}

/**
 * YAML ファイルを読み込む.
 */
export const loadYaml = async (file: string): Promise<ReturnType<typeof yaml.load>> => {
  const data = await fs.readFile(file, { encoding: 'utf-8' })
  return yaml.load(data)
}
