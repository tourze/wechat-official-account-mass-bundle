<?php

namespace WechatOfficialAccountMassBundle\Request;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 预览任务
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html
 */
class PreviewTaskRequest extends WithAccountRequest
{
    use TaskTrait;

    private string $toUser;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/message/mass/preview';
    }

    public function getRequestOptions(): ?array
    {
        $json = [
            'touser' => $this->getToUser(),
            ...$this->getMessage(),
        ];

        return [
            'json' => $json,
        ];
    }

    public function getToUser(): string
    {
        return $this->toUser;
    }

    public function setToUser(string $toUser): void
    {
        $this->toUser = $toUser;
    }
}
