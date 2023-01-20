/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { tap } from '@zinger/helpers'
import {
  Duration,
  Stack,
  StackProps,
  aws_backup as backup,
  aws_certificatemanager as certificateManager,
  aws_ec2 as ec2,
  aws_elasticloadbalancingv2 as elbv2,
  aws_elasticloadbalancingv2_targets as elbv2Targets,
  aws_events as events,
  aws_iam as iam,
  aws_route53 as route53,
  aws_route53_targets as route53Targets
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import {
  PORT_HTTP, PORT_HTTPS,
  REDASH_ELASTIC_IP,
  REDASH_INSTANCE,
  REDASH_TARGET_GROUP,
  ZINGER_ALB
} from '~aws/variables'

export type RedashStackProps = StackProps & {
  machineImageName: string
  needRedash: boolean
  redashDomainName: string
  vpc: ec2.Vpc
  zoneDomainName: string
}

export class RedashStack extends Stack {
  constructor (scope: Construct, id: string, props: RedashStackProps) {
    super(scope, id, props)

    const { machineImageName, needRedash, redashDomainName, vpc, zoneDomainName } = props

    if (!needRedash) { return }

    //
    // Redash用 EC2 ROLE 定義
    //
    const role = (id: string, roleProps: iam.RoleProps): iam.IRole => {
      return new iam.Role(this, id, {
        ...roleProps
      })
    }
    const redashRole = role('RedashIamRole', {
      assumedBy: new iam.ServicePrincipal('ec2.amazonaws.com'),
      managedPolicies: [
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonS3FullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMManagedInstanceCore')
      ],
      roleName: 'ZingerRedashRole'
    })

    //
    // Redash用 securityGroup 定義
    //
    const securityGroup = tap(
      new ec2.SecurityGroup(this, 'RedashSecurityGroup', { vpc }),
      it => {
        it.addIngressRule(ec2.Peer.anyIpv4(), ec2.Port.tcp(PORT_HTTP))
      }
    )

    //
    // Redash用 EC2 定義
    //
    const redashInstance = new ec2.Instance(this, REDASH_INSTANCE, {
      blockDevices: [{
        deviceName: '/dev/sda1',
        volume: ec2.BlockDeviceVolume.ebs(20)
      }],
      instanceName: REDASH_INSTANCE,
      instanceType: new ec2.InstanceType('t3a.small'),
      machineImage: ec2.MachineImage.lookup({
        name: machineImageName
      }),
      role: redashRole,
      securityGroup,
      userDataCausesReplacement: false,
      vpc,
      vpcSubnets: { subnetGroupName: 'Public' }
    })

    //
    // Redash用 EC2 Elastic IP 定義
    //
    const eip = new ec2.CfnEIP(this, REDASH_ELASTIC_IP, {
      tags: [{ key: 'Name', value: REDASH_ELASTIC_IP }]
    })
    new ec2.CfnEIPAssociation(this, 'RedashElasticIpAssociation', {
      eip: eip.ref,
      instanceId: redashInstance.instanceId
    })

    //
    // Redash用 EC2 AWS Backup 定義
    //
    const plan = new backup.BackupPlan(this, 'RedashBackupPlan', {
      backupPlanName: 'RedashBackupPlan'
    })
    plan.addRule(new backup.BackupPlanRule({
      completionWindow: Duration.hours(2),
      startWindow: Duration.hours(1),
      scheduleExpression: events.Schedule.cron({
        hour: '17',
        minute: '35'
      }),
      deleteAfter: Duration.days(7)
    }))
    new backup.BackupSelection(this, 'RedashBackupSelection', {
      backupPlan: plan,
      resources: [
        backup.BackupResource.fromEc2Instance(redashInstance)
      ]
    })

    //
    // Redash用 Route 53 A/AAAA レコード定義
    //
    // 本来ならば ApplicationLoadBalancer のオブジェクトは、ZingerAlbDnsStack から参照することが望ましいが、
    // cdkを実行するとARN（実体）を取得できない参照エラーとなる為、作成済みのリソースから情報を取得する `〜.fromlookup`
    // からオブジェクトを取得する。
    const alb = elbv2.ApplicationLoadBalancer.fromLookup(this, 'RedashAlb', {
      loadBalancerTags: { Name: ZINGER_ALB }
    })
    const hostedZone = route53.HostedZone.fromLookup(this, 'RedashHostedZone', {
      domainName: zoneDomainName
    })
    const redashRoute53Record = {
      recordName: redashDomainName,
      target: route53.RecordTarget.fromAlias(
        new route53Targets.LoadBalancerTarget(alb)
      ),
      zone: hostedZone
    }
    new route53.ARecord(this, 'RedashARecord', redashRoute53Record)
    new route53.AaaaRecord(this, 'RedashAaaaRecord', redashRoute53Record)

    //
    // Redash用 ALB Target Group 定義
    //
    const redashTargetGroup = new elbv2.ApplicationTargetGroup(this, REDASH_TARGET_GROUP, {
      port: PORT_HTTP,
      targetGroupName: REDASH_TARGET_GROUP,
      targetType: elbv2.TargetType.INSTANCE,
      targets: [new elbv2Targets.InstanceTarget(redashInstance)],
      vpc
    })

    //
    // Redash用 ALBへ関連付けるSSL証明書 定義
    //
    const redashCertificate = new certificateManager.DnsValidatedCertificate(this, 'RedashCertificate', {
      domainName: redashDomainName,
      hostedZone,
      region: vpc.env.region
    })

    //
    // Redash用 Route53 & ALB
    //
    const listener = elbv2.ApplicationListener.fromLookup(this, 'RedashAlbListener', {
      loadBalancerArn: alb.loadBalancerArn,
      listenerPort: PORT_HTTPS,
      listenerProtocol: elbv2.ApplicationProtocol.HTTPS
    })
    listener.addCertificates('RedashAddCertificates', [redashCertificate])

    //
    // Redash用 ALB HTTP 接続定義 を追加
    //
    new elbv2.ApplicationListenerRule(this, 'RedashListenerRule', {
      conditions: [
        elbv2.ListenerCondition.hostHeaders([redashDomainName])
      ],
      listener,
      priority: 1,
      targetGroups: [redashTargetGroup]
    })
  }
}
