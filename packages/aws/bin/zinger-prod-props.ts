/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { aws_ec2 as ec2, aws_iam as iam } from 'aws-cdk-lib'
import * as parameters from '~aws/bin/parameters.json'
import { ZingerProps } from '~aws/lib/props'
import {
  ZINGER_ACCOUNT,
  ZINGER_APP_CLI_PROD,
  ZINGER_APP_CLI_PROD_REPOSITORY_NAME,
  ZINGER_APP_SERVER_PROD,
  ZINGER_APP_SERVER_PROD_REPOSITORY_NAME,
  ZINGER_ECS_CLUSTER,
  ZINGER_GITHUB_ACTIONS_OIDC_PROVIDER_ID,
  ZINGER_GITHUB_ACTIONS_ROLE_ID,
  ZINGER_GITHUB_ACTIONS_ROLE_NAME,
  ZINGER_WEB_PROD,
  ZINGER_WEB_PROD_REPOSITORY_NAME
} from '~aws/variables'

export const ZingerProdProps: ZingerProps = {
  account: ZINGER_ACCOUNT,
  albDns: {
    public: {
      aRecord: '*.careid.jp',
      domainName: 'careid.jp'
    }
  },
  bastion: {
    bastionHostname: 'zinger-bastion',
    keyName: 'zingerKey'
  },
  db: {
    instanceType: ec2.InstanceType.of(ec2.InstanceClass.BURSTABLE2, ec2.InstanceSize.MICRO),
    masterUserPassword: parameters.zinger.secure.dbMasterPassword,
    multiAz: true
  },
  ecr: {
    repositories: [
      { id: ZINGER_APP_CLI_PROD, repositoryName: ZINGER_APP_CLI_PROD_REPOSITORY_NAME },
      { id: ZINGER_APP_SERVER_PROD, repositoryName: ZINGER_APP_SERVER_PROD_REPOSITORY_NAME },
      { id: ZINGER_WEB_PROD, repositoryName: ZINGER_WEB_PROD_REPOSITORY_NAME }
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
    needRedash: true,
    redashDomainName: 'redash.careid.jp',
    zoneDomainName: 'careid.jp'
  },
  redis: {
    authToken: parameters.zinger.secure.redisPassword,
    automaticFailoverEnabled: true,
    cacheNodeType: 'cache.t2.micro',
    multiAzEnabled: true,
    numCacheClusters: 2
  },
  s3: {},
  vpc: {
    cidr: '10.20.0.0/16',
    natGateways: 2,
    onePerAz: false
  }
}
