<?php

namespace WechatOfficialAccountMassBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 根据 OpenID 列表群发【订阅号不可用，服务号认证后可用】
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html
 */
class SendByOpenIdRequest extends WithAccountRequest
{
    use TaskTrait;

    private array $toUsers;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/message/mass/send';
    }

    public function getRequestOptions(): ?array
    {
        $json = [
            'touser' => $this->getToUsers(),
            'send_ignore_reprint' => $this->isSendIgnoreReprint() ? 1 : 0,
            ...$this->getMessage(),
        ];

        if (null !== $this->getClientMsgId()) {
            $json['clientmsgid'] = strval($this->getClientMsgId());
        }

        return [
            'json' => $json,
        ];
    }

    public function getToUsers(): array
    {
        return $this->toUsers;
    }

    public function setToUsers(array $toUsers): void
    {
        $this->toUsers = $toUsers;
    }
}
