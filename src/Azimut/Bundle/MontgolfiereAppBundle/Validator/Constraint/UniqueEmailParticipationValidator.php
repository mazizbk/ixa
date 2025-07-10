<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Validator\Constraint;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailParticipationValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmailParticipation) {
            throw new UnexpectedTypeException($constraint, UniqueEmailParticipation::class);
        }

        if(!$value instanceof CampaignParticipation) {
            throw new UnexpectedTypeException($value, CampaignParticipation::class);
        }

        if(!$value->getEmailAddress()) {
            return;
        }

        $segment = $value->getSegment();
        if(!$segment) {
            return;
        }
        $campaign = $segment->getCampaign();

        if($campaign->getFieldStatus('emailAddress') !== Campaign::FIELD_STATUS_REQUIRED) {
            return;
        }

        $existingParticipations = $this->entityManager->getRepository(CampaignParticipation::class)->createQueryBuilder('cp')
            ->select('COUNT(cp)')
            ->leftJoin('cp.segment', 's')
            ->where('cp.finished = true')
            ->andWhere('s.campaign = :campaign')
            ->andWhere('cp.emailAddress = :emailAddress')
            ->andWhere('cp.id != :id')
            ->setParameter(':campaign', $campaign)
            ->setParameter(':emailAddress', $value->getEmailAddress())
            ->setParameter(':id', $value->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($existingParticipations > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('emailAddress')
                ->addViolation()
            ;
        }
    }

}
