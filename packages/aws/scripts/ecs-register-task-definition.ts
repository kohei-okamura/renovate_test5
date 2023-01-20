/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { batch } from '~aws/scripts/ecs-task-definitions/batch'
import { Environment } from '~aws/scripts/ecs-task-definitions/functions'
import { queue } from '~aws/scripts/ecs-task-definitions/queue'
import { service } from '~aws/scripts/ecs-task-definitions/service'
import { Profile } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { getAccountId } from '~aws/scripts/utils/get-account-id'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import {
  ZINGER_APP_BATCH_TASK_DEFINITION,
  ZINGER_APP_CREATE_USER_BILLING_TASK_DEFINITION,
  ZINGER_APP_MIGRATION_TASK_DEFINITION
} from '~aws/variables'

const families = {
  batch: batch(ZINGER_APP_BATCH_TASK_DEFINITION, {
    command: ['php', 'artisan', 'schedule:run']
  }),
  createUserBilling: batch(ZINGER_APP_CREATE_USER_BILLING_TASK_DEFINITION, {
    command: ['php', 'artisan', 'user-billing:create', '--batch']
  }),
  migration: batch(ZINGER_APP_MIGRATION_TASK_DEFINITION, {
    command: ['php', 'artisan', 'migrate', '--force'],
    memory: '1024'
  }),
  queue,
  service
} as const

const familyNames = Object.keys(families)

export type Family = keyof typeof families

export type Options = RunCommandOptions & {
  tag: string
}

const getEnvironment = (profile: Profile): Environment => {
  switch (profile) {
    case 'zinger':
      return 'prod'
    case 'zinger-staging':
      return 'staging'
    case 'zinger-sandbox':
      return 'sandbox'
    default:
      throw new Error(`Unknown profile given: ${profile}`)
  }
}

function assertFamily (family: string): asserts family is Family {
  if (!familyNames.includes(family)) {
    throw new Error(`Undefined family given: ${family}`)
  }
}

const getParams = async (family: Family, params: Options): Promise<AWS.ECS.Types.RegisterTaskDefinitionRequest> => {
  const { profile, tag } = params
  return families[family]({
    accountId: await getAccountId(),
    environment: getEnvironment(profile),
    tag
  })
}

export const registerTaskDefinition = async (ecs: AWS.ECS, family: Family, options: Options) => {
  const params = await getParams(family, options)
  return await runAwsCommand(() => ecs.registerTaskDefinition(params))
}

export const main = (options: Options) => runCommand(options, () => {
  const ecs = createEcsService()
  return Promise.all(Object.keys(families).map(family => {
    assertFamily(family)
    return registerTaskDefinition(ecs, family, options)
  }))
})

// 以降の行はメインスクリプトとして読み込まれた場合のみ実行される ＝ テストでは実行されない
if (require.main === module) {
  const options = createCommand()
    .requiredOption('-t, --tag [tag]', 'tag')
    .parse(process.argv)
    .opts<Options>()

  // noinspection JSIgnoredPromiseFromCall
  main(options)
}
