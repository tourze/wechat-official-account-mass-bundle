<?php

namespace WechatOfficialAccountMassBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 不设置条件，群发
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html
 */
class SendToAllRequest extends WithAccountRequest
{
    use TaskTrait;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall';
    }

    public function getRequestOptions(): ?array
    {
        $json = [
            'filter' => [
                'is_to_all' => true,
            ],
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
}
