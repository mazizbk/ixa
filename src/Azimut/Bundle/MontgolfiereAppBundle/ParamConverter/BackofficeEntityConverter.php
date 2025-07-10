<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\ParamConverter;


use Azimut\Bundle\MontgolfiereAppBundle\Controller\AbstractBackofficeEntityController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BackofficeEntityConverter implements ParamConverterInterface
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
        if(!$controllerInstance instanceof AbstractBackofficeEntityController) {
            throw new \InvalidArgumentException('Controller must be an instance of AbstractBackofficeEntityController in order to use the azimut_backoffice_entity param converter');
        }

        $controllerParam = $configuration->getName();
        $requestValue = $request->attributes->get($controllerInstance->getRouteParameterName());
        $value = $controllerInstance->getEntity($requestValue);

        $request->attributes->set($controllerParam, $value);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'azimut_backoffice_entity';
    }

}
