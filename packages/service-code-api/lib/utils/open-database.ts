/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { camelToSnake } from '@zinger/helpers'
import knex from 'knex'

export const openDatabase = (filename: string) => knex({
  client: 'better-sqlite3',
  connection: { filename },
  useNullAsDefault: true,
  wrapIdentifier: (value, next) => next(camelToSnake(value))
})
