<?php

namespace Gino\Phplib\Signature;

abstract class AbstractSignature implements ISignature {

    protected $secret_key      = 'signature';
    protected $context         = null;
    protected $assemble_result = null;

    /**
     * @inheritDoc
     */
    public function setSecretKey($key): ISignature {
        $this->secret_key = $key;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSecretKey() {
        return $this->secret_key;
    }

    /**
     * @param mixed $context
     * @return $this
     */
    public function setContext($context): ISignature {
        $this->clear();
        $this->context = $context;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function verify(string $signature, string &$result = null): bool {
        $result = $this->sign();
        return $result === $signature;
    }

    /**
     * @inheritDoc
     */
    public function sign(): string {
        return md5($this->assemble());
    }


    /**
     * 清理数据
     *
     * @return $this
     */
    protected function clear() {
        $this->context         = null;
        $this->assemble_result = null;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function assemble(): string {
        if (!is_null($this->assemble_result)) {
            return $this->assemble_result;
        }
        return ($this->assemble_result = $this->_assemble());
    }

    /**
     * 组装数据
     *
     * @return string
     */
    protected abstract function _assemble(): string;

}