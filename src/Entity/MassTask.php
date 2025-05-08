<?php

namespace WechatOfficialAccountMassBundle\Entity;

use AntdCpBundle\Builder\Action\ModalFormAction;
use AntdCpBundle\Builder\Field\InputTextField;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\ListAction;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Enum\MassType;
use WechatOfficialAccountMassBundle\Request\PreviewTaskRequest;

#[AsPermission(title: '群发任务')]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Table(name: 'ims_wechat_mass_record', options: ['comment' => '群发任务'])]
#[ORM\Entity]
class MassTask
{
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Account $account = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '任务名'])]
    private ?string $title = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: false, enumType: MassType::class, options: ['default' => 0, 'comment' => '消息类型'])]
    private ?MassType $type = null;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true, options: ['default' => '', 'comment' => '回复内容'])]
    private string $content;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '发送时间'])]
    private ?\DateTimeInterface $sendTime = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '要发送的标签ID'])]
    private ?string $tagId = null;

    #[ORM\Column(nullable: true, options: ['comment' => '要发送的OpenID列表'])]
    private array $openIds = [];

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已发送'])]
    private ?bool $sent = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $mediaId = null;

    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '任务ID'])]
    private ?string $msgTaskId = null;

    /**
     * 该字段只有在群发图文消息时，才会出现。可以用于在图文分析数据接口中，获取到对应的图文消息的数据，是图文分析数据接口中的 msgid 字段中的前半部分，详见图文分析数据接口中的 msgid 字段的介绍。
     */
    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '消息的数据ID'])]
    private ?string $msgDataId = null;

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTagId(): ?string
    {
        return $this->tagId;
    }

    public function setTagId(?string $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }

    public function getOpenIds(): array
    {
        return $this->openIds;
    }

    public function setOpenIds(?array $openIds): self
    {
        $this->openIds = $openIds;

        return $this;
    }

    public function getSendTime(): ?\DateTimeInterface
    {
        return $this->sendTime;
    }

    public function setSendTime(\DateTimeInterface $sendTime): self
    {
        $this->sendTime = $sendTime;

        return $this;
    }

    public function getType(): ?MassType
    {
        return $this->type;
    }

    public function setType(?MassType $type): void
    {
        $this->type = $type;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isSent(): ?bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): self
    {
        $this->sent = $sent;

        return $this;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): self
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    public function getMsgTaskId(): ?string
    {
        return $this->msgTaskId;
    }

    public function setMsgTaskId(?string $msgTaskId): self
    {
        $this->msgTaskId = $msgTaskId;

        return $this;
    }

    public function getMsgDataId(): ?string
    {
        return $this->msgDataId;
    }

    public function setMsgDataId(?string $msgDataId): self
    {
        $this->msgDataId = $msgDataId;

        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function formatMessage(): array
    {
        if (MassType::TEXT === $this->getType()) {
            return [
                'msgtype' => 'text',
                'text' => [
                    'content' => $this->getContent(),
                ],
            ];
        }
        if (MassType::VOICE === $this->getType()) {
            return [
                'msgtype' => 'voice',
                'voice' => [
                    'media_id' => $this->getMediaId(),
                ],
            ];
        }

        // TODO 更多数据类型
        return [];
    }

    #[ListAction(title: '预览')]
    public function renderPreviewAction(): ModalFormAction
    {
        return ModalFormAction::gen()
            ->setFormTitle('预览')
            ->setLabel('预览')
            ->setFormFields([
                InputTextField::gen()
                    ->setId('openId')
                    ->setLabel('OpenID')
                    ->setInputProps([
                        'style' => [
                            'width' => '100%',
                        ],
                    ]),
            ])
            ->setCallback(function (
                array $form,
                array $record,
                OfficialAccountClient $client,
            ) {
                $request = new PreviewTaskRequest();
                $request->setToUser($form['openId']);
                $request->setMessage($this->formatMessage());
                $client->request($request);

                return [
                    '__message' => '操作成功，请在手机微信查看',
                ];
            });
    }
}
