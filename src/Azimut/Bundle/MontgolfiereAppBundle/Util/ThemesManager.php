<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ThemesManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array
     */
    protected $questionnaireLocales;

    /**
     * @var array<array-key, Theme[]>
     */
    private $cache = [];

    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $analysisVersionRepository;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, array $questionnaireLocales)
    {
        $this->repository = $entityManager->getRepository(Theme::class);
        $this->analysisVersionRepository = $entityManager->getRepository(AnalysisVersion::class);
        $this->requestStack = $requestStack;
        $this->questionnaireLocales = $questionnaireLocales;
    }

    /**
     * @return Theme[]
     */
    public function getThemes(?AnalysisVersion $analysisVersion = null): array
    {
        $analysisVersion = $analysisVersion ?? $this->getLastAnalysisVersion();
        $this->warmCache($analysisVersion);

        return array_values($this->cache[$analysisVersion->getId()]);
    }

    public function getTheme(int $id, ?AnalysisVersion $analysisVersion = null): Theme
    {
        $analysisVersion = $analysisVersion ?? $this->getLastAnalysisVersion();
        $this->warmCache($analysisVersion);

        if(!isset($this->cache[$analysisVersion->getId()][$id])) {
            throw new \InvalidArgumentException('Theme '.$id.' does not exist');
        }

        return $this->cache[$analysisVersion->getId()][$id];
    }

    /**
     * @return Item[]
     */
    public function getAllItems(?AnalysisVersion $analysisVersion = null): array
    {
        $items = [];
        $themes = $this->getThemes($analysisVersion);
        foreach ($themes as $theme) {
            if($theme->getItems()->isEmpty()) {
                continue;
            }
            array_push($items, ...$theme->getItems()->toArray());
        }

        return $items;
    }

    protected function warmCache(AnalysisVersion $analysisVersion)
    {
        if (!empty($this->cache) && !empty($this->cache[$analysisVersion->getId()])) {
            return;
        }

        $this->cache[$analysisVersion->getId()] = [];

        /** @var Theme[] $themes */
        $themes = $this->repository->findBy(['analysisVersion' => $analysisVersion,], ['position' => 'ASC',]);
        foreach ($themes as $theme) {
            $this->cache[$analysisVersion->getId()][$theme->getId()] = $theme;
        }
    }

    public function getLastAnalysisVersion(): AnalysisVersion
    {
        return $this->analysisVersionRepository->findBy([], ['id' => 'DESC'], 1)[0];
    }

}
