<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-12 14:41:36
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DateTimePickerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Not using US format by default, because it is not the most used in the world, see https://en.wikipedia.org/wiki/Date_format_by_country
        // Not using \IntlDateFormatter::SHORT because it is not exactly the format we want

        $dateFormat = 'dd/MM/yyyy';
        $timeFormat = 'HH:mm';

        if ('en' == \Locale::getDefault()) {
            // $dateFormat = 'MM/dd/yyyy'; // US format, but not GB, as we don't split english locales, use the default one
            $timeFormat = 'hh:mm aa';
        }

        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => $dateFormat . ' ' . $timeFormat,
            'attr' => [
                'autocomplete' => 'off',
            ],
            'html5' => false,
            'hint' => 'hint.format.datetime.picker',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateTimeType::class;
    }
}
