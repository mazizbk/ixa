<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-23 15:47:55
 */

namespace Azimut\Bundle\FrontofficeAzimailingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class SubscriptionType extends AbstractType
{
    /**
     * @var int
     */
    private $accountId;

    /**
     * @var int
     */
    private $groupId;

    public function __construct($accountId, $groupId)
    {
        $this->accountId = $accountId;
        $this->groupId = $groupId;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction('https://www.extrazimut.net/azimailing/inscription.asp')
            ->add('ID_CPTE_AZIMAILING', HiddenType::class, [
                'data' => $this->accountId,
            ])
            ->add('ID_GRPE_ANNUAIRE', HiddenType::class, [
                'data' => $this->groupId,
            ])
            ->add('valider', HiddenType::class, [
                'data' => 'on',
            ])
            ->add('mail', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'your.email'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
