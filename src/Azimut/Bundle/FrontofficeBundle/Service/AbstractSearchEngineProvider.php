<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-03 12:22:00
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

abstract class AbstractSearchEngineProvider implements SearchEngineProviderInterface
{
    protected $registry;
    protected $routerControllerName;
    protected $contentPath;
    protected $providedClass;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function getProvidedClass()
    {
        return $this->providedClass;
    }

    abstract public function getExpression($alias);

    public function appendExpressionToQueryBuilder(QueryBuilder $qb, $alias)
    {
        return $qb
            ->orWhere($this->getExpression($alias))
        ;
    }

    public function getQueryBuilder($alias = 'c')
    {
        $qb = $this->registry->getManager()
            ->createQueryBuilder($alias)
        ;
        return $this->appendExpressionToQueryBuilder($qb, $alias);
    }

    public function getQuery()
    {
        return $this->getQueryBuilder()->getQuery();
    }

    public function getResults()
    {
        return $this->getQuery()->getResult();
    }

    public function getPublishingPageContents(Site $site = null)
    {
        if (null === $site) {
            return $this->registry->getManager()
                ->getRepository(PageContent::class)
                ->findByStandaloneRouterController($this->routerControllerName)
            ;
        }

        return $this->registry->getManager()
            ->getRepository(PageContent::class)
            ->findBySiteAndStandaloneRouterController($site, $this->routerControllerName)
        ;
    }

    public function getContentPath()
    {
        return $this->contentPath;
    }
}
