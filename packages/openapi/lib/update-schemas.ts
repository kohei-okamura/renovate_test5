/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import { paramCase, pascalCase } from 'change-case'
import { promises as fs } from 'fs'
import * as path from 'path'
import * as schemas from '../schemas.json'
import { EntityInfo, EnumInfo, ModelExtendedInfo, TypeDef, ValueObjectInfo } from './types'
import { ComponentsDir, saveYaml } from './utils'

const Entity = 'Entity'
const Enum = 'Enum'
const ValueObject = 'Value Object'

const SchemasDir = path.join(ComponentsDir, 'schemas')

const Schemas = schemas.filter(x => x.frontend)
const Names = ['DateLike', ...Schemas.map(x => x.name)]

function ensureSchema (x: any): asserts x is ModelExtendedInfo {
  const type = x.type
  if (type !== Entity && type !== Enum && type !== ValueObject) {
    throw new Error(`Unexpected schema type: ${type}`)
  }
}

function isEntity (x: ModelExtendedInfo): x is EntityInfo {
  return x.type === 'Entity'
}

function isEnum (x: ModelExtendedInfo): x is EnumInfo {
  return x.type === 'Enum'
}

function isValueObject (x: ModelExtendedInfo): x is ValueObjectInfo {
  return x.type === 'Value Object'
}

type GetTypeDefArguments = {
  type: string
  description: string
  readOnly?: boolean
  minLength?: number
  maxLength?: number
}
const getTypeDef = ({ type, description, readOnly, minLength, maxLength }: GetTypeDefArguments): TypeDef => {
  switch (type) {
    case 'boolean':
    case 'number':
    case 'string':
      return ({
        description,
        type,
        ...(readOnly ? { readOnly } : {}),
        ...(minLength ? { minLength } : {}),
        ...(maxLength ? { maxLength } : {})
      })
    default:
      return Names.includes(type)
        ? {
          $ref: `#/components/schemas/${type}`
        }
        : {
          description,
          type: 'object'
        }
  }
}

type GetTypeArguments = {
  type: string
  label: string
  readOnly?: boolean
}
const getType = ({ type: x, label: description, ...rest }: GetTypeArguments): TypeDef => {
  const input = x.replace(/\s.+$/, '').trim()
  const isArray = input.endsWith('[]')
  const type = isArray ? input.slice(0, -2) : input
  return isArray
    ? { description, type: 'array', items: getTypeDef({ type, description }) }
    : getTypeDef({ type, description, ...rest })
}

const saveSchema = (name: string, data: Record<string, any>) => saveYaml(
  path.resolve(SchemasDir, paramCase(name) + '.yaml'),
  data
)

const saveEntitySchema = async (info: EntityInfo) => {
  await Promise.all([
    saveSchema(info.name + 'Id', {
      description: `${info.label} ID`,
      type: 'integer'
    }),
    saveSchema(info.name, {
      description: info.label,
      type: 'object',
      properties: Object.fromEntries(info.attrs.filter(x => x.type !== '-').map(x => [x.name, getType(x)]))
    })
  ])
}

const saveEnumSchema = async (info: EnumInfo) => {
  const type = info.entries.every(x => typeof x.value === 'string') ? 'string' : 'number'
  await saveSchema(info.name, {
    description: info.label,
    type,
    enum: info.entries.filter(x => x.value !== '-').map(x => x.value)
  })
}

const saveValueObjectSchema = async (info: ValueObjectInfo) => {
  await saveSchema(info.name, {
    description: info.label,
    type: 'object',
    properties: Object.fromEntries(info.attrs.filter(x => x.type !== '-').map(x => [x.name, getType(x)]))
  })
}

const updateSchemas = async () => {
  const promises = [...Schemas].map(async info => {
    ensureSchema(info)
    if (isEntity(info)) {
      await saveEntitySchema(info)
    } else if (isEnum(info)) {
      await saveEnumSchema(info)
    } else if (isValueObject(info)) {
      await saveValueObjectSchema(info)
    } else {
      throw new Error('Unexpected schema given')
    }
  })
  await Promise.all(promises)
}

const updateSchemaIndex = async () => {
  const entries = (await fs.readdir(SchemasDir)).map(filename => {
    const name = pascalCase(path.basename(filename, '.yaml'))
    const $ref = `./schemas/${filename}`
    return [name, { $ref }]
  })
  await saveYaml(path.resolve(ComponentsDir, 'schemas.yaml'), Object.fromEntries(entries))
}

const main = async () => {
  await updateSchemas()
  await updateSchemaIndex()
}

main().then(noop)
