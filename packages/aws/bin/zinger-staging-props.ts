/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { aws_ec2 as ec2, aws_iam as iam } from 'aws-cdk-lib'
import * as parameters from '~aws/bin/parameters.json'
import { ZingerProps } from '~aws/lib/props'
import {
  ZINGER_APP_CLI_STAGING,
  ZINGER_APP_CLI_STAGING_REPOSITORY_NAME,
  ZINGER_APP_SERVER_STAGING,
  ZINGER_APP_SERVER_STAGING_REPOSITORY_NAME,
  ZINGER_ECS_CLUSTER,
  ZINGER_GITHUB_ACTIONS_OIDC_PROVIDER_ID,
  ZINGER_GITHUB_ACTIONS_ROLE_ID,
  ZINGER_GITHUB_ACTIONS_ROLE_NAME,
  ZINGER_STAGING_ACCOUNT,
  ZINGER_WEB_STAGING,
  ZINGER_WEB_STAGING_REPOSITORY_NAME
} from '~aws/variables'

export const ZingerStagingProps: ZingerProps = {
  account: ZINGER_STAGING_ACCOUNT,
  albDns: {
    public: {
      aRecord: '*.staging.careid.net',
      domainName: 'staging.careid.net'
    }
  },
  db: {
    instanceType: ec2.InstanceType.of(ec2.InstanceClass.BURSTABLE2, ec2.InstanceSize.MICRO),
    masterUserPassword: parameters.zinger.secure.dbMasterPassword,
    multiAz: false
  },
  bastion: {
    bastionHostname: 'zinger-bastion-staging',
    keyName: 'stagingZingerKey'
  },
  ecr: {
    repositories: [
      { id: ZINGER_APP_CLI_STAGING, repositoryName: ZINGER_APP_CLI_STAGING_REPOSITORY_NAME },
      { id: ZINGER_APP_SERVER_STAGING, repositoryName: ZINGER_APP_SERVER_STAGING_REPOSITORY_NAME },
      { id: ZINGER_WEB_STAGING, repositoryName: ZINGER_WEB_STAGING_REPOSITORY_NAME }
    ]
  },
  ecs: {
    name: ZINGER_ECS_CLUSTER
  },
  githubActions: {
    github: {
      owner: 'eustylelab',
      repo: 'zinger'
    },
    managedPolicies: [
      iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonEC2ContainerRegistryPowerUser'),
      iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonECS_FullAccess'),
      iam.ManagedPolicy.fromAwsManagedPolicyName('CloudWatchLogsReadOnlyAccess')
    ],
    oidcProviderId: ZINGER_GITHUB_ACTIONS_OIDC_PROVIDER_ID,
    roleId: ZINGER_GITHUB_ACTIONS_ROLE_ID,
    roleName: ZINGER_GITHUB_ACTIONS_ROLE_NAME
  },
  redash: {
    machineImageName: 'redash-8.0.0-b32245-1-ap-northeast-1',
    needRedash: false,
    redashDomainName: 'redash.staging.careid.net',
    zoneDomainName: 'staging.careid.net'
  },
  redis: {
    authToken: parameters.zinger.secure.redisPassword,
    automaticFailoverEnabled: false,
    cacheNodeType: 'cache.t2.micro',
    multiAzEnabled: false,
    numCacheClusters: 1
  },
  s3: {
    suffix: 'staging'
  },
  vpc: {
    cidr: '10.25.0.0/16',
    natGateways: 1,
    onePerAz: true
  }
}
