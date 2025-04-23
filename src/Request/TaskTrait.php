<?php

namespace WechatOfficialAccountMassBundle\Request;

trait TaskTrait
{
    /**
     * 群发接口新增 send_ignore_reprint 参数，开发者可以对群发接口的 send_ignore_reprint 参数进行设置，指定待群发的文章被判定为转载时，是否继续群发。
     * 当 send_ignore_reprint 参数设置为1时，文章被判定为转载时，且原创文允许转载时，将继续进行群发操作。
     * 当 send_ignore_reprint 参数设置为0时，文章被判定为转载时，将停止群发操作。
     * send_ignore_reprint 默认为0。
     */
    private bool $sendIgnoreReprint = true;

    private array $message;

    /**
     * @var string|null 开发者侧群发msgid，长度限制64字节，如不填，则后台默认以群发范围和群发内容的摘要值做为clientmsgid
     */
    private ?string $clientMsgId = null;

    public function isSendIgnoreReprint(): bool
    {
        return $this->sendIgnoreReprint;
    }

    public function setSendIgnoreReprint(bool $sendIgnoreReprint): void
    {
        $this->sendIgnoreReprint = $sendIgnoreReprint;
    }

    public function getMessage(): array
    {
        return $this->message;
    }

    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    public function getClientMsgId(): ?string
    {
        return $this->clientMsgId;
    }

    public function setClientMsgId(?string $clientMsgId): void
    {
        $this->clientMsgId = $clientMsgId;
    }
}
