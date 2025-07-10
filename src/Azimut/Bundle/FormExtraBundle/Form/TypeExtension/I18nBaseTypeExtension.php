<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-26 15:11:28
 */

namespace Azimut\Bundle\FormExtraBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\GenericMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilled;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nBaseType;

class I18nBaseTypeExtension extends AbstractTypeExtension
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // required fields are accessible on the root entity of the form
        $rootData = $form->getRoot()->getData();

        if (is_object($rootData)) {
            $dataClass = get_class($rootData);

            // for each root entity constraints
            /** @var GenericMetadata $metadata */
            $metadata = $this->validator->getMetadataFor($dataClass);
            foreach ($metadata->getConstraints() as $constraint) {
                if ($constraint instanceof LangFilled) {
                    // if the name of the field is in constraint, then set the required var
                    if (in_array($view->vars['name'], $constraint->requiredFields)) {
                        $view->vars['required'] = true;
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return I18nBaseType::class;
    }
}
