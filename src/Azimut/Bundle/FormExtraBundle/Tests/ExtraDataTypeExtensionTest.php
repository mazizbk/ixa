<?php

namespace Azimut\Bundle\FormExtraBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @group azsystem
 */
class ExtraDataTypeExtensionTest extends WebTestCase
{
    private function getFormFactory()
    {
        self::createClient();

        return self::$kernel->getContainer()->get('form.factory');
    }

    /**
     * @coversNothing
     */
    public function testOptionInheritsInSubForm()
    {
        $builder = $this->getFormFactory()->createBuilder(FormType::class, null, array(
            'allow_form_extra_data' => true,
            'csrf_protection' => false,
        ));
        $builder->add($builder->create('child', FormType::class)->add('title', TextType::class));
        $form = $builder->getForm();

        $form->submit(array(
            'child' => array(
                'title' => 'expected value',
                'extra' => 'extra data'
        )));

        $this->assertTrue($form->isValid(), $form->getErrors(true, false));
    }

    /**
     * @coversNothing
     */
    public function testNoChild()
    {
        $form = $this->getFormFactory()->create(FormType::class, null, array(
            'csrf_protection' => false,
            'allow_form_extra_data' => true,
        ));

        $form->submit(array(
            'extra' => 'extra data'
        ));

        $this->assertTrue($form->isValid(), "Extra data does not throw an error.");
    }

    /**
     * @coversNothing
     */
    public function testDefaultDisabled()
    {
        $form = $this->getFormFactory()->create(FormType::class);
        $form->submit(array('extra' => 'extra data'));
        $this->assertFalse($form->isValid(), "Should not work");
    }
}
