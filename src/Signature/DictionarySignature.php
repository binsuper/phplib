<?php


namespace Gino\Phplib\Signature;

/**
 * 字典排序签名
 */
class DictionarySignature extends AbstractSignature {

    /**
     * @inheritDoc
     */
    protected function _assemble(): string {
        $data = $this->getContext();

        if (!is_array($data)) {
            return '';
        }

        // 1.排序
        ksort($data);

        // 2.构建明文
        array_walk($data, function (&$val, $key) {
            $value = $key . "=" . $val;
        });
        $text = implode('&', $data);

        // 3.拼接密钥
        $text .= $this->getSecretKey();

        return $text;
    }

}