<?php

namespace App\Traits\PageWithHaving;

trait PaginationWithHavings
{
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();
        return new BuilderWithPaginationHavingSupport(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }
}
