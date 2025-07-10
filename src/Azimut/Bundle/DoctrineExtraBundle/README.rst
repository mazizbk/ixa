AzimutDoctrineExtraBundle
=========================

Dynamic inheritance map
=======================

The class you want to generate dynamically inheritance clasmap:

.. code-block:: php

    use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\DiscriminatorColumn(name="discr", type="string")
     * @ORM\InheritanceType("JOINED")
     *
     * @DynamicInheritanceMap
     */
    abstract class AbstractContent
    {
        // ...
    }

And in subclasses you want to aggregate:

.. code-block:: php

    /**
     * @ORM\Entity
     *
     * @DynamicInheritanceSubClass
     */
    class Page extends AbstractContent
    {
        // ...
    }
