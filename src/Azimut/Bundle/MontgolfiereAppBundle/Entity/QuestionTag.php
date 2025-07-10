<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * QuestionTag
 *
 * @ORM\Table(name="montgolfiere_question_tag")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\QuestionTagRepository")
 */
class QuestionTag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "backoffice_segments_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Serializer\Groups({"Default", "backoffice_segments_list"})
     */
    private $name;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return QuestionTag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"Default", "backoffice_segments_list"})
     */
    public function getColor()
    {
        $code = dechex(crc32($this->name.strrev($this->name)));
        $code = substr($code, 0, 6);

        return $code;
    }
}

