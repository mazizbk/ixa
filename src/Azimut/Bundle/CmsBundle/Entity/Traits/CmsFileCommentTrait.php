<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 09:43:38
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\Comment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait CmsFileCommentTrait
{
    /**
     * @var Comment
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\Comment", mappedBy="cmsFile", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get ratings
     *
     * @return array
     */
    public function getRatings() {
        return array_filter(array_map(function ($comment) {
            return $comment->getRating();
        }, $this->comments->toArray()));
    }

    /**
     * Get ratings count
     *
     * @return int
     */
    public function getRatingsCount()
    {
        return count($this->getRatings());
    }

    /**
     * Get average rating
     *
     * @return int|null
     */
    public function getAverageRating()
    {
        $ratings = $this->getRatings();
        if (0 == count($ratings)) {
            return null;
        }
        return round(array_sum($ratings) / count($ratings), 1);
    }
}
