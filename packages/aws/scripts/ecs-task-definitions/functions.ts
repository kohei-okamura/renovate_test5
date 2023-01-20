/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { region } from '~aws/variables'

type TaskDefinition = AWS.ECS.Types.RegisterTaskDefinitionRequest

type TaskDefinitionParams = Partial<TaskDefinition> & Pick<TaskDefinition, 'containerDefinitions' | 'family'>

export type Environment = 'prod' | 'staging' | 'sandbox'

export type TaskParams = {
  accountId: string
  environment: Environment
  region?: string
  tag: string
}

export type ImageParams = TaskParams & {
  image: string
}

const DEFAULT_REGION = region

/**
 * ECR のホスト名を取得する.
 */
export const getEcrHost = ({ accountId, region }: Omit<TaskParams, 'environment' | 'tag'>): string => {
  return `${accountId}.dkr.ecr.${region ?? DEFAULT_REGION}.amazonaws.com`
}

/**
 * イメージ名を取得する.
 */
export const getImageName = (image: string, { accountId, environment, region, tag }: TaskParams) => {
  const host = getEcrHost({ accountId, region })
  return `${host}/${image}-${environment}:${tag}`
}

/**
 * タスク定義関数.
 */
export const taskDefinition = (definition: TaskDefinitionParams): TaskDefinition => ({
  networkMode: 'awsvpc',
  requiresCompatibilities: ['FARGATE'],
  taskRoleArn: 'ZingerEcsTaskRole',
  executionRoleArn: 'ZingerEcsTaskExecutionRole',
  ...definition
})
