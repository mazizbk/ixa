<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-04-11 11:01:57
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;

trait CmsFileRelatedArticlesTrait
{
    /**
     * Unidirectional One-To-Many
     * @var ArrayCollection<CmsFileArticle>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileArticle")
     * @ORM\JoinTable(name="cms_cmsfile_related_article")
     * @ORM\OrderBy({"id" = "DESC"})
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $relatedArticles;

    public function __construct()
    {
        $this->relatedArticles = new ArrayCollection();
    }

    /**
     * Get related articles
     * @return ArrayCollection<CmsFileArticle>
     */
    public function getRelatedArticles()
    {
        return $this->relatedArticles;
    }

    /**
     * Set related articles
     * @param ArrayCollection<CmsFileArticle> $cmsFileArticles
     *
     * @return self;
     */
    public function setRelatedArticles(ArrayCollection $cmsFileArticles)
    {
        $this->relatedArticles = $cmsFileArticles;

        return $this;
    }

    /**
     * Add a related article
     * @param CmsFileArticle $cmsFileArticle
     *
     * @return self;
     */
    public function addRelatedArticle(CmsFileArticle $cmsFileArticle)
    {
        if (!$this->relatedArticles->contains($cmsFileArticle)) {
            $this->relatedArticles->add($cmsFileArticle);
        }

        return $this;
    }

    /**
     * Has related articles
     *
     * @return bool;
     */
    public function hasRelatedArticles()
    {
        return count($this->relatedArticles) > 0;
    }

    /**
     * Remove a related article
     * @param CmsFileArticle $cmsFileArticle
     *
     * @return self;
     */
    public function removeRelatedArticle(CmsFileArticle $cmsFileArticle)
    {
        if ($this->relatedArticles->contains($cmsFileArticle)) {
            $this->relatedArticles->removeElement($cmsFileArticle);
        }

        return $this;
    }
}
