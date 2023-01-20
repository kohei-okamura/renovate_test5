/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Duration,
  Stack,
  Tags,
  aws_certificatemanager as certificateManager,
  aws_elasticloadbalancingv2 as elbv2,
  aws_route53 as route53,
  aws_route53_targets as route53Targets
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerAlbDnsStackProps } from '~aws/lib/props'
import {
  PORT_HTTP,
  PORT_HTTPS,
  region,
  ZINGER_A_RECORD,
  ZINGER_AAAA_RECORD,
  ZINGER_ALB,
  ZINGER_CERTIFICATE,
  ZINGER_HOSTED_ZONE,
  ZINGER_HTTP_LISTENER,
  ZINGER_HTTP_LISTENER_RULE,
  ZINGER_HTTPS_LISTENER,
  ZINGER_TARGET_GROUP
} from '~aws/variables'

export class ZingerAlbDnsStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerAlbDnsStackProps) {
    super(scope, id, props)
    const { vpc, securityGroup } = props

    //
    // ALB 定義
    //
    const alb = new elbv2.ApplicationLoadBalancer(this, ZINGER_ALB, {
      internetFacing: true,
      loadBalancerName: ZINGER_ALB,
      securityGroup,
      vpc
    })
    alb.logAccessLogs(props.logStorage)
    Tags.of(alb).add('Name', ZINGER_ALB)

    //
    // Route 53 A/AAAA レコード定義
    //
    const hostedZone = route53.HostedZone.fromLookup(this, ZINGER_HOSTED_ZONE, {
      domainName: props.public.domainName
    })
    const propsForRoute53Record = {
      recordName: props.public.aRecord,
      target: route53.RecordTarget.fromAlias(
        new route53Targets.LoadBalancerTarget(alb)
      ),
      zone: hostedZone
    }
    new route53.ARecord(this, ZINGER_A_RECORD, propsForRoute53Record)
    new route53.AaaaRecord(this, ZINGER_AAAA_RECORD, propsForRoute53Record)

    //
    // ALB Target Group 定義
    //
    const targetGroup = new elbv2.ApplicationTargetGroup(this, ZINGER_TARGET_GROUP, {
      port: PORT_HTTP,
      stickinessCookieDuration: Duration.days(1),
      targetGroupName: ZINGER_TARGET_GROUP,
      targetType: elbv2.TargetType.IP,
      vpc
    })

    //
    // ALB HTTP 接続定義
    //
    const httpListener = alb.addListener(ZINGER_HTTP_LISTENER, {
      defaultTargetGroups: [targetGroup],
      port: PORT_HTTP,
      protocol: elbv2.ApplicationProtocol.HTTP
    })
    new elbv2.ApplicationListenerRule(this, ZINGER_HTTP_LISTENER_RULE, {
      action: elbv2.ListenerAction.redirect({
        permanent: true,
        port: PORT_HTTPS.toString(),
        protocol: elbv2.ApplicationProtocol.HTTPS
      }),
      conditions: [
        elbv2.ListenerCondition.pathPatterns(['*'])
      ],
      listener: httpListener,
      priority: 1
    })

    //
    // ALB HTTPS 接続定義
    //
    const certificate = new certificateManager.DnsValidatedCertificate(this, ZINGER_CERTIFICATE, {
      domainName: props.public.aRecord,
      hostedZone,
      region
    })
    alb.addListener(ZINGER_HTTPS_LISTENER, {
      certificates: [certificate],
      defaultTargetGroups: [targetGroup],
      port: PORT_HTTPS,
      protocol: elbv2.ApplicationProtocol.HTTPS
    })
  }
}
