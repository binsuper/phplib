<?php

namespace Gino\Phplib\Signature;

interface ISignature {

    /**
     * 设置签名密钥
     *
     * @param mixed $key
     * @return $this
     */
    public function setSecretKey($key): ISignature;

    /**
     * 返回签名密钥
     *
     * @return mixed
     */
    public function getSecretKey();

    /**
     * 设置要签名的数据
     *
     * @param mixed $context
     * @return $this
     */
    public function setContext($context): ISignature;

    /**
     * 返回要签名的数据
     *
     * @return mixed
     */
    public function getContext();

    /**
     * 组装数据
     *
     * @return string
     */
    public function assemble(): string;

    /**
     * 签名
     *
     * @param $data
     * @param $key
     * @return string
     */
    public function sign(): string;

    /**
     * 验签
     *
     * @param string $signature
     * @return bool
     */
    public function verify(string $signature, string &$result = null): bool;

}