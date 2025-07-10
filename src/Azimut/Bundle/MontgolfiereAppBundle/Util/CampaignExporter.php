<?php
/**
 * Created by mikaelp on 2018-11-09 9:19 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationAnswer;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignExporter
{
    /**
     * @var ExcelExporter
     */
    protected $exporter;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var ThemesManager
     */
    protected $themesManager;
    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    /**
     * @var CampaignAnalyser
     */
    private $campaignAnalyser;

    public function __construct(ExcelExporter $exporter, TranslatorInterface $translator, EntityManagerInterface $entityManager, ThemesManager $themesManager, SortingFactorManager $sortingFactorManager, CampaignAnalyser $campaignAnalyser)
    {
        $this->exporter = $exporter;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->themesManager = $themesManager;
        $this->sortingFactorManager = $sortingFactorManager;
        $this->campaignAnalyser = $campaignAnalyser;
    }

    public function exportCampaign(Campaign $campaign): StreamedResponse
    {
        $repository = $this->entityManager->getRepository(CampaignParticipationAnswer::class);
        $qb = $repository
            ->createQueryBuilder('a')
            ->leftJoin('a.participation', 'p')
            ->leftJoin('p.segment', 's')
            ->leftJoin('p.sortingFactorsValues', 'sfv')
            ->addSelect('p')
            ->addSelect('s')
            ->addSelect('sfv')
            ->where('p.finished = true')
            ->andWhere('s.campaign = :campaign')
            ->andWhere('a.value IS NOT NULL')
            ->setParameter(':campaign', $campaign)
        ;
        $locale = $this->translator->getLocale();
        $trans = function($id){
            return $this->translator->trans($id);
        };

        $results = $qb->getQuery()->getResult();
        $results = array_map(function(CampaignParticipationAnswer $answer) use($trans, $campaign, $locale) {
            $result = [
                'theme' => $answer->getStep()->getTheme()->getName()[$this->translator->getLocale()],
                'position' => $answer->getStep()->getItem()->getName()[$this->translator->getLocale()],
                'value' => $answer->getValue(),
            ];
            foreach ($campaign->getSortingFactors() as $sortingFactor) {
                $value = $answer->getParticipation()->getSortingFactorValue($sortingFactor);
                $key = $this->sortingFactorManager->getSortingFactorName($locale, $sortingFactor);
                if(!$value) {
                    $result[$key] = '';
                    continue;
                }
                $result[$key] = $this->sortingFactorManager->getSortingFactorValueName($locale, $value);
            }

            return $result;
        }, $results);
        $headers = [
            $trans('montgolfiere.backoffice.questions.fields.theme'),
            $trans('montgolfiere.backoffice.questions.fields.position'),
            'value',
        ];
        foreach ($campaign->getSortingFactors() as $sortingFactor) {
            $headers[] = $this->sortingFactorManager->getSortingFactorName($locale, $sortingFactor);
        }
        array_unshift($results, $headers);

        return $this->exporter->exportArray($results, 'raw.csv', 'raw.csv', ExcelExporter::FORMAT_CSV);
    }

    public function exportCartography(Campaign $campaign, array $participations, FormInterface $form, string $locale, bool $asPercent, string $format): StreamedResponse
    {
        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations, $form, $this->campaignAnalyser->getFileName($campaign, $form, 'Cartographie'));

        $black = new Color();
        $white = new Color('FFFFFFFF');
        $gray = new Color('FFE0E0E0');
        $darkGray = new Color('FFE6E6E6');

        $spreadsheet = new Spreadsheet();
        /** @noinspection PhpUnhandledExceptionInspection */
        $sheet = $spreadsheet->getActiveSheet();
        $titleCell = $sheet
            ->mergeCells('A1:A2')
            ->getCell('A1')
            ->setValue($analysis->getTitle()."\n".count($participations).' participant'.(count($participations)>1?'s':''))
        ;
        $titleCell->getStyle()->getAlignment()->setWrapText(true);
        $titleCell->getStyle()->getFont()->setSize(18);
        $titleCell->getStyle()->getFont()->setColor($white);
        $titleCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor($black);

        $sheet->getDefaultColumnDimension()->setAutoSize(false)->setWidth(10.27);
        $sheet->getColumnDimensionByColumn(1)->setWidth(35.09);
        $sheet->getRowDimension(2)->setRowHeight(45);

        $colors = $campaign->getAnalysisVersion()->getColors();
        for($i=0;$i<count($colors);$i++) {
            $cell = $sheet->getCellByColumnAndRow(2+$i, 1);
            $cell
                ->getStyle()
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB(substr($colors[$i], 1))
            ;
        }
        $columns = [
            ['size' => 2, 'text' => 'Déficit',],
            ['size' => 2, 'text' => 'Insuffisance',],
            ['size' => 2, 'text' => 'Manque',],
            ['size' => 5, 'text' => 'Zone d\'équilibre',],
            ['size' => 2, 'text' => 'Excès',],
            ['size' => 2, 'text' => 'Inadaptation',],
            ['size' => 2, 'text' => 'Débordement',],
        ];

        $currentCol = 2;
        foreach ($columns as $i => $column) {
            $sheet->getCellByColumnAndRow($currentCol, 2)->setValue($column['text']);
            $sheet->mergeCellsByColumnAndRow($currentCol, 2, $currentCol+$column['size']-1, 2);

            $colorCell = $sheet->getCellByColumnAndRow($currentCol, 1);
            $colorCell->getStyle()->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM)->setColor($black);
            if($i + 1 === count($columns)) {
                $colorCellLast = $sheet->getCellByColumnAndRow($currentCol+$column['size']-1, 1);
                $colorCellLast->getStyle()->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM)->setColor($black);
            }

            $currentCol+=$column['size'];
        }
        $style = $sheet->getStyle('B2:'.$sheet->getHighestColumn('2').'2');
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM)->setColor($black);
        $style->getFont()->setBold(true)->setSize(14);

        $row = 3;

        foreach($this->themesManager->getThemes($campaign->getAnalysisVersion()) as $theme) {
            if ($theme->isVirtual() || $theme->isSkipInAnalysis()) {
                continue;
            }
            $themeAnalysis = $analysis->getThemeAnalysis($theme);

            $sheet->getCellByColumnAndRow(1, $row)
                ->setValue($theme->getName()[$locale])
            ;
            $cutsDistribution = $themeAnalysis->getCutsDistribution();
            self::writeCutsDistributions($columns, $cutsDistribution, $sheet, $row, true); // Themes are always shown as percents
            $themeRow = $row;

            $row++;

            foreach ($theme->getItems() as $item) {
                $sheet->getCellByColumnAndRow(1, $row)
                    ->setValue($item->getName()[$locale])
                ;

                $itemAnalysis = $themeAnalysis->getItem($item);
                $cutsDistribution = $itemAnalysis->getCutsDistribution();
                self::writeCutsDistributions($columns, $cutsDistribution, $sheet, $row, $asPercent);

                $row++;
            }
            $highestColumn = $sheet->getHighestColumn($themeRow);
            $themeRowStyle = $sheet->getStyle('A'.$themeRow.':'.$highestColumn.$themeRow);
            $themeRowStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM)->setColor($black);
            $themeRowStyle->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor($darkGray);
            $themeRowStyle->getFont()->setBold(true)->setSize(14);
            $sheet->getRowDimension($themeRow)->setRowHeight(22);

            $itemsRowStyle = $sheet->getStyle('B'.($themeRow+1).':'.$highestColumn.($row-1));
            $itemsRowStyle->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN)->setColor($gray);
        }

        // Center all cells
        $highestRowAndColumn = $sheet->getHighestRowAndColumn();
        $sheet->getStyle('A1:'.$highestRowAndColumn['column'].$highestRowAndColumn['row'])->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
        ;
        if($format === ExcelExporter::FORMAT_HTML) {
            $sheet->getPageMargins()->setLeft(0);
            $sheet->getPageMargins()->setRight(0);
            $sheet->getPageMargins()->setTop(0);
            $sheet->getPageMargins()->setBottom(0);
        }

        return $this->exporter->spreadsheetToResponse($spreadsheet, $analysis->getFileName().'.'.$format, null, $format);
    }

    private static function writeCutsDistributions(array $columns, array $cutsDistribution, Worksheet $sheet, int $row, bool $asPercent): void
    {
        $colCursor = 2;
        $cutCursor = 0;
        foreach ($columns as $column) {
            $cuts = array_slice($cutsDistribution, $cutCursor, $column['size']);
            $columnParticipations = array_sum($cuts);
            $cell = $sheet->getCellByColumnAndRow($colCursor, $row);
            if($asPercent) {
                $columnParticipations = $columnParticipations / array_sum($cutsDistribution);
                $cell->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            }
            $cell->setValue($columnParticipations);
            $sheet->mergeCellsByColumnAndRow($colCursor, $row, $colCursor + $column['size'] - 1, $row);
            $colCursor += $column['size'];
            $cutCursor += $column['size'];
        }
    }
}
