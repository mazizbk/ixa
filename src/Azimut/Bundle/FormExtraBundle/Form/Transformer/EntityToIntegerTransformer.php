<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-11-18 14:13:02
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Transformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToIntegerTransformer implements DataTransformerInterface
{
    private $em;
    private $entityClass;

    /**
    * @param EntityManager $em
    */
    public function __construct(EntityManager $em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
    * {@inheritdoc}
    */
    public function transform($entity)
    {
        if (null === $entity) {
            return "";
        }

        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            $entity = $this->em->getReference($this->entityClass, $entity->getId());
        }

        return (string) implode('-', $this->em->getUnitOfWork()->getEntityIdentifier($entity));
    }

    /**
    * {@inheritdoc}
    */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        // when find method is used, the repository provides an entity with all properties with null.
        $entity = $this->em->getRepository($this->entityClass)->find($id);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf(
                'Entity #%s of class "%s" does not exist!',
                $id,
                $this->entityClass
            ));
        }

        return $entity;
    }
}
