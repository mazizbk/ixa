<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-01-25 22:21:41
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileDocumentList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileDocumentListType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileDocumentList::class,
            'error_bubbling' => false
        ));
    }
}
