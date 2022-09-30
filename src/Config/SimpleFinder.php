<?php

namespace Gino\Phplib\Config;

class SimpleFinder implements IFinder {

    /**
     * @inheritDoc
     */
    public function find(string $dir, string $scope): array {
        return [$dir . DIRECTORY_SEPARATOR . $scope];
    }

}