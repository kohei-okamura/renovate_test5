/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  StackProps,
  aws_ec2 as ec2,
  aws_iam as iam,
  aws_s3 as s3,
  aws_ssm as ssm
} from 'aws-cdk-lib'
import { GithubActionsStackProps } from '~aws/lib/github-actions-stack'
import { RedashStackProps } from '~aws/lib/redash-stack'

export type ZingerAlbDnsStackProps = StackProps & {
  logStorage: s3.Bucket
  public: {
    aRecord: string
    domainName: string
  }
  securityGroup: ec2.SecurityGroup
  vpc: ec2.Vpc
}

export type ZingerBastionStackProps = StackProps & {
  bastionRole: iam.IRole
  bastionHostname: string
  keyName: string
  securityGroup: ec2.SecurityGroup
  subnetGroupName: string
  vpc: ec2.Vpc
}

export type ZingerDbStackProps = StackProps & {
  instanceType: ec2.InstanceType
  masterUserPassword: ssm.SecureStringParameterAttributes
  multiAz: boolean
  securityGroup: ec2.SecurityGroup
  vpc: ec2.Vpc
}

type Repository = {
  id: string
  repositoryName: string
}

export type ZingerEcrStackProps = StackProps & {
  repositories: Repository[]
}

export type ZingerEcsStackProps = StackProps & {
  name: string
  vpc: ec2.Vpc
}

export type ZingerRedisStackProps = StackProps & {
  authToken: ssm.SecureStringParameterAttributes
  automaticFailoverEnabled: boolean
  cacheNodeType: string
  multiAzEnabled: boolean
  numCacheClusters: number
  securityGroup: ec2.SecurityGroup
  subnetGroupName: string
  vpc: ec2.Vpc
}

export type ZingerS3StackProps = StackProps & {
  suffix?: string
}

export type ZingerSecurityGroupStackProps = StackProps & {
  vpc: ec2.Vpc
}

export type ZingerVpcStackProps = StackProps & {
  cidr: string
  logStorage: s3.Bucket
  natGateways: number
  onePerAz: boolean
}

export type ZingerProps = {
  account: string
  albDns: Omit<ZingerAlbDnsStackProps, 'logStorage' | 'securityGroup' | 'vpc'>
  bastion: Omit<ZingerBastionStackProps, 'bastionRole' | 'securityGroup' | 'subnetGroupName' | 'vpc'>
  db: Omit<ZingerDbStackProps, 'securityGroup' | 'vpc'>
  ecr: ZingerEcrStackProps
  ecs: Omit<ZingerEcsStackProps, 'vpc'>
  githubActions: GithubActionsStackProps
  redash: Omit<RedashStackProps, 'vpc'>
  redis: Omit<ZingerRedisStackProps, 'securityGroup' | 'subnetGroupName' | 'vpc'>
  s3: ZingerS3StackProps
  vpc: Omit<ZingerVpcStackProps, 'logStorage'>
}
