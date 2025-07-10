<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-03 10:24:50
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

interface SearchEngineProviderInterface
{
    /**
     * Returns the class name of provided content
     *
     * @return string
     */
    public function getProvidedClass();

    /**
     * Returns the DQL expression filter
     *
     * @return Query\Expr
     */
    public function getExpression($alias);

    /**
     * Append filters to select provided content to an existing query builder
     *
     * @return QueryBuilder
     */
    public function appendExpressionToQueryBuilder(QueryBuilder $qb, $alias);

    /**
     * Returns the complete query builder of provided content
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder($alias);

    /**
     * Returns the complete query of provided content
     *
     * @return Query
     */
    public function getQuery();

    /**
     * Returns the provided content
     *
     * @return array
     */
    public function getResults();

    /**
     * Return pages publishing provided content
     *
     * @return PageContent[]
     */
    public function getPublishingPageContents(Site $site);

    /**
     * Return the url fragment holding the content
     * (relative to the PageContent url)
     *
     * @return string
     */
    public function getContentPath();
}
