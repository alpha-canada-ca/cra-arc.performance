<?php

// Code from https://github.com/dogukanakkaya/dawn-db

namespace Driver;

include "DriverInterface.php";
include "Query.php";

use Builder\Query;
use DriverInterface;
use SQLite3Result;

class Sqlite extends \SQLite3 implements DriverInterface
{
    private Query $builder;

    public function __construct(string $path = '')
    {
        $this->open($path);
        $this->builder = new Query($this);
    }

    public function getQueryBuilder(): Query
    {
        return $this->builder;
    }

    public function execute(string $sql, array $bindings = []): SQLite3Result
    {
        $statement = $this->prepare($sql);

        foreach ($bindings as $binding) {
            $statement->bindValue($binding[0], $binding[1]);
        }

        return $statement->execute();
    }

    public function transBegin(): void
    {
        $this->exec('BEGIN;');
    }

    public function transCommit(): void
    {
        $this->exec('COMMIT;');
    }
}