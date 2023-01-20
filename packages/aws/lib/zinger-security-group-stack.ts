/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { tap } from '@zinger/helpers'
import {
  Stack,
  aws_ec2 as ec2
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerSecurityGroupStackProps } from '~aws/lib/props'
import {
  PORT_HTTP,
  PORT_HTTPS,
  PORT_MYSQL,
  PORT_NGINX,
  PORT_REDIS,
  ZINGER_SECURITY_GROUP_ALB,
  ZINGER_SECURITY_GROUP_BASTION,
  ZINGER_SECURITY_GROUP_ECS,
  ZINGER_SECURITY_GROUP_MYSQL,
  ZINGER_SECURITY_GROUP_REDIS
} from '~aws/variables'

export class ZingerSecurityGroupStack extends Stack {
  public securityGroups: { [index: string]: ec2.SecurityGroup }

  constructor (scope: Construct, id: string, props: ZingerSecurityGroupStackProps) {
    super(scope, id, props)
    const { vpc } = props

    //
    // セキュリティグループ
    //
    const alb = tap(
      new ec2.SecurityGroup(this, ZINGER_SECURITY_GROUP_ALB, { vpc }),
      it => {
        it.addIngressRule(ec2.Peer.anyIpv4(), ec2.Port.tcp(PORT_HTTP))
        it.addIngressRule(ec2.Peer.anyIpv4(), ec2.Port.tcp(PORT_HTTPS))
      }
    )
    const bastion = new ec2.SecurityGroup(this, ZINGER_SECURITY_GROUP_BASTION, { vpc })
    const ecs = tap(
      new ec2.SecurityGroup(this, ZINGER_SECURITY_GROUP_ECS, { vpc }),
      it => {
        it.addIngressRule(alb, ec2.Port.tcp(PORT_NGINX))
        it.addIngressRule(bastion, ec2.Port.tcp(PORT_NGINX))
      }
    )
    const mysql = tap(
      new ec2.SecurityGroup(this, ZINGER_SECURITY_GROUP_MYSQL, { vpc }),
      it => {
        it.addIngressRule(ecs, ec2.Port.tcp(PORT_MYSQL))
        it.addIngressRule(bastion, ec2.Port.tcp(PORT_MYSQL))
      }
    )
    const redis = tap(
      new ec2.SecurityGroup(this, ZINGER_SECURITY_GROUP_REDIS, { vpc }),
      it => {
        it.addIngressRule(ecs, ec2.Port.tcp(PORT_REDIS))
        it.addIngressRule(bastion, ec2.Port.tcp(PORT_REDIS))
      }
    )
    this.securityGroups = {
      alb,
      bastion,
      ecs,
      mysql,
      redis
    }
  }
}
