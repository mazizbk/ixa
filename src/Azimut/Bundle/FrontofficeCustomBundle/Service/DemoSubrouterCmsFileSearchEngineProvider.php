<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-03 10:23:33
 */

namespace Azimut\Bundle\FrontofficeCustomBundle\Service;

use Azimut\Bundle\FrontofficeBundle\Service\AbstractSearchEngineProvider;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;

class DemoSubrouterCmsFileSearchEngineProvider extends AbstractSearchEngineProvider
{
    protected $routerControllerName = 'AzimutFrontofficeCustomBundle:DemoSubrouter:index';
    protected $contentPath = 'demo-subroute';
    protected $providedClass = CmsFile::class;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry);
    }

    public function getExpression($alias)
    {
        $repository = $this->registry->getManager()->getRepository(CmsFile::class);
        return $repository->getFilterFindByTypeHavingValidPublicationDates($alias);
    }

    public function appendExpressionToQueryBuilder(QueryBuilder $qb, $alias, $parameterPrefix = '')
    {
        $qb
            ->andWhere($this->getExpression($alias))
            //->setParameter($parameterPrefix.'type', CmsFileArticle::getCmsFileType())
        ;

        return [
            $parameterPrefix.'type' => CmsFileArticle::getCmsFileType(),
        ];
    }

    public function getQueryBuilder($alias = 'c')
    {
        $qb = $this->registry->getManager()
            ->createQueryBuilder($alias)
            ->select($alias)
            ->from('AzimutCmsBundle:CmsFile', $alias)
        ;

        $parameters = $this->appendExpressionToQueryBuilder($qb, $alias);

        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        return $qb;
    }

}
