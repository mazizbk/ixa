<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\WBEText;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;

class WBEManager
{
    public const WEIGHTED_SCORE_WB = 1;
    public const WEIGHTED_SCORE_E = 2;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EngineInterface
     */
    private $engine;
    private $fromAddress;
    private $fromName;
    private $sender;
    private $replyTo;

    public function __construct(EntityManagerInterface $entityManager, \Swift_Mailer $mailer, TranslatorInterface $translator, EngineInterface $engine, $fromAddress, $fromName, $sender, $replyTo)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->sender = $sender;
        $this->replyTo = $replyTo;
        $this->engine = $engine;
    }

    public static function getEngagementProfilesCount()
    {
        return 5;
    }

    public static function getWellBeingProfilesCount()
    {
        return 8;
    }

    public static function getEngagementProfileFromParticipation(CampaignParticipation $participation): int
    {
        foreach ($participation->getAnswers() as $answer) {
            if($answer->getStep()->getItem() !== null && $answer->getStep()->getItem()->getDefinesEngagementProfile()) {
                $engagementAnswer = $answer;
                break;
            }
        }

        $score = self::getWeightedScore($participation, self::WEIGHTED_SCORE_E);
        if(!isset($engagementAnswer) || $engagementAnswer->getSkipped()) {
            throw new \InvalidArgumentException('Can\'t determine an engagement profile because the required question was not answered');
        }

        if($engagementAnswer->getValue() < 0) {
            // Lacking side
            if($score > 7.5) {
                return 3;
            }
            if($score > 3.5) {
                return 2;
            }
            return 1;
        }
        else {
            // Excess side
            if($score > 7.5) {
                return 3;
            }
            if($score > 3.5) {
                return 4;
            }
            return 5;
        }
    }

    public static function getWellBeingProfileFromParticipation(CampaignParticipation $participation): int
    {
        $score = self::getWeightedScore($participation, self::WEIGHTED_SCORE_WB);
        $profiles = [1.5, 3, 4.5, 6, 7, 8, 9, 10];
        foreach ($profiles as $i => $profile) {
            if($profile >= $score) {
                return $i+1;
            }
        }

        throw new \InvalidArgumentException('Well Being score is greater than the maximal allowed value');
    }

    public static function hasLowWBEScore(CampaignParticipation $participation): bool
    {
        $wellBeingProfile = self::getWellBeingProfileFromParticipation($participation);

        switch(self::getEngagementProfileFromParticipation($participation)) {
            case 1:
            case 5:
                return $wellBeingProfile <= 4;
            case 2:
            case 4:
                return $wellBeingProfile <= 2;
            case 3:
                return $wellBeingProfile <= 1;
        }
        throw new \LogicException();
    }

    public static function getWeightedScore(CampaignParticipation $participation, int $scoreType): float
    {
        if($scoreType !== self::WEIGHTED_SCORE_WB && $scoreType !== self::WEIGHTED_SCORE_E) {
            throw new \InvalidArgumentException();
        }
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($participation->getSegment()->getSteps() as $step) {
            $item = $step->getItem();
            $question = $step->getQuestion();
            if(!$item && !$question) {
                continue;
            }

            $answer = $participation->getAnswer($step);
            if(!$answer || $answer->getSkipped()) {
                continue;
            }

            $value = 10 - abs($answer->getValue());
            if($item) {
                $weight = $scoreType === self::WEIGHTED_SCORE_WB ? $item->getWellBeingWeight() : $item->getEngagementWeight();
            }
            else {
                $weight = $scoreType === self::WEIGHTED_SCORE_WB ? $question->getWellBeingCoefficient() : $question->getEngagementCoefficient();
                if($question->getType() === Question::TYPE_SATISFACTION_GAUGE) {
                    $factor = $question->getGaugeMaxValue() / 10;
                    $value = $answer->getValue() / $factor;
                    $value = $question->isGaugeInvert() ? $value : 10 - $value;
                }
            }

            $totalScore+= $value *$weight;
            $totalWeight+= $weight;
        }

        if($totalWeight === 0) {
            return 0;
        }

        return $totalScore/$totalWeight;
    }

    public function sendEmail(CampaignParticipation $participation, string $email, string $locale = 'fr'): void
    {
        /** @var WBEText|null $wbeText */
        $wbeText = $this->entityManager->getRepository(WBEText::class)->findOneBy([
            'locale' => $locale,
            'engagementProfile' => self::getEngagementProfileFromParticipation($participation),
            'wellBeingProfile' => self::getWellBeingProfileFromParticipation($participation),
        ]);

        $message = (new \Swift_Message())
            ->setSubject($this->translator->trans('montgolfiere.emails.participation.subject'))
            ->setTo($email)
            ->setFrom($this->sender, $this->fromName)
            ->setSender($this->sender)
            ->setReplyTo($this->replyTo)
        ;
        $viewParameters = ['participation' => $participation, 'wbe_text' => $wbeText, 'contact_email' => $this->replyTo,];
        $message
            ->setBody($this->engine->render('@AzimutMontgolfiereApp/Email/participation.txt.twig', $viewParameters), 'text/plain')
            ->addPart($this->engine->render('@AzimutMontgolfiereApp/Email/participation.html.twig', $viewParameters), 'text/html')
        ;

        $this->mailer->send($message);
    }
}
