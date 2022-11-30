<?php

namespace Gino\Phplib\Signature;

class FulltextSignature extends AbstractSignature {

    /**
     * @inheritDoc
     */
    protected function _assemble(): string {
        $data = $this->getContext();
        if (!is_string($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $data = (string)$data;
        }
        return $data . $this->getSecretKey();
    }

}