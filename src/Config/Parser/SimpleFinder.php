<?php

namespace Gino\Phplib\Config\Parser;

class SimpleFinder implements IFinder {

    /**
     * @inheritDoc
     */
    public function find(string $dir, string $scope): string {
        return $dir . DIRECTORY_SEPARATOR . $scope;
    }

}