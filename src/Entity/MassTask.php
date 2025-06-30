<?php

namespace WechatOfficialAccountMassBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Enum\MassType;

#[ORM\Table(name: 'ims_wechat_mass_record', options: ['comment' => '群发任务'])]
#[ORM\Entity]
class MassTask implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '发送时间'])]
    private ?\DateTimeImmutable $sendTime = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '要发送的标签ID'])]
    private ?string $tagId = null;

    #[ORM\Column(nullable: true, options: ['comment' => '要发送的OpenID列表'])]
    private array $openIds = [];

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已发送'])]
    private ?bool $sent = null;

    #[ORM\Column(length: 120, nullable: true, options: ['comment' => '媒体ID'])]
    private ?string $mediaId = null;

    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '任务ID'])]
    private ?string $msgTaskId = null;

    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '消息的数据ID'])]
    private ?string $msgDataId = null;

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

    public function getSendTime(): ?\DateTimeImmutable
    {
        return $this->sendTime;
    }

    public function setSendTime(\DateTimeImmutable $sendTime): self
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

    public function __toString(): string
    {
        return $this->msgTaskId ?? 'New MassTask';
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
}
