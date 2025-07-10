<?php

namespace Azimut\Bundle\FrontofficeBundle\Nelmio;

use Nelmio\ApiDocBundle\Parser\FormTypeParser as BaseFormTypeParser;
use Symfony\Component\Form\FormFactoryInterface;

class FormTypeParser extends BaseFormTypeParser
{
    public function __construct(FormFactoryInterface $formFactory, $entityToChoice)
    {
        parent::__construct($formFactory, $entityToChoice);

        $this->mapTypes['i18n_textarea'] = 'string[]';
    }
}
