<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItemTableText;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\RestitutionItemTableTextType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\RestitutionItemType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItem;
use Symfony\Component\HttpFoundation\Response;

class BackofficeCampaignsRestitutionController extends AbstractController
{

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ThemesManager
     */
    protected $themesManager;

    public function __construct(FormFactoryInterface $formFactory, ThemesManager $themesManager)
    {
        $this->formFactory = $formFactory;
        $this->themesManager = $themesManager;
    }

    public function indexAction(): Response
    {
        return $this->render('@AzimutMontgolfiereApp/Backoffice/Restitution/base.html.twig',[
            'themes' => $this->getThemes(),
        ]);
    }

    public function themeAction(Theme $theme, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        //build update theme form
        $theme = $this->generateThemeWithItsItems($theme, $em);
        $updateForm = $this->formFactory->createNamedBuilder('updateForm', FormType::class,  $theme['categories']);
        /**
         * @var string $label
         * @var RestitutionItem[] $category
         */
        foreach ($theme['categories'] as $label => $category) {
            foreach ($category as $i => $item) {
                $em->persist($item);
                $updateForm->add($item->getCategory().'_'.$i, RestitutionItemType::class, [
                    'property_path' => '['.$label.']['.$i.']',
                    'label' => false,
                ]);
            }
        }

        $updateForm = $updateForm->getForm();
        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid()) {
            foreach ($theme['categories'] as $category) {
                foreach ($category as $item) {
                    if($item->getTrendText() == null && $item->getActionPlanText() == null){
                        $em->remove($item);
                    }
                }
            }
            $em->flush();
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Restitution/theme.html.twig',[
            'themes' => $this->getThemes(),
            'updateForm' => $updateForm->createView(),
            'theme' => $theme,
        ]);
    }

    public function itemAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $trends = range(0, count(CampaignAnalyser::getTrendsCuts()) - 1);
        $items = $this->themesManager->getAllItems();

        $itemsRestitutions = $this->generateItemRestitution($trends, $items, $em);
        $updateForm = $this->formFactory->createNamedBuilder('updateForm', FormType::class, $itemsRestitutions);
        foreach ($itemsRestitutions as $itemId => $restitutions) {
            $loopFirst = true;
            foreach ($restitutions as $trend => $restitution) {
                $updateForm->add($itemId.'_trend_'.$trend, RestitutionItemTableTextType::class, [
                    'property_path' => '['.$itemId.']['.$trend.']',
                    'label' => false,
                    'attr' => [
                        'class' => 'well',
                    ],
                    'long_signification_hidden' => !$loopFirst,
                ]);
                $loopFirst = false;
            }
        }
        $updateForm = $updateForm->getForm();
        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les textes ont été enregistrés');
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Restitution/item.html.twig',[
            'themes' => $this->getThemes(),
            'updateForm' => $updateForm->createView(),
            'trends' => $trends,
            'items' => $items,
            'colors' => $this->themesManager->getLastAnalysisVersion()->getColors(),
        ]);
    }

    /**
     * @param Theme $theme
     * @param EntityManagerInterface $em
     * @return array
     */
    private function generateThemeWithItsItems(Theme $theme, EntityManagerInterface $em): array
    {

        $categories = [
            RestitutionItem::CATEGORY_BAD => [],
            RestitutionItem::CATEGORY_DISPARATE => [],
            RestitutionItem::CATEGORY_UNBALANCED => [],
            RestitutionItem::CATEGORY_COMPENSATED => [],
            RestitutionItem::CATEGORY_CONSISTENT => [],
        ];
        $itemsFromDB = $em->getRepository(RestitutionItem::class)->findBy(['theme' => $theme]);

        foreach ($categories as $category => $value) {
            $categories[$category] = $this->generateItems($itemsFromDB, $theme, $category);
        }

        return [
            'title' => $theme->getName()['fr'],
            'categories' => $categories,
        ];
    }

    /**
     * @param array $trends
     * @param array $items
     * @param EntityManagerInterface $em
     * @return RestitutionItemTableText[][]
     */
    private function generateItemRestitution(array $trends, array $items, EntityManagerInterface $em): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[$item->getId()] = [];
            foreach ($item->getRestitution() as $restitution) {
                $result[$item->getId()][$restitution->getTrend()] = $restitution;
            }

            foreach ($trends as $trend) {
                if(array_key_exists($trend, $result[$item->getId()])) {
                    continue;
                }
                $result[$item->getId()][$trend] = new RestitutionItemTableText();
                $result[$item->getId()][$trend]
                    ->setItem($item)
                    ->setTrend($trend)
                ;
                $em->persist($result[$item->getId()][$trend]);
            }
        }

        return $result;
    }

    /**
     * @param RestitutionItem[] $existingItems
     * @param Theme $theme
     * @param $categoryIndex
     * @return array
     */
    private function generateItems(array $existingItems, Theme $theme, $categoryIndex): array
    {
        $newItems = [];

        $sets = [
            RestitutionItem::CATEGORY_BAD => [
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW,],
            ],
            RestitutionItem::CATEGORY_DISPARATE => [
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_YELLOW,],
            ],
            RestitutionItem::CATEGORY_UNBALANCED => [
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW,],

            ],
            RestitutionItem::CATEGORY_COMPENSATED => [
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_GREEN,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_BLUE,],
            ],
            RestitutionItem::CATEGORY_CONSISTENT => [
                [RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW, RestitutionItem::COLOR_YELLOW,],
                [RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE, RestitutionItem::COLOR_BLUE,],
                [RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN, RestitutionItem::COLOR_GREEN,],
            ],
        ];

        foreach ($sets[$categoryIndex] as $set) {
            $item = new RestitutionItem();
            $item
                ->setTheme($theme)
                ->setCategory($categoryIndex)
                ->setCombination(implode('-', $set))
            ;
            $newItems[] = self::searchColorCombination($item, $existingItems);
        }

        return $newItems;
    }

    /**
     * @param RestitutionItem   $searchedItem
     * @param RestitutionItem[] $items
     * @return RestitutionItem
     */
    private static function searchColorCombination(RestitutionItem $searchedItem, array $items): RestitutionItem
    {
        foreach ($items as $item) {
            if($item->getCombination() === $searchedItem->getCombination()) {
                return $item;
            }
        }

        return $searchedItem;
    }

    /**
     * @return Theme[]
     */
    private function getThemes(): array
    {
        $themes = $this->themesManager->getThemes();

        return array_filter($themes, function(Theme $theme) {
            return !$theme->isSkipInAnalysis();
        });
    }

}
