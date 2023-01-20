/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { EnvironmentVariables, SecretList } from 'aws-sdk/clients/ecs'
import { EnvironmentName } from '~aws/scripts/ecs-task-definitions/environment-name'
import { Environment } from '~aws/scripts/ecs-task-definitions/functions'
import {
  mackerelApiKey,
  SsmMackerelParameterName
} from '~aws/scripts/ssm-parameters/names'

type EnvironmentDef = Partial<Record<EnvironmentName, string>>

type MackerelSecretName = 'MACKEREL_APIKEY'

const toVariables = (def: EnvironmentDef): EnvironmentVariables => Object.entries(def).map(([name, value]) => ({
  name,
  value
}))

export const mackerelEnvironments: Record<Environment, EnvironmentVariables> = {
  prod: toVariables({
    MACKEREL_CONTAINER_PLATFORM: 'ecs_v3',
    MACKEREL_ROLES: 'Zinger:Ecs'
  }),
  staging: toVariables({
    MACKEREL_CONTAINER_PLATFORM: 'ecs_v3',
    MACKEREL_ROLES: 'ZingerStaging:Ecs'
  }),
  sandbox: toVariables({
    MACKEREL_CONTAINER_PLATFORM: 'ecs_v3',
    MACKEREL_ROLES: 'ZingerSandbox:Ecs'
  })
}

const mackerelSecretsDef: Record<MackerelSecretName, SsmMackerelParameterName> = {
  MACKEREL_APIKEY: mackerelApiKey
}

export const mackerelSecrets: SecretList = Object.entries(mackerelSecretsDef).map(([name, valueFrom]) => ({
  name,
  valueFrom: valueFrom!
}))
