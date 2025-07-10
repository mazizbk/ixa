<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-11 10:49:24
 */

namespace Azimut\Bundle\ModerationBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class CmsFileConverterProperty
{
    /**
     * Target property name
     *
     * @var string
     */
    public $targetName;
}
