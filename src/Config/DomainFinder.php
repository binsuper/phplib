<?php

namespace Gino\Phplib\Config;

class DomainFinder implements IFinder {

    /**
     * @inheritDoc
     */
    public function find(string $dir, string $scope): array {
        $settled = $dir . DIRECTORY_SEPARATOR . '.domain';
        if (is_file($settled)) {
            $domain = trim(file_get_contents($settled));
        } else {
            $domain = get_server_host();
        }
        if (!$domain) {
            $domain = 'console';
        }

        return [
            $dir . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . $scope,
        ];
    }

}