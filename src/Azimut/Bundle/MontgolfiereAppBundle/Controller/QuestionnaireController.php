<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationAnswer;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\HelpText;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\WBEText;
use Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber\UploadSubscriber;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\QuestionnaireBasicInformationType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\QuestionnaireQuestionType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class QuestionnaireController extends AbstractController
{
    const STEP_START = -2,
        STEP_INTRODUCTION = -1,
        STEP_BASIC_INFORMATION = 0,
        STEP_CONGRATULATIONS = 1000
    ;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    protected $fromAddress;
    protected $fromName;
    protected $sender;
    protected $replyTo;
    protected $alternativeContactFromRecipient;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var WBEManager
     */
    private $WBEManager;

    public function __construct(RequestStack $requestStack, FormFactoryInterface $formFactory, \Swift_Mailer $mailer, TranslatorInterface $translator, $fromAddress, $fromName, $sender, $replyTo, $alternativeContactFromRecipient, WBEManager $WBEManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->formFactory = $formFactory;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->sender = $sender;
        $this->replyTo = $replyTo;
        $this->WBEManager = $WBEManager;
        $this->alternativeContactFromRecipient = $alternativeContactFromRecipient;
    }

    public function indexAction(Campaign $campaign): Response
    {
        if($this->request->query->has('switchLocale') && in_array($locale = $this->request->query->get('switchLocale'), $campaign->getAllowedLanguages())) {
            $this->request->getSession()->set('questionnaire_locale', $locale);

            return $this->redirectToRoute('azimut_montgolfiere_questionnaire_index', ['questionnaireToken' => $campaign->getQuestionnaireToken(),]);
        }

        $locale = 'fr';
        if($this->request->getSession()->has('questionnaire_locale')) {
            $locale = $this->request->getSession()->get('questionnaire_locale');
        }
        if(!in_array($locale, $campaign->getAllowedLanguages())) {
            $locale = $campaign->getAllowedLanguages()[0];
        }

        $this->translator->setLocale($locale);

        if($campaign->getEndDate() && $campaign->getEndDate() < new \DateTime) {
            return $this->render('@AzimutMontgolfiereApp/Questionnaire/expired.html.twig', [
                'campaign' => $campaign,
            ]);
        }
        if($campaign->getStartDate() && $campaign->getStartDate() > new \DateTime) {
            return $this->render('@AzimutMontgolfiereApp/Questionnaire/not_open.html.twig', [
                'campaign' => $campaign,
            ]);
        }

        return $this->getStepResponse($campaign);
    }

    protected function getStepResponse(Campaign $campaign): Response
    {
        $step = $this->request->getSession()->get('participation_'.$campaign->getId().'_step', self::STEP_START);

        switch($step) {
            case self::STEP_START:
                return $this->stepStart($campaign);
            case self::STEP_INTRODUCTION:
                return $this->stepIntroduction($campaign);
            case self::STEP_BASIC_INFORMATION:
                return $this->stepBasicInformation($campaign);
            case self::STEP_CONGRATULATIONS:
                return $this->stepCongratulations($campaign);
        }
        if($step > 0) {
            $participation = $this->getParticipation($campaign);
            $segment = $participation->getSegment();
            if(!$segment) {
                // Sanity check: we should always have a segment here, but just in case we redirect to the step that asks for that segment
                $this->setSessionStep($campaign, self::STEP_BASIC_INFORMATION);
                return $this->redirectToRoute('azimut_montgolfiere_questionnaire_index', ['questionnaireToken' => $campaign->getQuestionnaireToken(),]);
            }
            $step = $segment->getStep($step);

            if($step->getType() === CampaignSegmentStep::TYPE_DIVIDER) {
                return $this->stepDivider($campaign, $step);
            }
            return $this->stepQuestion($campaign, $step);
        }

        throw new \LogicException('Step '.$step.' is not supported');
    }

    protected function setSessionStep(Campaign $campaign, $step): void
    {
        $this->request->getSession()->set('participation_'.$campaign->getId().'_step', $step);
    }

    protected function stepStart(Campaign $campaign): Response
    {
        $form = $this->formFactory->createNamedBuilder('start');
        $form->add('submit', SubmitType::class, [
            'label' => 'montgolfiere.questionnaire.start',
        ]);
        $form = $form->getForm();
        if($form->handleRequest($this->request)->isSubmitted()) {
            $this->setSessionStep($campaign, self::STEP_INTRODUCTION);

            return $this->getStepResponse($campaign);
        }

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/index.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
        ]);
    }

    protected function stepIntroduction(Campaign $campaign): Response
    {
        $form = $this->formFactory->createNamedBuilder('introduction');
        $form
            ->add('privacy_policy', CheckboxType::class, [
                'label' => 'montgolfiere.questionnaire.introduction.i_have_read_and_i_agree_the_tos',
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'montgolfiere.questionnaire.introduction.tos_required'
                    ]),
                ]
            ])
             ->add('submit', SubmitType::class, [
                'label' => 'montgolfiere.questionnaire.continue',
            ]);
        $form = $form->getForm();
        if($form->handleRequest($this->request)->isSubmitted() && $form->isValid()) {
            $this->setSessionStep($campaign, self::STEP_BASIC_INFORMATION);

            return $this->getStepResponse($campaign);
        }

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/introduction.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
            'locale' => $this->translator->getLocale(),
        ]);
    }

    protected function stepBasicInformation(Campaign $campaign): Response
    {
        $em = $this->getDoctrine()->getManager();
        $participation = new CampaignParticipation();
        $participation->setCampaign($campaign);
        if(count($validSegments = $campaign->getSegments()->filter(function(CampaignSegment $segment){return $segment->isValid() && $segment->getLocale() === $this->translator->getLocale();})) === 1) {
            $participation->setSegment($validSegments->first());
        }

        $form = $this->createForm(QuestionnaireBasicInformationType::class, $participation);
        if($form->handleRequest($this->request)->isSubmitted() && $form->isValid()) {
            $participation->setIPAddress($this->request->getClientIp());
            $em->persist($participation);
            $em->flush();

            $this->saveParticipationToSession($participation);
            $this->setSessionStep($campaign, 1);

            return $this->getStepResponse($campaign);
        }

        $affectations = [];
        $automaticAffectations = $em->getRepository(CampaignAutomaticAffectation::class)->getForCampaign($campaign);
        foreach ($automaticAffectations as $automaticAffectation) {
            if($automaticAffectation->getLocale() !== $this->translator->getLocale()) {
                continue;
            }
            $affectation = [];

            foreach ($automaticAffectation->getSortingFactorValues() as $sortingFactorValue) {
                $affectation[$sortingFactorValue->getSortingFactor()->getId()] = $sortingFactorValue->getId();
            }

            $affectations[] = $affectation;
        }

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/basic_information.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
            'affectations' => $affectations,
            'total_steps' => $this->getTotalSteps($participation),
            'current_progress' => 0,
            'steps_separators' => $this->getStepsSeparators($participation, null),
        ]);
    }

    protected function stepQuestion(Campaign $campaign, CampaignSegmentStep $step): Response
    {
        $em = $this->getDoctrine()->getManager();

        $participation = $this->getParticipation($campaign);

        /** @var Question $question */
        $segment = $participation->getSegment();

        $question = $step->getQuestion();
        if(!$question) {
            throw new \LogicException('Question step without a question');
        }

        // Get existing answer when going back
        $answer = $participation->getAnswer($step);
        if(!$answer) {
            $answer = new CampaignParticipationAnswer();
            $answer
                ->setParticipation($participation)
                ->setStep($step)
            ;
        }
        $answer->setQuestion($question); // This is required for validation

        $form = $this->formFactory->createNamedBuilder('step_'.$step->getPosition(), QuestionnaireQuestionType::class, $answer, [
            'question' => $question,
            'block_name' => 'questionnaire',
        ]);

        $form
            ->add('previous', SubmitType::class, [
                'label' => 'montgolfiere.questionnaire.question.previous_question',
                'disabled' => !self::canGoBack($step, $participation),
            ])
            ->add('skip', SubmitType::class, [
                'label' => 'montgolfiere.questionnaire.question.skip_question',
                'disabled' => !$question->getCanBeSkipped(),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'montgolfiere.questionnaire.question.continue',
                'attr' => [
                    'disabled' => !$question->getCanBeSkipped() && $answer->getId()===null,
                ],
            ])
        ;
        $form = $form->getForm();
        /** @var Form $form */

        if($form->handleRequest($this->request)->isSubmitted()) {
            $action = $form->getClickedButton()->getName();
            switch($action) {
                case 'previous':
                    // This button can't be pressed if we can't go back (and Symfony enforces it), we can simply remove one step
                    $this->setSessionStep($campaign, $step->getPosition()-1);

                    return $this->getStepResponse($campaign);
                case 'skip':
                case 'submit':
                    if($form->isValid()) {
                        $participation->addAnswer($answer);
                        $em->persist($answer);
                        $em->flush();

                        $step = self::getNextStep($segment, $step);
                        $this->setSessionStep($campaign, $step);

                        return $this->getStepResponse($campaign);
                    }
                    break;
            }
        }

        $tooltipText = null;

        if($tooltip = $em->getRepository(Tooltip::class)->findOneBy(['locale' => $this->translator->getLocale(), 'item' => $step->getItem(),])) {
            $tooltipText = $tooltip->getText();
        }
        $tooltipText = $tooltipText?:$question->getTooltip();

        $hasSeenTutorial = $this->hasSeenTutorial($campaign);
        if($question->getType() === Question::TYPE_SLIDER_VALUE) {
            $this->markTutorialSeen($campaign);
        }

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/question.html.twig', [
            'campaign' => $campaign,
            'question' => $question,
            'theme' => $step->getTheme(),
            'participation' => $participation,
            'form' => $form->createView(),
            'help_text' => $em->getRepository(HelpText::class)->findOneBy(['locale' => $this->translator->getLocale()])->getText(),
            'tooltip' => $tooltipText,
            'total_steps' => $this->getTotalSteps($participation),
            'current_progress' => $step->getPosition() - 1 + $this->getBasicInfosSteps($campaign),
            'show_tutorial' => !$hasSeenTutorial,
            'steps_separators' => $this->getStepsSeparators($participation, $step),
        ]);
    }

    protected function stepDivider(Campaign $campaign, CampaignSegmentStep $step): Response
    {
        $form = $this->formFactory->createNamedBuilder('step_'.$step->getPosition());

        $form
            ->add('submit', SubmitType::class, [
                'label' => 'montgolfiere.questionnaire.question.continue',
            ])
        ;
        $form = $form->getForm();
        /** @var Form $form */

        $participation = $this->getParticipation($campaign);
        if($form->handleRequest($this->request)->isSubmitted()) {
            $segment = $participation->getSegment();
            $this->setSessionStep($campaign, self::getNextStep($segment, $step));

            return $this->getStepResponse($campaign);
        }

        $themeIndex = 0;
        foreach ($participation->getSegment()->getSteps() as $iStep) {
            if($iStep->getType() === CampaignSegmentStep::TYPE_DIVIDER) {
                $themeIndex++;
                if($iStep === $step) {
                    break;
                }
            }
        }

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/divider.html.twig', [
            'campaign' => $campaign,
            'participation' => $participation,
            'theme' => $step->getTheme(),
            'themeIndex' => $themeIndex,
            'form' => $form->createView(),
        ]);
    }

    private function checkParticipationAlert(CampaignParticipation $participation)
    {
        if($participation->isWBEAlertSent() || !WBEManager::hasLowWBEScore($participation)) {
            return;
        }
        $recipients = [$this->replyTo];
        if ($this->alternativeContactFromRecipient !== '') {
            $recipients[] = $this->alternativeContactFromRecipient;
        }
        $message = (new \Swift_Message())
            ->setSubject($this->translator->trans('montgolfiere.emails.bee_alert.subject'))
            ->setTo($recipients)
            ->setFrom($this->sender, $this->fromName)
            ->setSender($this->sender)
        ;
        $message->setBody($this->renderView('@AzimutMontgolfiereApp/Email/participation_alert.txt.twig', ['participation' => $participation]), 'text/plain');
        $message->addPart($this->renderView('@AzimutMontgolfiereApp/Email/participation_alert.html.twig', ['participation' => $participation]), 'text/html');

        $this->mailer->send($message);
        $participation->setWBEAlertSent(true);
        $participation->setRpsAlert(true);


        $this->getDoctrine()->getManager()->flush();
    }

    protected function stepCongratulations(Campaign $campaign): Response
    {
        $participation = $this->getParticipation($campaign);
        $participation->setFinished(true);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->checkParticipationAlert($participation);

        $this->clearParticipationFromSession($participation);
        /** @var WBEText|null $wbeText */
        $wbeText = $this->getDoctrine()->getRepository(WBEText::class)->findOneBy([
            'locale' => $this->request->getLocale(),
            'engagementProfile' => WBEManager::getEngagementProfileFromParticipation($participation),
            'wellBeingProfile' => WBEManager::getWellBeingProfileFromParticipation($participation),
        ]);

        return $this->render('@AzimutMontgolfiereApp/Questionnaire/congratulations.html.twig', [
            'campaign' => $campaign,
            'participation' => $participation,
            'contact_address' => $this->replyTo,
            'wbe_text' => $wbeText,
        ]);
    }

    public function sendEmailAction(Campaign $campaign, Request $request): Response
    {
        $emailAddress = $request->request->get('email');
        if(false === filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return new Response('Veuillez saisir une adresse email valide', 400);
        }

        $participation = $this->getParticipation($campaign, true);
        if (!$participation) {
            return new Response('Votre session a expiré et votre participation ne peut plus vous être envoyée par email', 400);
        }

        if($request->getSession()->has('questionnaire_previous_locale')) {
            $this->translator->setLocale($request->getSession()->get('questionnaire_previous_locale'));
        }

        $this->WBEManager->sendEmail($participation, $emailAddress, $this->request->getLocale());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function contactAction(Campaign $campaign, Request $request): Response
    {
        $fullName = $request->request->get('name');
        $emailAddress = $request->request->get('email');
        $phone = $request->request->get('phone');
        $message = $request->request->get('message');
        if($request->request->get('contact-type', 'contact') === 'low-score') {
            $message = 'Souhaite être contacté par un psychologue suite à un indice BEE faible.';
        }

        if(empty($fullName)) {
            return new Response($this->translator->trans('montgolfiere.questionnaire.question.contact.errors.name'), Response::HTTP_BAD_REQUEST);
        }
        if(empty($emailAddress) && empty($phone)) {
            return new Response($this->translator->trans('montgolfiere.questionnaire.question.contact.errors.email_or_phone'), Response::HTTP_BAD_REQUEST);
        }
        if($emailAddress && false===filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return new Response($this->translator->trans('montgolfiere.questionnaire.question.contact.errors.email_invalid'), Response::HTTP_BAD_REQUEST);
        }
        if(empty($message)) {
            return new Response($this->translator->trans('montgolfiere.questionnaire.question.contact.errors.message'), Response::HTTP_BAD_REQUEST);
        }

        $participation = $this->getParticipation($campaign, true);

        $step = $this->request->getSession()->get('participation_'.$campaign->getId().'_step');
        $step = $participation->getSegment()->getStep($step);

        $this->translator->setLocale('fr');

        $email = (new \Swift_Message())
            ->setSubject($this->translator->trans('montgolfiere.emails.contact.subject'))
            ->setTo($this->replyTo)
            ->setFrom($this->sender, $this->fromName)
            ->setSender($this->sender)
        ;
        $email->setBody($this->renderView('@AzimutMontgolfiereApp/Email/contact.txt.twig', [
            'participation' => $participation,
            'campaign' => $campaign,
            'fullName' => $fullName,
            'email' => $emailAddress,
            'phone' => $phone,
            'message' => $message,
            'step' => $step,
        ]), 'text/plain');

        $this->mailer->send($email);

        if ($emailAddress) {
            $confirm = (new \Swift_Message())
                ->setSubject($this->translator->trans('montgolfiere.emails.contact_confirmation.subject'))
                ->setTo($emailAddress)
                ->setFrom($this->sender, $this->fromName)
                ->setSender($this->sender)
                ->setReplyTo($this->replyTo);
            $confirm
                ->setBody($this->renderView('@AzimutMontgolfiereApp/Email/contact_confirmation.txt.twig', ['campaign' => $campaign]), 'text/plain')
                ->addPart($this->renderView('@AzimutMontgolfiereApp/Email/contact_confirmation.html.twig', ['campaign' => $campaign]), 'text/html');

            $this->mailer->send($confirm);
        }

        $participation->setContactRequested(true);
        $this->getDoctrine()->getManager()->flush();

        return new Response(null, 204);
    }

    public function refuseContactAction(Campaign $campaign): Response
    {
        $participation = $this->getParticipation($campaign, true);
        if (!$participation) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $participation->setContactRefused(true);

        if ($participation->getEmailAddress()) {
            $refusal = (new \Swift_Message())
                ->setSubject($this->translator->trans('montgolfiere.emails.contact_refusal.subject', [], 'messages'))
                ->setTo($participation->getEmailAddress())
                ->setFrom($this->sender, $this->fromName)
                ->setSender($this->sender)
                ->setReplyTo($this->replyTo);

            $refusal
                ->setBody($this->renderView('@AzimutMontgolfiereApp/Email/contact_refusal.txt.twig', ['campaign' => $campaign]), 'text/plain')
                ->addPart($this->renderView('@AzimutMontgolfiereApp/Email/contact_refusal.html.twig', ['campaign' => $campaign]), 'text/html');

            $this->mailer->send($refusal);
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function themeImageAction(Theme $theme, UploadSubscriber $uploadSubscriber): Response
    {
        if(!$theme->getFilename()) {
            throw $this->createNotFoundException();
        }

        return $this->file($uploadSubscriber->getUploadsDir().DIRECTORY_SEPARATOR.$uploadSubscriber->getTargetDir().DIRECTORY_SEPARATOR.$theme->getFilename());
    }

    protected function getParticipation(Campaign $campaign, bool $allowPrevious = false): ?CampaignParticipation
    {
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);

        $session = $this->request->getSession();
        if($allowPrevious && !$session->has('participation_'.$campaign->getId().'_id')) {
            $participationId = $session->get('participation_'.$campaign->getId().'_id_previous');
        }
        else {
            $participationId = $session->get('participation_'.$campaign->getId().'_id');
        }
        if(!$participationId) {
            return null;
        }

        return $repo->find($participationId);
    }

    protected function saveParticipationToSession(CampaignParticipation $participation): void
    {
        $campaign = $participation->getCampaign();

        $this->request->getSession()->set('participation_'.$campaign->getId().'_id', $participation->getId());
    }

    protected function clearParticipationFromSession(CampaignParticipation $participation): void
    {
        $campaign = $participation->getCampaign();
        $session = $this->request->getSession();
        // Save last participation id to session so the "Send by email" link works
        $session->set('participation_'.$campaign->getId().'_id_previous', $session->get('participation_'.$campaign->getId().'_id'));
        $session->set('questionnaire_previous_locale', $session->get('questionnaire_locale'));
        $session->remove('participation_'.$campaign->getId().'_id');
        $session->remove('participation_'.$campaign->getId().'_step');
        $session->remove('participation_'.$campaign->getId().'_has_seen_tutorial');
        $session->remove('questionnaire_locale');
    }

    protected function hasSeenTutorial(Campaign $campaign): bool
    {
        $session = $this->request->getSession();

        return $session->get('participation_'.$campaign->getId().'_has_seen_tutorial', false);
    }

    protected function markTutorialSeen(Campaign $campaign): self
    {
        $session = $this->request->getSession();

        $session->set('participation_'.$campaign->getId().'_has_seen_tutorial', true);

        return $this;
    }

    protected static function getNextStep(CampaignSegment $segment, CampaignSegmentStep $step): ?int
    {
        $nextStep = $segment->getStep($step->getPosition() + 1);
        if(!$nextStep) {
            return self::STEP_CONGRATULATIONS;
        }

        return $step->getPosition() + 1;
    }

    protected static function canGoBack(CampaignSegmentStep $currentStep, CampaignParticipation $participation): bool
    {
        $previous = $participation->getSegment()->getStep($currentStep->getPosition() - 1);
        if($previous && $previous->getType() === CampaignSegmentStep::TYPE_DIVIDER) {
            return false;
        }

        return $currentStep->getType() === CampaignSegmentStep::TYPE_ITEM || $currentStep->getType() === CampaignSegmentStep::TYPE_QUESTION;
    }

    private function getTotalSteps(CampaignParticipation $participation): int
    {
        $campaign = $participation->getCampaign();

        $basicInfosQuestions = $this->getBasicInfosSteps($campaign);

        // While divider steps should really count towards progress (because no question is asked),
        // the current_progress variable is defined by the current step which also increments on a divider step
        if($participation->getSegment()) {
            $questions = $participation->getSegment()->getSteps()->count();
        }
        else {
            // If not, we take the highest possible number of steps
            $questions = max(...$campaign->getValidSegments()->map(function(CampaignSegment $segment): int {return $segment->getSteps()->count();}));
        }

        return $basicInfosQuestions + $questions;
    }

    private function getBasicInfosSteps(Campaign $campaign): int
    {
        $initialQuestions = 0;
        $hasPersonalInfoStep = false;
        foreach ($campaign->getFieldsStatus() as $fieldName => $fieldStatus) {
            if($fieldStatus === Campaign::FIELD_STATUS_DISABLED) {
                continue;
            }
            if(in_array($fieldName, ['firstName', 'lastName', 'phoneNumber',])) {
                if($hasPersonalInfoStep) {
                    continue;
                }
                $hasPersonalInfoStep = true;
            }
            $initialQuestions++;
        }
        $sortingFactors = count($campaign->getSortingFactors());
        $segmentQuestion = count($campaign->getSegments()) > 1 ? 1 : 0;

        return $initialQuestions + $sortingFactors + $segmentQuestion;
    }

    private function getStepsSeparators(CampaignParticipation $participation, ?CampaignSegmentStep $currentStep): array
    {
        $totalSteps = $this->getTotalSteps($participation);
        $basicInfosSteps = $this->getBasicInfosSteps($participation->getCampaign());
        $separators = [
            [
                'count' => $basicInfosSteps,
                'complete' => !is_null($currentStep),
                'inner_size' => is_null($currentStep) ? 0 : 100,
                'done_steps' => is_null($currentStep) ? 0 : $basicInfosSteps,
                'active' => is_null($currentStep),
            ]
        ];
        $dividersCount = 0;

        $segment = $participation->getSegment();
        if(!$segment) {
            foreach ($participation->getCampaign()->getValidSegments() as $testSegment) {
                if(!$segment || $segment->getSteps()->count() < $testSegment->getSteps()->count()) {
                    $segment = $testSegment;
                }
            }
        }

        $hasFoundStep = is_null($currentStep);
        foreach ($segment->getSteps() as $step) {
            if($step === $currentStep) {
                $hasFoundStep = true;
            }
            if($step->getType() === CampaignSegmentStep::TYPE_DIVIDER) {
                $dividersCount++;
                $separators[] = [
                    'count' => 0,
                    'complete' => false,
                    'inner_size' => 0,
                    'done_steps' => 0,
                    'active' => false,
                ];
                continue;
            }
            $separators[count($separators)-1]['count']++;
            if(!$hasFoundStep) {
                $separators[count($separators)-1]['done_steps']++;
            }
        }

        $totalSteps-= $dividersCount;

        foreach ($separators as &$separator) {
            $separator['size'] = round($separator['count'] * 100 / $totalSteps, 2);
            $separator['inner_size'] = $separator['count'] > 0 ? $separator['done_steps'] / $separator['count'] * 100 : 0;
            $separator['complete'] = $separator['inner_size'] === 100;
        }

        return $separators;
    }

}
