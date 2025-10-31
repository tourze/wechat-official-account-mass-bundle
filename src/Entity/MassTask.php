<?php

namespace WechatOfficialAccountMassBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
    #[Assert\NotNull]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Account $account = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '任务名'])]
    private ?string $title = null;

    #[IndexColumn]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [MassType::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 10, nullable: false, enumType: MassType::class, options: ['default' => '1', 'comment' => '消息类型'])]
    private ?MassType $type = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: false, options: ['default' => '', 'comment' => '回复内容'])]
    private string $content;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '发送时间'])]
    private ?\DateTimeImmutable $sendTime = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '要发送的标签ID'])]
    private ?string $tagId = null;

    /**
     * @var string[]
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(nullable: true, options: ['comment' => '要发送的OpenID列表'])]
    private array $openIds = [];

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已发送'])]
    private ?bool $sent = null;

    #[Assert\Length(max: 120)]
    #[ORM\Column(length: 120, nullable: true, options: ['comment' => '媒体ID'])]
    private ?string $mediaId = null;

    #[Assert\Length(max: 60)]
    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '任务ID'])]
    private ?string $msgTaskId = null;

    #[Assert\Length(max: 60)]
    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '消息的数据ID'])]
    private ?string $msgDataId = null;

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTagId(): ?string
    {
        return $this->tagId;
    }

    public function setTagId(?string $tagId): void
    {
        $this->tagId = $tagId;
    }

    /**
     * @return string[]
     */
    public function getOpenIds(): array
    {
        return $this->openIds;
    }

    /**
     * @param string[] $openIds
     */
    public function setOpenIds(array $openIds): void
    {
        $this->openIds = $openIds;
    }

    public function getSendTime(): ?\DateTimeImmutable
    {
        return $this->sendTime;
    }

    public function setSendTime(?\DateTimeImmutable $sendTime): void
    {
        $this->sendTime = $sendTime;
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

    public function setSent(?bool $sent): void
    {
        $this->sent = $sent;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getMsgTaskId(): ?string
    {
        return $this->msgTaskId;
    }

    public function setMsgTaskId(?string $msgTaskId): void
    {
        $this->msgTaskId = $msgTaskId;
    }

    public function getMsgDataId(): ?string
    {
        return $this->msgDataId;
    }

    public function setMsgDataId(?string $msgDataId): void
    {
        $this->msgDataId = $msgDataId;
    }

    public function __toString(): string
    {
        return $this->msgTaskId ?? 'New MassTask';
    }

    /**
     * @return array<string, mixed>
     */
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
