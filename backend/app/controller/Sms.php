<?php
declare(strict_types=1);

namespace app\controller;

/** 短信域（代理到 User::sendSms） */
class Sms extends Base
{
    public function send() { return (new User())->sendSms(); }
}
