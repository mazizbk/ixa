<?php
/**
 * Created by mikaelp on 01-Aug-18 9:50 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\ParamConverter;


use Azimut\Bundle\MontgolfiereAppBundle\Controller\AbstractBackofficeSubEntityController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BackofficeSubEntityConverter implements ParamConverterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $controller = $request->attributes->get('_controller');
        list($controllerClass, ) = explode('::', $controller);
        $controllerInstance = $this->container->get($controllerClass);
        if(!$controllerInstance instanceof AbstractBackofficeSubEntityController) {
            throw new \InvalidArgumentException('Controller must be an instance of AbstractBackofficeSubEntityController in order to use the azimut_backoffice_subentity param converter');
        }

        $controllerParam = $configuration->getName();

        switch($configuration->getName()) {
            case 'entity':
                $requestValue = $request->attributes->get($controllerInstance->getParentRouteParamName());
                $value = $controllerInstance->getEntity($requestValue);
                break;
            case 'subEntity':
                $requestValue = $request->attributes->get($controllerInstance->getSubEntityRouteParamName());
                $value = $controllerInstance->getSubEntity($requestValue);
                break;
            default:
                throw new \InvalidArgumentException('azimut_backoffice_subentity param converter can only convert parameters named entity and subEntity');
        }

        $request->attributes->set($controllerParam, $value);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'azimut_backoffice_subentity';
    }

}
