/* eslint-disable no-use-before-define */
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export type ModelBaseInfo = {
  label: string
  name: string
  type: 'Entity' | 'Value Object' | 'Enum'
  backend: boolean
  frontend: boolean
  namespace: string
}

export type EntityAttr = {
  label: string
  name: string
  type: string
  readOnly?: boolean
}

export type EntityInfo = ModelBaseInfo & {
  attrs: EntityAttr[]
}

export type ValueObjectInfo = EntityInfo

export type EnumEntry = {
  label: string
  name: string
  value: string | number
}

export type EnumInfo = ModelBaseInfo & {
  entries: EnumEntry[]
}

export type ModelExtendedInfo = EntityInfo | ValueObjectInfo | EnumInfo

type PrimitiveTypeDef = {
  description: string
  type: 'string' | 'number' | 'integer' | 'boolean'
  readOnly?: true
}

type ArrayTypeDef = {
  description: string
  type: 'array'
  items: TypeDef
}

type ObjectTypeDef = {
  description: string
  type: 'object'
  properties?: Record<string, TypeDef>
}

type RefTypeDef = {
  $ref: string
}

export type TypeDef = PrimitiveTypeDef | ArrayTypeDef | ObjectTypeDef | RefTypeDef
