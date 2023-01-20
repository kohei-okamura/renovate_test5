/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { CredentialsOptions } from 'aws-sdk/lib/credentials'
import { execSync } from 'child_process'
import * as fs from 'fs'
import { Seq } from 'immutable'
import ini from 'ini'
import { DateTime } from 'luxon'
import path from 'path'

export type AwsConfigOptions = {
  credentialsOptions: CredentialsOptions
  region: string
}

type AwsCliCache = {
  Credentials: {
    AccessKeyId: string
    SecretAccessKey: string
    SessionToken: string
    Expiration: string
  }
}

type AwsConfigKey = 'region' | 'role_arn' | 'source_profile'
type AwsConfig = Record<AwsConfigKey, string>

const AWS_CONFIG = `${process.env.HOME}/.aws/config`
const AWS_CLI_CACHE = `${process.env.HOME}/.aws/cli/cache`

const normalizeArn = (arn: string): string => arn.replace(':iam:', ':sts:').replace(':role', ':assumed-role')
const loadAwsConfig = (profile: string): AwsConfig => {
  const content = fs.readFileSync(AWS_CONFIG, 'utf-8')
  const config = ini.parse(content)[`profile ${profile}`]
  assert(config !== undefined, `Failed to load config for profile: ${profile}`)
  return config
}

const loadAwsCache = (config: AwsConfig): AwsCliCache | undefined => {
  const cacheFilePaths = fs
    .readdirSync(AWS_CLI_CACHE)
    .filter(filename => filename.toLowerCase().endsWith('.json'))
    .map(filename => path.join(AWS_CLI_CACHE, filename))
  const roleArn = normalizeArn(config.role_arn)
  return Seq(cacheFilePaths)
    .filter(filepath => fs.statSync(filepath).isFile())
    .map(filepath => fs.readFileSync(filepath, 'utf-8'))
    .map(content => JSON.parse(content))
    .find(data => normalizeArn(data.AssumedRoleUser.Arn).includes(roleArn))
}

const isCacheExpired = (cache: AwsCliCache): boolean => {
  return DateTime.fromISO(cache.Credentials.Expiration) <= DateTime.local()
}

const loadAwsCacheGreedy = (profile: string, config: AwsConfig): AwsCliCache | undefined => {
  const cache = loadAwsCache(config)
  if (cache === undefined || isCacheExpired(cache)) {
    execSync(`aws sts get-caller-identity --profile ${profile}`)
    return loadAwsCache(config)
  } else {
    return cache
  }
}

export const getAwsConfigOptions = (profile: string): AwsConfigOptions => {
  const config = loadAwsConfig(profile)
  const cache = loadAwsCacheGreedy(profile, config)

  assert(cache !== undefined, 'Failed to authentication')

  return {
    credentialsOptions: {
      accessKeyId: cache.Credentials.AccessKeyId,
      secretAccessKey: cache.Credentials.SecretAccessKey,
      sessionToken: cache.Credentials.SessionToken
    },
    region: config.region
  }
}
