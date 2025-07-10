<?php

namespace Azimut\Bundle\DoctrineExtraBundle\Listener;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Finder\Finder;

/**
 * This event listener takes place when Doctrine loads metadata.
 *
 * It will inject dynamically inheritance maps.
 */
class DynamicInheritanceMapListener implements EventSubscriber
{
    const ANNOTATION_CLASS = DynamicInheritanceMap::class;

    protected $reader;
    protected $dirs = null;
    protected $cache = [];
    /**
     * @var array
     */
    private $bundles;

    /**
     * @param Reader    $reader
     * @param array     $bundles
     */
    public function __construct(Reader $reader, array $bundles)
    {
        $this->reader = $reader;
        $this->bundles = $bundles;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $e
     * @throws \ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $e)
    {
        $metadata = $e->getClassMetadata();
        $class = $metadata->getName();
        if(!$metadata->getReflectionClass()) {
            return;
        }

        $annotation = $this->reader->getClassAnnotation($metadata->getReflectionClass(), self::ANNOTATION_CLASS);

        // class not annotated
        if (null === $annotation) {
            return;
        }

        $discriminatorMap = array();
        $finder = Finder::create()->in($this->getDirs())->name('*.php');
        foreach ($finder as $file) {
            $otherClass = $this->findClass($file);
            if (false === $otherClass) {
                continue;
            }

            if ($otherClass != $class && !is_subclass_of($otherClass, $class)) {
                continue;
            }

            $discrValue = $this->getDiscriminatorValue($otherClass);

            $discriminatorMap[$discrValue] = $otherClass;
        }
        //erase actual discriminatorMap
        $metadata->discriminatorMap = null;
        //create the new one
        $metadata->setDiscriminatorMap($discriminatorMap);
    }

    protected function findClass(\SplFileInfo $file)
    {
        if(array_key_exists($file->getRealPath(), $this->cache)) {
            return $this->cache[$file->getRealPath()];
        }

        $className = $file->getRealPath();
        $src = 'src'.DIRECTORY_SEPARATOR;
        $className = substr($className, strpos($className, $src)+strlen($src), -4);
        $className = str_replace('/', '\\', $className);
        $this->cache[$file->getRealPath()] = $className;

        return $this->cache[$file->getRealPath()];
    }

    /**
     * @param $className
     * @return string|null
     * @throws \ReflectionException
     */
    protected function getDiscriminatorValue($className)
    {
        $refl = new \ReflectionClass($className);
        $annotation = $this->reader->getClassAnnotation($refl, DynamicInheritanceSubClass::class);

        return null === $annotation ? null : $annotation->discriminatorValue;
    }

    protected function getDirs()
    {
        if($this->dirs) {
            return $this->dirs;
        }

        // ideally, constructor should be passed a list of namespaces or folder to
        // search entities in.
        // For pragmatism reasons and because this is a POC, we convert bundles
        // to folders here.
        // TODO : complete this

        $this->dirs = array();
        foreach ($this->bundles as $bundle) {
            try {
                $refl = new \ReflectionClass($bundle);
            } catch (\ReflectionException $e) {
                continue;
            }
            $dir = dirname($refl->getFileName()).'/Entity';
            if (is_dir($dir)) {
                $this->dirs[] = $dir;
            }
        }

        return $this->dirs;
    }
}
