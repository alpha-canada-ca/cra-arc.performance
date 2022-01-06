<?php

// Code from https://github.com/dogukanakkaya/dawn-db

use Builder\Query;

interface DriverInterface
{
    public function getQueryBuilder(): Query;

    public function execute(string $sql, array $bindings);
}