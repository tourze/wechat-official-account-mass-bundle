<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatOfficialAccountMassBundle\Controller\Admin\MassTaskCrudController;
use WechatOfficialAccountMassBundle\Entity\MassTask;

/**
 * 群发任务CRUD控制器测试
 *
 * @internal
 */
#[CoversClass(MassTaskCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MassTaskCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MassTaskCrudController
    {
        return self::getService(MassTaskCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'title' => ['任务名称'];
        yield 'account' => ['公众号账户'];
        yield 'type' => ['消息类型'];
        yield 'sendTime' => ['发送时间'];
        yield 'sent' => ['已发送'];
        yield 'status' => ['有效状态'];
        yield 'createTime' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'sendTime' => ['sendTime'];
        yield 'tagId' => ['tagId'];
        yield 'mediaId' => ['mediaId'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'sendTime' => ['sendTime'];
        yield 'tagId' => ['tagId'];
        yield 'mediaId' => ['mediaId'];
    }

    public function testEntityFqcn(): void
    {
        self::assertSame(MassTask::class, MassTaskCrudController::getEntityFqcn());
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new MassTaskCrudController();
        self::assertSame(MassTask::class, $controller::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        // 尝试不同的按钮选择器
        $submitButtonSelectors = [
            'button[type="submit"]',
            'input[type="submit"]',
            'button:contains("Save")',
            'button:contains("保存")',
            '.btn-primary',
        ];

        $form = null;
        foreach ($submitButtonSelectors as $selector) {
            $buttons = $crawler->filter($selector);
            if ($buttons->count() > 0) {
                $form = $buttons->form();
                break;
            }
        }

        if (null === $form) {
            // 如果找不到按钮，直接获取表单
            $forms = $crawler->filter('form');
            self::assertGreaterThan(0, $forms->count(), '应该有表单存在');
            $form = $forms->first()->form();
        }

        // 提交部分填写的表单以触发验证错误（避免类型错误）
        $form['MassTask[sendTime]'] = date('Y-m-d H:i:s');
        $crawler = $client->submit($form);

        // 验证响应状态码（可能是200也可能是422）
        $statusCode = $client->getResponse()->getStatusCode();
        self::assertThat(
            $statusCode,
            self::logicalOr(
                self::equalTo(200),  // 表单重新显示
                self::equalTo(422)   // 验证错误
            ),
            '应该返回表单重新显示或验证错误状态码'
        );

        // 验证页面中有错误信息或必填字段提示
        $pageContent = $crawler->html();
        self::assertThat(
            $pageContent,
            self::logicalOr(
                self::stringContains('invalid-feedback'),
                self::stringContains('form-error'),
                self::stringContains('error'),
                self::stringContains('required'),
                self::stringContains('必填')
            ),
            '页面应该包含验证相关的提示信息'
        );
    }

    public function testConfigureFields(): void
    {
        $controller = new MassTaskCrudController();
        $fields = $controller->configureFields('index');

        self::assertIsIterable($fields);

        // 验证字段配置不为空
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testNewPageShowsTextareaForContent(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        $entityName = $this->getEntitySimpleName();

        // 检查textarea元素存在（content字段是TextareaField）
        $textareaSelector = sprintf('form[name="%s"] textarea[name="%s[content]"]', $entityName, $entityName);
        self::assertGreaterThan(
            0,
            $crawler->filter($textareaSelector)->count(),
            'content字段的textarea应该存在'
        );

        // 检查标签存在
        $labelSelector = sprintf('label[for="%s_content"]', $entityName);
        $labelElements = $crawler->filter($labelSelector);
        self::assertGreaterThan(
            0,
            $labelElements->count(),
            'content字段的标签应该存在'
        );
    }

    public function testNewPageShowsRadioForType(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        $entityName = $this->getEntitySimpleName();

        // 检查radio元素存在（type字段是ChoiceField with radio）
        $radioSelector = sprintf('form[name="%s"] input[type="radio"][name="%s[type]"]', $entityName, $entityName);
        self::assertGreaterThan(
            0,
            $crawler->filter($radioSelector)->count(),
            'type字段的radio按钮应该存在'
        );

        // 检查按钮数量（应该有两个选项：文本和语音）
        self::assertSame(
            2,
            $crawler->filter($radioSelector)->count(),
            'type字段应该有两个选项'
        );
    }

    public function testNewPageShowsSelectForAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        $entityName = $this->getEntitySimpleName();

        // 检查select元素存在（account字段是AssociationField）
        $selectSelector = sprintf('form[name="%s"] select[name="%s[account]"]', $entityName, $entityName);
        self::assertGreaterThan(
            0,
            $crawler->filter($selectSelector)->count(),
            'account字段的select元素应该存在'
        );

        // 检查标签存在
        $labelSelector = sprintf('label[for="%s_account"]', $entityName);
        $labelElements = $crawler->filter($labelSelector);
        self::assertGreaterThan(
            0,
            $labelElements->count(),
            'account字段的标签应该存在'
        );
    }

    public function testNewPageShowsCheckboxForValidAndSent(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        $entityName = $this->getEntitySimpleName();

        // 检查checkbox元素存在（valid和sent字段是BooleanField）
        $validCheckboxSelector = sprintf('form[name="%s"] input[type="checkbox"][name="%s[valid]"]', $entityName, $entityName);
        self::assertGreaterThan(
            0,
            $crawler->filter($validCheckboxSelector)->count(),
            'valid字段的checkbox应该存在'
        );

        $sentCheckboxSelector = sprintf('form[name="%s"] input[type="checkbox"][name="%s[sent]"]', $entityName, $entityName);
        self::assertGreaterThan(
            0,
            $crawler->filter($sentCheckboxSelector)->count(),
            'sent字段的checkbox应该存在'
        );
    }
}
