<?php

namespace WechatOfficialAccountMassBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 删除群发【订阅号与服务号认证后均可用】
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html
 */
class DeleteTaskRequest extends WithAccountRequest
{
    /**
     * @var string 发送出去的消息ID
     */
    private string $msgId;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/message/mass/delete';
    }

    public function getRequestOptions(): ?array
    {
        $json = [
            'msg_id' => $this->getMsgId(),
        ];

        return [
            'json' => $json,
        ];
    }

    public function getMsgId(): string
    {
        return $this->msgId;
    }

    public function setMsgId(string $msgId): void
    {
        $this->msgId = $msgId;
    }
}
