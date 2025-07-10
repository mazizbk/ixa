<?php
/**
 * Created by mikaelp on 2018-11-07 12:21 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="montgolfiere_campaign_segment_additional_question")
 * @deprecated To be removed once all existing segments has been converted to the new Steps system
 */
class CampaignSegmentAdditionalQuestion
{
    /**
     * @var CampaignSegment
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment", inversedBy="additionalQuestions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Gedmo\SortableGroup()
     */
    private $segment;

    /**
     * @var Question
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition()
     */
    private $position;

    /**
     * @return CampaignSegment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * @param CampaignSegment $segment
     * @return $this
     */
    public function setSegment(CampaignSegment $segment)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Question $question
     * @return $this
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
