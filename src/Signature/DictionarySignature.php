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
        $text = $this->buildDictString($data);

        // 3.拼接密钥
        $text .= $this->getSecretKey();

        return $text;
    }

    protected function buildDictString(array $arr): string {
        array_walk($arr, function (&$val, $key) {
            if (is_array($val)) {
                $val = $this->buildDictString($val);
            }
            $val = $key . "=" . $val;
        });
        return implode('&', $arr);
    }

}