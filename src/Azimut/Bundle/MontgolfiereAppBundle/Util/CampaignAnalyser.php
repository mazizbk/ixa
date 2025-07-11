<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItem;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItemTableText;
use Azimut\Bundle\MontgolfiereAppBundle\Model\CampaignAnalysisResult;
use Azimut\Bundle\MontgolfiereAppBundle\Model\ItemAnalysis;
use Azimut\Bundle\MontgolfiereAppBundle\Model\ThemeAnalysis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignAnalyser
{

    /**
     * @var ThemesManager
     */
    protected $themesManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var SortingFactorManager
     */
    private $sortingFactorManager;

    public function __construct(ThemesManager $themesManager, TranslatorInterface $translator, EntityManagerInterface $entityManager, SortingFactorManager $sortingFactorManager)
    {
        $this->themesManager = $themesManager;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->sortingFactorManager = $sortingFactorManager;
    }

    /**
     * @param CampaignParticipation[] $participations
     */
    public function getAnalysisData(Campaign $campaign, array $participations, ?FormInterface $form = null, string $fileName = ''): CampaignAnalysisResult
    {
        $result = new CampaignAnalysisResult();
        $riRepo = $this->entityManager->getRepository(RestitutionItem::class);
        $itemTableTextRepo = $this->entityManager->getRepository(RestitutionItemTableText::class);

        $date = $campaign->getEndDate()??$campaign->getStartDate();
        $title = $this->getHouseTitle($campaign, $form);
        $expectedParticipations = $this->getExpectedParticipations($campaign, $form);

        $result
            ->setTitle($title)
            ->setDate($date?\DateTimeImmutable::createFromMutable($date):null)
            ->setExpectedParticipants($expectedParticipations)
            ->setFileName($fileName)
            ->setParticipants(count($participations))
            ->setAnalysisVersion($campaign->getAnalysisVersion())
        ;

        $themes = $this->themesManager->getThemes($campaign->getAnalysisVersion());
        $themesAnalysis = (new \SplObjectStorage());
        $itemsAnalysis = (new \SplObjectStorage());
        foreach ($themes as $theme) {
            if($theme->isSkipInAnalysis()) {
                continue;
            }
            $themesAnalysis[$theme] = new ThemeAnalysis($theme);
            $themesAnalysis[$theme]
                ->setName($theme->getName()[$this->translator->getLocale()])
                ->setHouseSettings($theme->getHouseSettings())
                ->setWordSettings($theme->getWordSettings())
                ->setAnalysisVersion($campaign->getAnalysisVersion())
            ;

            foreach ($theme->getItems() as $item) {
                $itemsAnalysis[$item] = new ItemAnalysis();
                $itemsAnalysis[$item]
                    ->setName($item->getName()[$this->translator->getLocale()])
                    ->setTheme($theme)
                    ->setItem($item)
                    ->setHouseSettings($item->getHouseSettings())
                    ->setAnalysisVersion($campaign->getAnalysisVersion())
                ;
            }

        }

        foreach ($participations as $participation) {
            foreach ($participation->getAnswers() as $answer) {
                if($answer->getStep()->getType() !== CampaignSegmentStep::TYPE_ITEM) {
                    continue;
                }
                $theme = $answer->getStep()->getTheme();
                if($theme->isSkipInAnalysis()) {
                    continue;
                }
                $item = $answer->getStep()->getItem();
                $themesAnalysis[$theme]->addAnswer($answer);
                $itemsAnalysis[$item]->addAnswer($answer);
            }
        }

        // Reassociate items with their themes
        foreach ($itemsAnalysis as $item) {
            $analysis = $itemsAnalysis[$item];

            /** @var RestitutionItemTableText|null $texts */
            $texts = $itemTableTextRepo->find(['item' => $analysis->getItem(), 'trend' => $analysis->getTrend(),]);
            if($texts) {
                $analysis
                    ->setShortSignification($texts->getShortSignification())
                    ->setLongSignification($texts->getLongSignification())
                ;
            }

            $themesAnalysis[$analysis->getTheme()]->addItem($analysis);
        }

        foreach ($themesAnalysis as $theme) {
            if($theme->isVirtual()) {
                foreach ($theme->getVirtualSettings()->getParentThemesIds() as $parentThemeId) {
                    $parentTheme = $this->themesManager->getTheme($parentThemeId, $campaign->getAnalysisVersion());
                    $parentAnalysis = $themesAnalysis[$parentTheme];

                    $themesAnalysis[$theme]->addItem($parentAnalysis->asItemAnalysis());
                }
            }

            $combination = array_map(function(ItemAnalysis $itemAnalysis): string {return self::turnAverageIntoColorCode($itemAnalysis->getWorkcareAverage());}, $themesAnalysis[$theme]->getItems());
            $restitutionItem = $riRepo->findOneBy(['theme' => $theme, 'combination' => implode('-', $combination),]);
            $themesAnalysis[$theme]
                ->setRestitution($restitutionItem)
                ->lock()
            ;
        }

        $result->setThemesAnalysis(self::getStorageValues($themesAnalysis));

        return $result;
    }

    public function getFileName(Campaign $campaign, FormInterface $form, $documentType): string
    {
        $locale = $this->translator->getLocale();
        $sortingFactorTitle = 'Global';
        foreach ($campaign->getSortingFactors() as $sortingFactor) {
            $fieldName = 'sorting_factor_'.$sortingFactor->getId();
            if(!$form->get('fastSearch')->has($fieldName) || !($value = $form->get('fastSearch')->get($fieldName)->getData())) {
                continue;
            }
            $sortingFactorTitle = $this->sortingFactorManager->getSortingFactorValueName($locale, $value);
        }
        $fileName = $sortingFactorTitle .' - '. $campaign->getClient()->getCorporateName() .' - '. $campaign->getStartDate()->format('m Y') .' - '. $documentType;

        return $fileName;
    }

    protected function getHouseTitle(Campaign $campaign, ?FormInterface $form = null): string
    {
        $locale = $this->translator->getLocale();
        $tr = function ($id) {
            return $this->translator->trans($id);
        };

        $title = $campaign->getName();
        if(!$form) {
            return $title;
        }
        $groups = $form->get('groups')->getData()??[];
        foreach ($groups as $group) {
            if(!$group->getName()) {
                continue;
            }
            $title.= ' - '.$group->getName();
        }

        if ($segment = $form->get('fastSearch')->get('segment')->getData()) {
            $title .= ' - '.$segment->getName();
        }

        foreach ($campaign->getSortingFactors() as $sortingFactor) {
            $fieldName = 'sorting_factor_'.$sortingFactor->getId();
            if(!$form->get('fastSearch')->has($fieldName) || !($value = $form->get('fastSearch')->get($fieldName)->getData())) {
                continue;
            }
            $title.= ' - '.$this->sortingFactorManager->getSortingFactorName($locale, $sortingFactor).' : '.$this->sortingFactorManager->getSortingFactorValueName($locale, $value);
        }

        if ($form->get('fastSearch')->has('managerName') && $managerName = $form->get('fastSearch')->get('managerName')->getData()) {
            $title .= ' - Manager : '.$managerName;
        }
        if ($form->get('fastSearch')->has('gender') && $gender = $form->get('fastSearch')->get('gender')->getData()) {
            $title .= ' - Sexe : '.$tr('montgolfiere.questionnaire.basic_information.genders.'.$gender);
        }
        if ($form->get('fastSearch')->has('seniority') && $seniority = $form->get('fastSearch')->get('seniority')->getData()) {
            $title .= ' - Ancienneté : '.$tr('montgolfiere.questionnaire.basic_information.seniorities.'.$seniority);
        }
        if ($form->get('fastSearch')->has('education') && $education = $form->get('fastSearch')->get('education')->getData()) {
            $title .= ' - Niveau d\'étude : '.$tr('montgolfiere.questionnaire.basic_information.educations.'.$education);
        }
        if ($form->get('fastSearch')->has('csp') && $csp = $form->get('fastSearch')->get('csp')->getData()) {
            $title .= ' - CSP : '.$tr('montgolfiere.questionnaire.basic_information.csps.'.$csp);
        }
        if ($form->get('fastSearch')->has('age') && $age = $form->get('fastSearch')->get('age')->getData()) {
            $title .= ' - Tranche d\'âge : '.$tr('montgolfiere.questionnaire.basic_information.ages.'.$age);
        }
        if ($form->get('fastSearch')->has('maritalStatus') && $maritalStatus = $form->get('fastSearch')->get('maritalStatus')->getData()) {
            $title .= ' - Situation familiale : '.$tr('montgolfiere.questionnaire.basic_information.marital_statuses.'.$maritalStatus);
        }
        if ($form->get('fastSearch')->has('managementResponsibilities') && $managementResponsibilities = $form->get('fastSearch')->get('managementResponsibilities')->getData()) {
            $title .= ' - Resp. de management : '.$tr('montgolfiere.questionnaire.basic_information.management_responsibilities_values.'.$managementResponsibilities);
        }
        if ($form->get('fastSearch')->has('residenceState') && $residenceState = $form->get('fastSearch')->get('residenceState')->getData()) {
            $title .= ' - Département de résidence : '.$tr('montgolfiere.questionnaire.basic_information.states.'.$residenceState);
        }

        return $title;
    }

    protected function getExpectedParticipations(Campaign $campaign, ?FormInterface $form): ?int
    {
        if(!$form) {
            return $campaign->getExpectedAnswers();
        }

        if(count($form->get('groups')->getData() ?? []) > 0) {
            return null;
        }

        // If any of these fields has value, we cannot guess the expected participations
        $extraFields = ['segment', 'managerName', 'gender', 'seniority', 'education', 'csp', 'age', 'maritalStatus', 'managementResponsibilities', 'residenceState'];
        foreach ($extraFields as $extraField) {
            if($form->get('fastSearch')->has($extraField) && $form->get('fastSearch')->get($extraField)->getData() !== null) {
                return null;
            }
        }

        $expectedParticipations = null;
        $hasSelectedSortingFactor = false;
        foreach ($campaign->getSortingFactors() as $sortingFactor) {
            $fieldName = 'sorting_factor_'.$sortingFactor->getId();
            /** @var CampaignSortingFactorValue $value */
            if(!$form->get('fastSearch')->has($fieldName) || !($value = $form->get('fastSearch')->get($fieldName)->getData())) {
                continue;
            }

            if($hasSelectedSortingFactor) {
                return null;
            }
            $hasSelectedSortingFactor = true;
            $expectedParticipations = $value->getWorkforce();
        }

        if(!$hasSelectedSortingFactor) {
            return $campaign->getExpectedAnswers();
        }
        return $expectedParticipations;
    }

    private static function getStorageValues(\SplObjectStorage $storage): array
    {
        $result = [];
        foreach ($storage as $key) {
            $result[] = $storage[$key];
        }

        return $result;
    }

    private static function turnAverageIntoColorCode(float $average): string
    {
        switch(true) {
            case $average <= 37.5:
                return RestitutionItem::COLOR_BLUE;
            case $average > 37.5 && $average <= 62.5:
                return RestitutionItem::COLOR_GREEN;
            case $average > 62.5:
                return RestitutionItem::COLOR_YELLOW;
        }
        throw new \InvalidArgumentException();
    }

    public static function getTrendsCuts(): array
    {
        // The two outermost segments have been removed. The remaining cuts are
        // scaled so that the final total still reaches 100.
        $i = 0;
        return [
            $i += 10.1,
            $i += 7.3,
            $i += 7.2,
            $i += 7.3,
            $i += 7.2,
            $i += 7.3,

            $i += 7.2, // Green blue
            $i += 7.3, // Solid green
            $i += 7.2, // Solid green
            $i += 7.3, // Solid green
            $i += 7.2, // Green yellow

            $i += 7.3,
            $i += 10.1,
        ];
    }

    public static function getTrend($value, $base = 100): int
    {
        if ($base != 100){
            $value = $value * 100 / $base;
        }

        foreach (self::getTrendsCuts() as $trend => $cut) {
            if($value <= $cut) {
                return $trend;
            }
        }

        return count(self::getTrendsCuts());
    }

    public static function getTrendColor(int $value, int $base, Campaign $campaign): string
    {
        $colors = $campaign->getAnalysisVersion()->getColors();
        $trend = self::getTrend($value, $base);

        $offset = (int) ((count($colors) - count(self::getTrendsCuts())) / 2);
        $index = $trend + $offset;

        if ($index >= count($colors)) {
            $index = count($colors) - 1;
        }

        return $colors[$index];
    }

}