<?php

namespace Gino\Phplib\Config\Parser;

interface IFinder {

    /**
     * @param string $dir
     * @param string $scope
     * @return string
     */
    public function find(string $dir, string $scope): string;

}