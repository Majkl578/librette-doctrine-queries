<?php

declare(strict_types=1);

namespace UselessSoft\Queries\Doctrine\Query;

use Doctrine\ORM\QueryBuilder;
use Kdyby\StrictObjects\Scream;
use UselessSoft\Queries\Doctrine\QueryObjectHandler;
use UselessSoft\Queries\Doctrine\QueryObjectHandlerTrait;
use UselessSoft\Queries\Doctrine\QueryObjectInterface;
use UselessSoft\Queries\QueryInterface;

class SelectQueryHandler extends QueryObjectHandler
{
    use QueryObjectHandlerTrait;
    use Scream;

    protected function createQuery(QueryObjectInterface $queryObject) : QueryBuilder
    {
        assert($queryObject instanceof SelectQuery);

        $qb = $this->getQueryable()->createQueryBuilder($queryObject->getEntityClass(), 'e');

        foreach ($queryObject->getFilters() as $filter) {
            list ($field, $value) = $filter;
            if ($value === NULL && $field instanceof \Closure) {
                $field($qb, 'e');
            } else {
                $paramName = 'param_' . (count($qb->getParameters()) + 1);
                $fieldName = strpos($field, '.') === false ? sprintf('%s.%s', $qb->getRootAliases()[0], $field) : $field;
                $qb->andWhere(sprintf('%s = :%s', $fieldName, $paramName));
                $qb->setParameter($paramName, $value);
            }
        }
        foreach ($queryObject->getOrderBy() as $field => $direction) {
            $qb->addOrderBy($field, $direction);
        }

        return $qb;
    }

    public function supports(QueryInterface $query) : bool
    {
        return $query instanceof SelectQuery;
    }
}
