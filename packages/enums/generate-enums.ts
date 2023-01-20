/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Command } from 'commander'
import { promises as fs } from 'fs'
import * as YAML from 'js-yaml'
import Mustache from 'mustache'
import * as path from 'path'
import { plural } from 'pluralize'

const CAMELIZE_REGEXP = /^[A-Z]/g
const HYPHENATE_REGEXP = /[A-Z]/g

const PACKAGES_DIR = path.resolve(__dirname, '..')
const ROOT_DIR = path.resolve(PACKAGES_DIR, '..')
const TEMPLATES_DIR = __dirname
const YAML_DIR = path.resolve(__dirname, 'yaml')

const PHP_TEMPLATE = path.resolve(TEMPLATES_DIR, 'enum-php.mustache')
const PHP_ENUMS_DIR = path.resolve(ROOT_DIR, 'server', 'modules', 'domain')
const TYPESCRIPT_TEMPLATE = path.resolve(TEMPLATES_DIR, 'enum-typescript.mustache')
const TYPESCRIPT_ENUMS_DIR = path.resolve(__dirname, 'lib')

type Names = {
  camelCase: string
  kebabCase: string
  pluralized: string
}

type PhpDefinition = Readonly<{
  enabled: true
  namespace: string
  traits?: string[]
  withResolver?: boolean
} | {
  enabled: false
}>

type TypeScriptDefinition = Readonly<{
  enabled: boolean
}>

type ItemDefinition = Readonly<{
  description: string
  name: string
  value: any
}>

type Definition = Readonly<{
  name: string
  description: string
  php: PhpDefinition
  typescript: TypeScriptDefinition
  items: ItemDefinition[]
}>

type TraitParams = Readonly<{
  enabled: boolean
  entries: string[]
}>

type PhpParams = Readonly<{
  enabled: true
  imports: string[]
  namespace: string
  trait: TraitParams
  withResolver: boolean
} | {
  enabled: false
  namespace: string
}>

type TypeScriptParams = TypeScriptDefinition

type ItemParams = ItemDefinition & {
  names: Names
  isLast: boolean
}

type Params = {
  name: string
  names: Names
  description: string
  php: PhpParams
  typescript: TypeScriptParams
  items: ItemParams[]
}

type WriteArgs = {
  command: Command
  destination: string
  enabled: boolean
  params: Params
  template: string
  yamlLastUpdate: number
  yamlPath: string
}

/**
 * 文字列を camelCase に変換する.
 */
const camelize = (input: string): string => input.replace(CAMELIZE_REGEXP, m => m.toLowerCase())

/**
 * 文字列を kebab-case に変換する.
 */
const hyphenate = (input: string): string => input.replace(HYPHENATE_REGEXP, m => '-' + m.toLowerCase()).substr(1)

/**
 * 文字列を受け取り `Names` を生成する.
 */
const createNames = (name: string): Names => {
  const camelCase = camelize(name)
  const kebabCase = hyphenate(name)
  const pluralized = plural(camelCase)
  return {
    camelCase,
    kebabCase,
    pluralized
  }
}

/**
 * PHPのトレイト関連の定義を受け取りパラメータに変換する.
 */
const parseTraitsDefinition = (traits: string[]): TraitParams => ({
  enabled: traits.length > 0,
  entries: traits
})

/**
 * PHP関連の定義を受け取りパラメータに変換する.
 */
const parsePhpDefinition = (definition: PhpDefinition): PhpParams => {
  if (definition.enabled) {
    const { namespace } = definition
    const trait = parseTraitsDefinition(definition.traits ?? [])
    const imports = ['Domain\\Enum'].sort()
    return {
      enabled: true,
      imports,
      namespace,
      trait,
      withResolver: definition.withResolver ?? false
    }
  } else {
    return {
      enabled: false,
      namespace: ''
    }
  }
}

/**
 * TypeScript 関連の定義を受け取りパラメータに変換する.
 */
const parseTypeScriptDefinition = (definition: TypeScriptDefinition): TypeScriptParams => definition

/**
 * 要素の定義を受け取りパラメータに変換する.
 */
const parseItemsDefinition = (definition: ItemDefinition[]): ItemParams[] => {
  const [last, ...xs] = definition
    .map(item => ({
      ...item,
      value: typeof item.value === 'number' ? item.value : `'${item.value.replace(/['\\]/g, '\\$&')}'`,
      names: createNames(item.name)
    }))
    .reverse()
  return [
    ...xs.map(x => ({ ...x, isLast: false })).reverse(),
    { ...last, isLast: true }
  ]
}

/**
 * YAML の定義を読み込む.
 */
const loadYaml = async (filePath: string): Promise<Params> => {
  const definition = YAML.load(await fs.readFile(filePath, 'utf-8')) as Definition
  return {
    name: definition.name,
    names: createNames(definition.name),
    description: definition.description,
    php: parsePhpDefinition(definition.php),
    typescript: parseTypeScriptDefinition(definition.typescript),
    items: parseItemsDefinition(definition.items)
  }
}

/**
 * 指定したファイルの最終更新時刻をミリ秒単位で取得する.
 */
const stat = async (filePath: string): Promise<number> => {
  try {
    const stat = await fs.stat(filePath)
    return stat.isFile() ? stat.mtimeMs : 0
  } catch {
    return 0
  }
}

/**
 * コードを生成する.
 */
const generate = (template: string, params: Params): string => Mustache.render(template, {
  params,
  year: (new Date()).getFullYear()
})

/**
 * コードを生成して書き込む.
 */
const write = async ({ command, destination, enabled, params, template, yamlPath, yamlLastUpdate }: WriteArgs) => {
  if (enabled && (command.opts().force || yamlLastUpdate > await stat(destination))) {
    const dir = path.dirname(destination)
    await fs.mkdir(dir, { recursive: true })
    await fs.writeFile(destination, generate(template, params))
    return [`✴️  ${path.basename(yamlPath)} -> ${path.basename(destination)}\n`]
  } else {
    return []
  }
}

async function main (): Promise<void> {
  const command = (new Command())
    .version('1.0.0')
    .option('-f, --force', 'force update enum files', false)
    .parse(process.argv)

  const [files, php, typescript] = await Promise.all([
    fs.readdir(YAML_DIR, { withFileTypes: true }),
    fs.readFile(PHP_TEMPLATE, 'utf-8'),
    fs.readFile(TYPESCRIPT_TEMPLATE, 'utf-8')
  ])

  const xs = await Promise.all(files.filter(x => x.isFile()).map(async file => {
    const yamlPath = path.join(YAML_DIR, file.name)
    const yamlLastUpdate = command.opts().force ? 0 : await stat(yamlPath)
    const params = await loadYaml(yamlPath)
    const args = {
      command,
      yamlPath,
      yamlLastUpdate,
      params
    }
    return Promise.all([
      write({
        ...args,
        destination: path.join(PHP_ENUMS_DIR, params.php.namespace, `${params.name}.php`),
        enabled: params.php.enabled,
        template: php
      }),
      write({
        ...args,
        destination: path.join(TYPESCRIPT_ENUMS_DIR, `${params.names.kebabCase}.ts`),
        enabled: params.typescript.enabled,
        template: typescript
      })
    ])
  }))

  xs.flat(2).forEach(x => process.stdout.write(x))
}

// noinspection JSIgnoredPromiseFromCall
main()
