<?php

namespace Gino\Phplib\Config;

interface IFinder {

    /**
     * @param string $dir
     * @param string $scope
     * @return array
     */
    public function find(string $dir, string $scope): array;

}