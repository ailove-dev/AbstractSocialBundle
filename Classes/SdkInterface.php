<?php


namespace Ailove\AbstractSocialBundle\Classes;


interface SdkInterface
{
    public function getAppId();
    public function getAppSecret();
    public function getAccessToken();
    public function setAppId($appId);
    public function setAppSecret($appSecret);
    public function setAccessToken($accessToken);
}
