<?php
/**
 * Created by mikaelp on 16-Jul-18 3:34 PM
 */

namespace Azimut\Bundle\DoctrineExtraBundle\DataCollector;


use Azimut\Bundle\CmsBundle\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector as BaseDoctrineDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces the base DoctrineDataCollector and removes the error on Comment entity
 */
class DoctrineDataCollector extends BaseDoctrineDataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        parent::collect($request, $response, $exception);
        foreach ($this->data['errors'] as $entityManagerName => $errors) {
            unset($this->data['errors'][$entityManagerName][Comment::class]);
        }
    }

}
