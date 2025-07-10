<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-25 11:24:49
 */

namespace Azimut\Bundle\TwigExtraBundle\Twig;

use Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AppExtension extends \Twig_Extension
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;
    /**
     * @var MediaDeclinationTagParser
     */
    private $mediaDeclinationTagParser;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct($mediaDeclinationTagParser,  PropertyAccessorInterface $propertyAccessor, RequestStack $requestStack, RegistryInterface $registry)
    {
        $this->mediaDeclinationTagParser = $mediaDeclinationTagParser;
        $this->propertyAccessor = $propertyAccessor;
        $this->request = $requestStack->getCurrentRequest();
        $this->registry = $registry;
    }

    public function getName()
    {
        return 'azimut_app';
    }

    public function getFunctions()
    {
        return [];
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sort_by', array($this, 'sortBy')),
            new \Twig_SimpleFilter('render_media_declination_tags', array($this, 'renderMediaDeclinationTags')),
            new \Twig_SimpleFilter('strip_media_declination_tags', array($this, 'stripMediaDeclinationTags')),
            new \Twig_SimpleFilter('limit_length', array($this, 'limitLength')),
            new \Twig_SimpleFilter('emphasis_words', [$this, 'emphasisWords',[
                'pre_escape' => 'html',
                'is_safe' => ['html']
            ]]),
        );
    }

    public function sortBy($object, $property)
    {
        if ($object instanceof PersistentCollection) {
            /** @var \ArrayIterator $iterator */
            $iterator = $object->getIterator();
            $iterator->uasort(function ($a, $b) use($property) {
                return $this->propertyAccessor->getValue($a, $property) > $this->propertyAccessor->getValue($b, $property);
            });

            $object->unwrap();
            $object->clear();
            foreach ($iterator as $item) {
                $object->add($item);
            }
        } elseif (is_array($object)) {
            usort($object, function ($a, $b) use ($property) {
                if (is_array($a)) {
                    //return strcmp($a[$property], $b[$property]);
                    return $a[$property] > $b[$property];
                }

                return $this->propertyAccessor->getValue($a, $property) > $this->propertyAccessor->getValue($b, $property);
            });
        }

        return $object;
    }

    public function renderMediaDeclinationTags($text)
    {
        return $this->mediaDeclinationTagParser->parse($text);
    }

    public function stripMediaDeclinationTags($text)
    {
        return $this->mediaDeclinationTagParser->stripTags($text);
    }

    public function limitLength($text, $limit)
    {
        $text = $this->mediaDeclinationTagParser->stripTags($text);
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit).'…';
        }

        return $text;
    }

    public function emphasisWords($text, $words, $tag = 'mark', $class = null)
    {
        $patterns = [];
        foreach ($words as $key => $word) {
            $patterns[$key] = '/('.$word.')/i';
        }

        $text = preg_replace($patterns, '<'. $tag . ($class ? ' class="'.$class.'"' : '') .'>$1</'. $tag .'>', $text);

        return $text;
    }
}
