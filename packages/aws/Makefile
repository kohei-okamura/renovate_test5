CDK = cdk
SHELL := /bin/bash

# 並列デプロイ対象外のスタック
CORE_STACKS := ZingerAlbDns ZingerIam ZingerSecurityGroup ZingerVpc

# 並列デプロイ対象のスタック
STACKS := ZingerBastion ZingerDb ZingerEcr ZingerEcs ZingerRedis ZingerS3 ZingerSqs GithubActions

# ↑ で定義したスタックの一覧を正しいスタック名に変換するユーザー関数
f = $(addsuffix Stack, $(foreach name, $(1), $(shell perl -ne 'print ucfirst' <<< $(name))))

# 並列デプロイ
all: $(call f, $(STACKS))

# 直列デプロイ
seq:
	yarn $(CDK) deploy --require-approval never $(call f, $(CORE_STACKS) $(STACKS))

# 並列化前にデプロイが必要なスタックをデプロイする
ZingerCoreStacks:
	yarn $(CDK) deploy --require-approval never $(call f, $(CORE_STACKS))

# 任意のスタックをデプロイする
Zinger%Stack: ZingerCoreStacks
	yarn $(CDK) deploy --require-approval never $@

# CDKで構築したリソースを破棄する
destroy:
	yarn $(CDK) destroy --require-approval never $(call f, $(CORE_STACKS) $(STACKS))
