/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stack } from 'aws-cdk-lib'
import { Template } from 'aws-cdk-lib/assertions'
import { Zinger } from '~aws/bin/zinger'
import { ZingerProdProps } from '~aws/bin/zinger-prod-props'
import { ZingerSandboxProps } from '~aws/bin/zinger-sandbox-props'
import { ZingerStagingProps } from '~aws/bin/zinger-staging-props'

describe('aws cdk deployment', () => {
  describe.each([
    ['prod', ZingerProdProps],
    ['staging', ZingerStagingProps],
    ['sandbox', ZingerSandboxProps]
  ])('%s', (_, props) => {
    const zinger = new Zinger(props)
    describe.each<[string, Stack]>([
      ['ZingerAlbDnsStack', zinger.albDns],
      ['ZingerBastionStack', zinger.bastion],
      ['ZingerDbStack', zinger.db],
      ['ZingerEcrStack', zinger.ecr],
      ['ZingerEcsStack', zinger.ecs],
      ['ZingerIamStack', zinger.iam],
      ['ZingerRedisStack', zinger.redis],
      ['ZingerS3Stack', zinger.s3],
      ['ZingerSecurityGroupStack', zinger.securityGroup],
      ['ZingerSqsStack', zinger.sqs],
      ['ZingerVpcStack', zinger.vpc],
      ['GithubActionsStack', zinger.githubActions],
      ['RedashStack', zinger.redash]
    ])('%s', (_, stack) => {
      it('should be deployed correctly', () => {
        expect(Template.fromStack(stack).toJSON()).toMatchSnapshot()
      })
    })
  })
})
