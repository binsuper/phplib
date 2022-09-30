<?php

namespace Gino\Phplib\Config;

class DomainFinder implements IFinder {

    /**
     * @inheritDoc
     */
    public function find(string $dir, string $scope): array {
        $directory = get_server_host();
        if (!$directory) {
            $directory = 'console';
        }

        return [
            $dir . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $scope,
        ];
    }

}