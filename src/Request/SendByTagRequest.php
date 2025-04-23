<?php

namespace WechatOfficialAccountMassBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 根据标签进行群发【订阅号与服务号认证后均可用】
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html
 */
class SendByTagRequest extends WithAccountRequest
{
    use TaskTrait;

    /**
     * @var string 标签ID
     */
    private string $tagId;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall';
    }

    public function getRequestOptions(): ?array
    {
        $json = [
            'filter' => [
                'is_to_all' => false,
                'tag_id' => $this->getTagId(),
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

    public function getTagId(): string
    {
        return $this->tagId;
    }

    public function setTagId(string $tagId): void
    {
        $this->tagId = $tagId;
    }
}
