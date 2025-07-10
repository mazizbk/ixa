<?php
/**
 * User: goulven
 * Date: 05/08/2022
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationOpinion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpinionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question1', ChoiceType::class, [
                'required' => true,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question1.question',
                'choices' => [
                    'montgolfiere.frontoffice.personal_area.opinion_form.question1.answer1' => 1,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question1.answer2' => 2,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question1.answer3' => 3,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question1.answer4' => 4,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question1.answer5' => 5,
                ],
                'expanded' => true,
            ])
            ->add('question2', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question2',
            ])
            ->add('question3', ChoiceType::class, [
                'required' => true,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question3.question',
                'choices' => [
                    'montgolfiere.frontoffice.personal_area.opinion_form.question3.answer1' => 1,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question3.answer2' => 2,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question3.answer3' => 3,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question3.answer4' => 4,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question3.answer5' => 5,
                ],
                'expanded' => true,
            ])
            ->add('question4', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question4',
            ])
            ->add('question5', ChoiceType::class, [
                'required' => true,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question5.question',
                'choices' => [
                    'montgolfiere.frontoffice.personal_area.opinion_form.question5.answer1' => 1,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question5.answer2' => 2,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question5.answer3' => 3,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question5.answer4' => 4,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question5.answer5' => 5,
                ],
                'expanded' => true,
            ])
            ->add('question6', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question6',
            ])
            ->add('question7', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question7',
            ])
            ->add('question8', ChoiceType::class, [
                'required' => true,
                'label' => 'montgolfiere.frontoffice.personal_area.opinion_form.question8.question',
                'choices' => [
                    'montgolfiere.frontoffice.personal_area.opinion_form.question8.answer1' => 1,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question8.answer2' => 2,
                    'montgolfiere.frontoffice.personal_area.opinion_form.question8.answer3' => 3,
                ],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CampaignParticipationOpinion::class
        ));
    }
}