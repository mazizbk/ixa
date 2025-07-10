<?php
/**
 * Created by mikaelp on 17-Sep-18 5:36 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationAnswer;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Form\DataTransformer\MontgolfiereQuestionValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class QuestionnaireQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Question $question */
        $question = $options['question'];
        switch($question->getType()) {
            case Question::TYPE_SLIDER_VALUE:
                $builder->add('value', IntegerType::class, [
                    'required' => !$question->getCanBeSkipped(),
                    'block_name' => 'question_value',
                    'label' => false,
                ]);
                $builder->get('value')->addModelTransformer(new MontgolfiereQuestionValueTransformer($question));
                break;
            case Question::TYPE_OPEN:
                $builder->add('openAnswer', TextareaType::class, [
                    'required' => !$question->getCanBeSkipped(),
                    'label' => 'montgolfiere.questionnaire.question.open_answer',
                ]);
                break;
            case Question::TYPE_TRUE_FALSE:
                $builder->add('openAnswer', ChoiceType::class, [
                    'required' => !$question->getCanBeSkipped(),
                    'label' => 'montgolfiere.questionnaire.question.open_answer',
                    'choices' => [
                        'montgolfiere.questionnaire.question.true_' => 'true',
                        'montgolfiere.questionnaire.question.false_' => 'false',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                ]);
                break;
            case Question::TYPE_CHOICES_MULTIPLES:
            case Question::TYPE_CHOICES_UNIQUE:
                $builder->add('openAnswer', ChoiceType::class, [
                    'required' => !$question->getCanBeSkipped(),
                    'placeholder' => false,
                    'label' => 'montgolfiere.questionnaire.question.open_answer',
                    'choices' => $this->extractChoices($question->getPossibleValues()),
                    'multiple' => $question->getType() === Question::TYPE_CHOICES_MULTIPLES,
                    'expanded' => true,
                    'attr' => [
                        'class' => 'choice-container',
                    ],
                ]);
                break;
            case Question::TYPE_SATISFACTION_GAUGE:
                $builder->add('openAnswer', IntegerType::class, [
                    'required' => !$question->getCanBeSkipped(),
                    'label' => 'montgolfiere.questionnaire.question.open_answer',
                    'scale' => 0,
                    'constraints' => [
                        new Range(['min' => 0, 'max' => $question->getGaugeMaxValue(),]),
                    ],
                    'attr' => [
                        'min' => 0,
                        'max' => $question->getGaugeMaxValue(),
                        'class' => 'satisfaction-slider',
                    ],
                ]);
                break;
        }
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
            /** @var Form $form */
            $form = $event->getForm();
            $data = $event->getData();
            if(!$data instanceof CampaignParticipationAnswer) {
                return;
            }

            if($data->getQuestion()->getType() !== Question::TYPE_SLIDER_VALUE && $data->getQuestion()->getCanBeSkipped() && (is_null($data->getOpenAnswer()) || empty($data->getOpenAnswer()))) {
                $data->setSkipped(true);
            }

            if($data->getQuestion()->getCanBeSkipped() && $form->getClickedButton()->getName() === 'skip') {
                $data->setSkipped(true);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CampaignParticipationAnswer::class,
            ])
            ->setRequired('question')
            ->setAllowedTypes('question', Question::class)
        ;
    }

    private function extractChoices($possibleValues)
    {
        $lines = explode("\n", $possibleValues);
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if(($separatorPos = strpos($line, '|')) === false) {
                $result[$line] = $line;
            }
            else {
                $result[substr($line, $separatorPos+1)] = substr($line, 0, $separatorPos);
            }
        }

        return $result;
    }

}
