#!/usr/bin/env node
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { App, Environment } from 'aws-cdk-lib'
import 'source-map-support/register'
import { GithubActionsStack } from '~aws/lib/github-actions-stack'
import { ZingerProps } from '~aws/lib/props'
import { RedashStack } from '~aws/lib/redash-stack'
import { ZingerAlbDnsStack } from '~aws/lib/zinger-alb-dns-stack'
import { ZingerBastionStack } from '~aws/lib/zinger-bastion-stack'
import { ZingerDbStack } from '~aws/lib/zinger-db-stack'
import { ZingerEcrStack } from '~aws/lib/zinger-ecr-stack'
import { ZingerEcsStack } from '~aws/lib/zinger-ecs-stack'
import { ZingerIamStack } from '~aws/lib/zinger-iam-stack'
import { ZingerRedisStack } from '~aws/lib/zinger-redis-stack'
import { ZingerS3Stack } from '~aws/lib/zinger-s3-stack'
import { ZingerSecurityGroupStack } from '~aws/lib/zinger-security-group-stack'
import { ZingerSqsStack } from '~aws/lib/zinger-sqs-stack'
import { ZingerVpcStack } from '~aws/lib/zinger-vpc-stack'
import { region, ZINGER_PRIVATE_SUBNET_NAME, ZINGER_PUBLIC_SUBNET_NAME } from '~aws/variables'

export class Zinger {
  readonly albDns: ZingerAlbDnsStack
  readonly bastion: ZingerBastionStack
  readonly db: ZingerDbStack
  readonly ecr: ZingerEcrStack
  readonly ecs: ZingerEcsStack
  readonly githubActions: GithubActionsStack
  readonly iam: ZingerIamStack
  readonly redis: ZingerRedisStack
  readonly redash: RedashStack
  readonly s3: ZingerS3Stack
  readonly securityGroup: ZingerSecurityGroupStack
  readonly sqs: ZingerSqsStack
  readonly vpc: ZingerVpcStack

  constructor (props: ZingerProps) {
    const app = new App()
    const env: Environment = {
      account: props.account,
      region
    }

    //
    // IAM
    //
    this.iam = new ZingerIamStack(app, 'ZingerIamStack', {
      env
    })
    const { bastionRole } = this.iam

    //
    // S3
    //
    this.s3 = new ZingerS3Stack(app, 'ZingerS3Stack', {
      env,
      ...props.s3
    })
    const { logStorage } = this.s3

    //
    // VPC
    //
    this.vpc = new ZingerVpcStack(app, 'ZingerVpcStack', {
      env,
      logStorage,
      ...props.vpc
    })
    const { vpc } = this.vpc

    //
    // SecurityGroup
    //
    this.securityGroup = new ZingerSecurityGroupStack(app, 'ZingerSecurityGroupStack', {
      env,
      vpc
    })
    const { securityGroups } = this.securityGroup

    //
    // ALB & DNS (Route53)
    //
    this.albDns = new ZingerAlbDnsStack(app, 'ZingerAlbDnsStack', {
      env,
      logStorage,
      securityGroup: securityGroups.alb,
      vpc,
      ...props.albDns
    })

    //
    // DB (RDS)
    //
    this.db = new ZingerDbStack(app, 'ZingerDbStack', {
      env,
      securityGroup: securityGroups.mysql,
      vpc,
      ...props.db
    })

    //
    // Redis (ElastiCache)
    //
    this.redis = new ZingerRedisStack(app, 'ZingerRedisStack', {
      env,
      securityGroup: securityGroups.redis,
      subnetGroupName: ZINGER_PRIVATE_SUBNET_NAME,
      vpc,
      ...props.redis
    })

    //
    // Bastion (EC2)
    //
    this.bastion = new ZingerBastionStack(app, 'ZingerBastionStack', {
      env,
      bastionRole,
      securityGroup: securityGroups.bastion,
      subnetGroupName: ZINGER_PUBLIC_SUBNET_NAME,
      vpc,
      ...props.bastion
    })

    //
    // ECR
    //
    this.ecr = new ZingerEcrStack(app, 'ZingerEcrStack', {
      env,
      ...props.ecr
    })

    //
    // ECS
    //
    this.ecs = new ZingerEcsStack(app, 'ZingerEcsStack', {
      env,
      vpc,
      ...props.ecs
    })

    //
    // SQS
    //
    this.sqs = new ZingerSqsStack(app, 'ZingerSqsStack', {
      env
    })

    //
    // GitHub Actions
    //
    this.githubActions = new GithubActionsStack(app, 'GithubActionsStack', {
      env,
      ...props.githubActions
    })

    //
    // Redash
    //
    this.redash = new RedashStack(app, 'RedashStack', {
      env,
      vpc,
      ...props.redash
    })
  }
}
