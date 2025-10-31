# WeChat 微信公众号群发组件

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个用于微信公众号群发消息功能的 Symfony 组件，支持定时向所有用户、按标签向特定用户组
或通过 OpenID 向单个用户发送消息。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [系统要求](#系统要求)
- [配置](#配置)
- [API 参考](#api-参考)
- [高级用法](#高级用法)
- [安全性](#安全性)
- [故障排除](#故障排除)
- [贡献](#贡献)
- [许可证](#许可证)
- [参考文档](#参考文档)

## 功能特性

- **灵活的目标定位**：向所有用户、按标签向特定用户组或通过 OpenID 向单个用户发送消息
- **多种消息类型**：支持文本和语音消息
- **定时广播**：安排消息在特定时间发送
- **自动发送**：内置定时任务自动派发消息
- **基于数据库的任务管理**：持久化存储任务并跟踪状态
- **微信 API 集成**：与微信公众号 API 完全集成
- **响应跟踪**：存储微信 API 响应数据（msg_id、msg_data_id）
- **多账号支持**：处理多个微信公众号

## 安装

```bash
composer require tourze/wechat-official-account-mass-bundle
```

## 快速开始

### 1. 启用组件

在 `config/bundles.php` 中注册组件：

```php
<?php

return [
    // ...
    WechatOfficialAccountMassBundle\WechatOfficialAccountMassBundle::class => ['all' => true],
];
```

### 2. 配置数据库

更新数据库模式以创建所需的表：

```bash
php bin/console doctrine:schema:update --force
```

### 3. 创建群发任务

```php
<?php

use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

// 创建文本消息任务
$task = new MassTask();
$task->setTitle('日常通讯');
$task->setType(MassType::TEXT);
$task->setContent('您好，这是一条群发消息！');
$task->setSendTime(new \DateTimeImmutable('+1 hour'));
$task->setValid(true);

// 向所有用户发送
$task->setTagId(null);
$task->setOpenIds([]);

// 向特定标签发送
$task->setTagId('100');

// 向特定用户发送
$task->setOpenIds(['openid1', 'openid2']);

$entityManager->persist($task);
$entityManager->flush();
```

### 2. 通过命令发送消息

该组件提供了一个用于发送群发消息的控制台命令：

```bash
php bin/console wechat:send-mass
```

该命令将：
- 查找所有有效、未发送且发送时间已到的任务
- 通过微信 API 发送消息
- 更新任务状态并存储响应数据

### 3. 使用定时任务自动发送

`SendMassCommand` 已配置为使用定时任务属性每分钟自动运行：

```php
#[AsCronTask(expression: '* * * * *')]
class SendMassCommand extends Command
```

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本
- 具有 API 凭据的微信公众号

## 配置

该组件需要 `tourze/wechat-official-account-bundle` 正确配置您的微信公众号凭据。

### 必需的环境变量

在 `.env` 中配置您的微信凭据：

```env
# 微信公众号配置
WECHAT_APP_ID=your_app_id
WECHAT_APP_SECRET=your_app_secret
```

## API 参考

### 实体

#### MassTask

用于管理群发消息任务的主要实体。

**属性：**
- `title` (string)：用于识别的任务名称
- `type` (MassType)：消息类型（TEXT、VOICE）
- `content` (string)：文本消息的消息内容
- `sendTime` (DateTimeImmutable)：计划发送时间
- `tagId` (string|null)：群组消息的目标标签 ID
- `openIds` (array)：单个消息的目标用户 OpenID
- `mediaId` (string|null)：语音消息的媒体 ID
- `sent` (bool)：发送状态指示器
- `valid` (bool)：任务有效性标志
- `account` (Account|null)：关联的微信公众号
- `msgTaskId` (string|null)：微信 API 响应任务 ID
- `msgDataId` (string|null)：微信 API 响应数据 ID

**方法：**
- `formatMessage()`：为微信 API 格式化消息数据
- 所有属性的标准 getter 和 setter 方法

### 枚举

#### MassType

支持的消息类型：
- `TEXT` = '1'：文本消息
- `VOICE` = '3'：语音消息

### 仓储

#### MassTaskRepository

继承自 Doctrine 的 ServiceEntityRepository，提供自定义查询方法。

### 请求类

- `SendToAllRequest`：向所有用户发送
- `SendByTagRequest`：向具有特定标签的用户发送
- `SendByOpenIdRequest`：通过 OpenID 向特定用户发送
- `PreviewTaskRequest`：发送前预览消息
- `DeleteTaskRequest`：删除计划任务

### 命令

#### wechat:send-mass

发送计划的群发消息。配置为通过定时任务每分钟自动运行。

**使用方法：**
```bash
php bin/console wechat:send-mass
```

**处理流程：**
1. 查询所有有效、未发送且发送时间已到的任务
2. 将任务标记为已发送以防止重复处理
3. 确定目标受众（所有用户、按标签或按 OpenID）
4. 通过微信 API 发送消息
5. 存储 API 响应数据（msg_task_id、msg_data_id）

## 高级用法

### 语音消息示例

```php
<?php

use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

$task = new MassTask();
$task->setTitle('语音公告');
$task->setType(MassType::VOICE);
$task->setMediaId('您已上传的语音媒体ID');
$task->setSendTime(new \DateTimeImmutable('+1 hour'));
$task->setValid(true);

$entityManager->persist($task);
$entityManager->flush();
```

### 多账号支持

```php
<?php

use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Entity\MassTask;

// 假设您已配置多个账号
$account = $accountRepository->findOneBy(['name' => 'main_account']);

$task = new MassTask();
$task->setAccount($account);
// ... 其他任务配置
```

### 任务状态监控

```php
<?php

// 检查任务状态
$task = $taskRepository->find($taskId);

if ($task->isSent()) {
    echo "任务发送成功";
    echo "微信任务 ID：" . $task->getMsgTaskId();
    echo "微信数据 ID：" . $task->getMsgDataId();
}
```

## 安全性

### 数据保护

- **敏感信息**：切勿在源代码中存储微信 API 凭据。始终使用环境变量或安全配置管理。
- **访问控制**：为群发消息功能实施适当的身份验证和授权，以防止未经授权的使用。
- **输入验证**：所有用户输入都使用 Symfony 验证约束进行验证，以防止恶意数据注入。

### 最佳实践

- **速率限制**：注意微信 API 速率限制，避免账号被暂停。
- **审计日志**：考虑为群发消息活动实施审计日志。
- **用户同意**：在发送群发消息之前确保您已获得用户的适当同意。
- **内容监控**：为群发消息实施内容审查流程，以符合平台政策。

### 漏洞报告

如果您发现安全漏洞，请按照负责任的披露实践向项目维护者报告。

## 故障排除

### 常见问题

1. **消息未发送**
    - 确保定时任务正在运行：检查系统 cron 配置
    - 验证任务有效性：任务必须设置 `valid = true`
    - 检查发送时间：必须是过去的时间才能立即发送

2. **API 错误**
    - 验证配置中的微信凭据
    - 检查账号的群发消息权限
    - 确保媒体 ID 有效且未过期

3. **数据库问题**
    - 运行 `doctrine:schema:validate` 检查实体映射
    - 确保数据库用户具有适当的权限

## 贡献

欢迎贡献！详情请参阅 [CONTRIBUTING.md](CONTRIBUTING.md)。

### 开发设置

1. Fork 该仓库
2. 创建您的功能分支（`git checkout -b feature/amazing-feature`）
3. 提交您的更改（`git commit -m '添加一些令人惊叹的功能'`）
4. 推送到分支（`git push origin feature/amazing-feature`）
5. 开启一个 Pull Request

### 运行测试

```bash
./vendor/bin/phpunit packages/wechat-official-account-mass-bundle/tests
```

## 许可证

MIT 许可证。请参阅 [License File](LICENSE) 了解更多信息。

## 参考文档

- [微信公众号群发消息 API 文档](https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html)
- [Symfony Bundle 文档](https://symfony.com/doc/current/bundles.html)
- [Doctrine ORM 文档](https://www.doctrine-project.org/projects/orm.html)
