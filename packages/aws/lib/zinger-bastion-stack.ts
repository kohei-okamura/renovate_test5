/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  aws_ec2 as ec2
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerBastionStackProps } from '~aws/lib/props'
import {
  ZINGER_BASTION,
  ZINGER_BASTION_ELASTIC_IP,
  ZINGER_BASTION_ELASTIC_IP_ASSOCIATION
} from '~aws/variables'

export class ZingerBastionStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerBastionStackProps) {
    super(scope, id, props)
    const { securityGroup, subnetGroupName, vpc } = props

    //
    // EC2 (Bastion)
    //
    const bastion = new ec2.Instance(this, ZINGER_BASTION, {
      instanceName: ZINGER_BASTION,
      instanceType: new ec2.InstanceType('t2.micro'),
      keyName: props.keyName,
      machineImage: new ec2.AmazonLinuxImage({
        generation: ec2.AmazonLinuxGeneration.AMAZON_LINUX_2
      }),
      role: props.bastionRole,
      securityGroup,
      vpc,
      vpcSubnets: { subnetGroupName }
    })
    // EC2（Bastion）のユーザーデータは、初回起動時の起動サイクルのみ実行される。
    // 修正したユーザーデータをプロビジョニングする時の動作は、修正内容を反映した新規のEC2を立上げ、既存のEC2を削除する。
    bastion.userData.addCommands(
      // パッケージをアップデート
      'yum update -y',
      // MySQLコマンドをインストール
      'yum install -y https://dev.mysql.com/get/mysql80-community-release-el7-3.noarch.rpm',
      'yum-config-manager --enable mysql80-community',
      'yum install -y mysql-community-client',
      // Redisコマンドをインストール
      'amazon-linux-extras install -y redis4.0',
      'yum install -y stunnel',
      // ホストの作成
      `hostnamectl set-hostname ${props.bastionHostname}`
    )

    //
    // Elastic IP
    //
    const eip = new ec2.CfnEIP(this, ZINGER_BASTION_ELASTIC_IP, {
      tags: [{ key: 'Name', value: ZINGER_BASTION_ELASTIC_IP }]
    })
    new ec2.CfnEIPAssociation(this, ZINGER_BASTION_ELASTIC_IP_ASSOCIATION, {
      eip: eip.ref,
      instanceId: bastion.instanceId
    })
  }
}
