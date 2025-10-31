# WeChat Official Account Mass Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-official-account-mass-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-mass-bundle)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle for WeChat Official Account mass messaging functionality, supporting scheduled 
broadcasts to all users, specific user groups by tag, or individual users by OpenID.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [API Reference](#api-reference)
- [Advanced Usage](#advanced-usage)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)
- [References](#references)

## Features

- **Flexible Targeting**: Send messages to all users, specific user groups by tag, 
  or individual users by OpenID
- **Multiple Message Types**: Support for text and voice messages
- **Scheduled Broadcasting**: Schedule messages to be sent at specific times
- **Automatic Sending**: Built-in cron task for automatic message dispatch
- **Database-backed Task Management**: Persistent storage of tasks with status tracking
- **WeChat API Integration**: Full integration with WeChat Official Account API
- **Response Tracking**: Stores WeChat API response data (msg_id, msg_data_id)
- **Multi-account Support**: Handle multiple WeChat Official Accounts

## Installation

```bash
composer require tourze/wechat-official-account-mass-bundle
```

## Quick Start

### 1. Enable the Bundle

Register the bundle in your `config/bundles.php`:

```php
<?php

return [
    // ...
    WechatOfficialAccountMassBundle\WechatOfficialAccountMassBundle::class => ['all' => true],
];
```

### 2. Configure Database

Update your database schema to create the required tables:

```bash
php bin/console doctrine:schema:update --force
```

### 3. Create a Mass Task

```php
<?php

use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

// Create a text message task
$task = new MassTask();
$task->setTitle('Daily Newsletter');
$task->setType(MassType::TEXT);
$task->setContent('Hello, this is a mass message!');
$task->setSendTime(new \DateTimeImmutable('+1 hour'));
$task->setValid(true);

// For all users
$task->setTagId(null);
$task->setOpenIds([]);

// For specific tag
$task->setTagId('100');

// For specific users
$task->setOpenIds(['openid1', 'openid2']);

$entityManager->persist($task);
$entityManager->flush();
```

### 2. Send Messages via Command

The bundle provides a console command for sending mass messages:

```bash
php bin/console wechat:send-mass
```

This command will:
- Find all valid, unsent tasks where send time has passed
- Send messages via WeChat API
- Update task status and store response data

### 3. Automatic Sending with Cron

The `SendMassCommand` is configured to run automatically every minute using the cron job attribute:

```php
#[AsCronTask(expression: '* * * * *')]
class SendMassCommand extends Command
```

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher
- WeChat Official Account with API credentials

## Configuration

This bundle requires the `tourze/wechat-official-account-bundle` to be properly configured with your WeChat Official Account credentials.

### Required Environment Variables

Configure your WeChat credentials in `.env`:

```env
# WeChat Official Account Configuration
WECHAT_APP_ID=your_app_id
WECHAT_APP_SECRET=your_app_secret
```

## API Reference

### Entities

#### MassTask

Main entity for managing mass messaging tasks.

**Properties:**
- `title` (string): Task name for identification
- `type` (MassType): Message type (TEXT, VOICE)
- `content` (string): Message content for text messages
- `sendTime` (DateTimeImmutable): Scheduled send time
- `tagId` (string|null): Target tag ID for group messaging
- `openIds` (array): Target user OpenIDs for individual messaging
- `mediaId` (string|null): Media ID for voice messages
- `sent` (bool): Send status indicator
- `valid` (bool): Task validity flag
- `account` (Account|null): Associated WeChat Official Account
- `msgTaskId` (string|null): WeChat API response task ID
- `msgDataId` (string|null): WeChat API response data ID

**Methods:**
- `formatMessage()`: Formats message data for WeChat API
- Standard getters and setters for all properties

### Enums

#### MassType

Supported message types:
- `TEXT` = '1': Text messages
- `VOICE` = '3': Voice messages

### Repositories

#### MassTaskRepository

Extends Doctrine's ServiceEntityRepository for custom query methods.

### Request Classes

- `SendToAllRequest`: Send to all users
- `SendByTagRequest`: Send to users with specific tag
- `SendByOpenIdRequest`: Send to specific users by OpenID
- `PreviewTaskRequest`: Preview message before sending
- `DeleteTaskRequest`: Delete scheduled task

### Commands

#### wechat:send-mass

Sends scheduled mass messages. Configured to run automatically every minute via cron.

**Usage:**
```bash
php bin/console wechat:send-mass
```

**Process:**
1. Queries all valid, unsent tasks where send time has passed
2. Marks tasks as sent to prevent duplicate processing
3. Determines target audience (all users, by tag, or by OpenID)
4. Sends messages via WeChat API
5. Stores API response data (msg_task_id, msg_data_id)

## Advanced Usage

### Voice Message Example

```php
<?php

use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

$task = new MassTask();
$task->setTitle('Voice Announcement');
$task->setType(MassType::VOICE);
$task->setMediaId('your_uploaded_voice_media_id');
$task->setSendTime(new \DateTimeImmutable('+1 hour'));
$task->setValid(true);

$entityManager->persist($task);
$entityManager->flush();
```

### Multi-account Support

```php
<?php

use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Entity\MassTask;

// Assuming you have multiple accounts configured
$account = $accountRepository->findOneBy(['name' => 'main_account']);

$task = new MassTask();
$task->setAccount($account);
// ... other task configuration
```

### Task Status Monitoring

```php
<?php

// Check task status
$task = $taskRepository->find($taskId);

if ($task->isSent()) {
    echo "Task sent successfully";
    echo "WeChat Task ID: " . $task->getMsgTaskId();
    echo "WeChat Data ID: " . $task->getMsgDataId();
}
```

## Security

### Data Protection

- **Sensitive Information**: Never store WeChat API credentials in your source code. 
  Always use environment variables or secure configuration management.
- **Access Control**: Implement proper authentication and authorization for mass messaging 
  functionality to prevent unauthorized usage.
- **Input Validation**: All user inputs are validated using Symfony's validation constraints 
  to prevent malicious data injection.

### Best Practices

- **Rate Limiting**: Be aware of WeChat API rate limits to avoid account suspension.
- **Audit Logging**: Consider implementing audit logs for mass messaging activities.
- **User Consent**: Ensure you have proper consent from users before sending mass messages.
- **Content Monitoring**: Implement content review processes for mass messages to 
  comply with platform policies.

### Vulnerability Reporting

If you discover a security vulnerability, please report it to the project maintainers 
following responsible disclosure practices.

## Troubleshooting

### Common Issues

1. **Messages not sending**
    - Ensure cron job is running: Check system cron configuration
    - Verify task validity: Task must have `valid = true`
    - Check send time: Must be in the past for immediate sending

2. **API Errors**
    - Verify WeChat credentials in configuration
    - Check account permissions for mass messaging
    - Ensure media IDs are valid and not expired

3. **Database Issues**
    - Run `doctrine:schema:validate` to check entity mappings
    - Ensure database user has proper permissions

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Running Tests

```bash
./vendor/bin/phpunit packages/wechat-official-account-mass-bundle/tests
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## References

- [WeChat Official Account Mass Messaging API Documentation](https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html)
- [Symfony Bundle Documentation](https://symfony.com/doc/current/bundles.html)
- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/orm.html)
