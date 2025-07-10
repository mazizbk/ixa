<?php

namespace Azimut\Bundle\DoctrineExtraBundle\Tests\Validator\Constraints;

use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilled;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @group azsystem
 */
class LangFilledValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var LangFilledValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->createMock(ExecutionContext::class);

        $this->validator = new LangFilledValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    /**
     * @covers \Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator::validate
     */
    public function testValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $obj = new TestObject();
        $obj->setTitle('Foo', 'fr');
        $obj->setContent('Foo', 'fr');

        $this->validator->validate($obj, new LangFilled(array('requiredFields' => array('title', 'content'))));
    }

    /**
     * @covers \Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator::validate
     */
    public function testWhenNoLocaleGivenExplicitly()
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('langfilled.no.translated.locale')
        ;

        $this->validator->validate(new TestObject(), new LangFilled(array('requiredFields' => array('title', 'content'))));
    }

    /**
     * @covers \Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator::validate
     */
    public function testWhenSetterWithEmptyArgGiven()
    {
        $object = new TestObject();
        $object->setTitle('My title', 'en');
        $object->setContent('My content', 'en');
        // The Form component will fire all setters even those not present on request.
        $object->setTitle(null, 'fr');
        $object->setContent(null, 'fr');

        $this->context->expects($this->never())
            ->method('buildViolation')
        ;

        $this->validator->validate($object, new LangFilled(array('requiredFields' => array('title', 'content'))));

        $object->setTitle('', 'fr');
        $object->setContent('', 'fr');
        $this->validator->validate($object, new LangFilled(array('requiredFields' => array('title', 'content'))));

        $object->setTitle('   ', 'fr');
        $object->setContent('   ', 'fr');
        $this->validator->validate($object, new LangFilled(array('requiredFields' => array('title', 'content'))));
    }

    /**
     * @covers \Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator::validate
     */
    public function testWhenIncompleteTranslationWithoutLocaleGivenExplicitly()
    {
        $object = new TestObject();
        $object->setTitle('My title', 'en');
        $object->setContent('My content', 'en');
        $object->setTitle('Mon contenu', 'fr');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('langfilled.incomplete.locale')
            ->will($this->returnValue($this->getMockConstraintViolationBuilder()))
        ;

        $this->validator->validate($object, new LangFilled(array('requiredFields' => array('title', 'content'))));
    }

    /**
     * @covers \Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\LangFilledValidator::validate
     */
    public function testWhenIncompleteTranslationWithLocaleGivenExplicitly()
    {
        $object = new TestObject();
        $object->setTitle('My title', 'en');
        $object->setContent('My content', 'en');
        $object->setTitle('Mon contenu', 'fr');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('langfilled.incomplete.locale')
            ->will($this->returnValue($this->getMockConstraintViolationBuilder()))
        ;

        $this->validator->validate($object, new LangFilled(array('requiredFields' => array('title', 'content'), 'requiredLocales' => array('fr'))));
    }

    private function getMockConstraintViolationBuilder()
    {
        $cvb = $this->getMockBuilder(ConstraintViolationBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $cvb->expects($this->any())->method('atPath')->willReturn($cvb);
        $cvb->expects($this->any())->method('setParameter')->willReturn($cvb);
        $cvb->expects($this->any())->method('setInvalidValue')->willReturn($cvb);
        $cvb->expects($this->any())->method('addViolation')->willReturn($cvb);

        return $cvb;
    }
}

class TestObject implements TranslatableEntityInterface
{
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    static function getTranslationClass()
    {
        return TestObjectTranslation::class;
    }

    public function getTitle($locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->setTitle($title);
    }

    public function getContent($locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getContent();
    }

    public function setContent($title, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->setContent($title);
    }
}

class TestObjectTranslation
{
    private $translatable;
    private $locale;
    private $title;
    private $content;

    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
