<?php
/**
 * User: goulven
 * Date: 10/08/2022
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Util;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\TwigExtension;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Section;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignWordGenerator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var TwigExtension
     */
    private $ixaTwigExtension;
    private $kernelRootDir;

    public function __construct(TranslatorInterface $translator, TwigExtension $ixaTwigExtension, $kernelRootDir)
    {
        $this->translator = $translator;
        $this->ixaTwigExtension = $ixaTwigExtension;
        $this->kernelRootDir = $kernelRootDir;
    }

    public function generateWordDocument(Campaign $campaign, $participations, $analysis, Request $request)
    {
        $companyName = $campaign->getClient()->getTradingName()?:$campaign->getClient()->getCorporateName();

        $phpWord = WordExporter::createDocument();
        //a section represent a page
        $section = $phpWord->addSection(['orientation' => Section::ORIENTATION_LANDSCAPE]);
        $availableWidth = $section->getStyle()->getPageSizeW()-$section->getStyle()->getMarginLeft()-$section->getStyle()->getMarginRight();
        $header = $section->addHeader();
        $headerText = $header->addTextRun(['alignment' => Jc::END]);
        $headerText->addText('Analyse Workcare - '.$companyName.'     ');
        $ixaLogo = realpath(implode(DIRECTORY_SEPARATOR, [
            $this->kernelRootDir,
            '..',
            'web',
            'img/questionnaire/logo-green.png'
        ]));
        $headerText->addImage($ixaLogo, [
            'width' => Converter::cmToPoint(1.37),
            'marginLeft' => Converter::cmToInch(.57),
            'marginTop' => Converter::cmToInch(-1.26),
            'alignment' => Jc::END,
        ]);
        $footer = $section->addFooter();
        $footer->addText(
            'Ce document et son contenu sont la propriété exclusive de la SARL Montgolfière Management. '.
            'Toute reproduction ou détournement, en tout ou en partie, sous quelque forme que ce soit, est interdite'.
            ' sans l\'autorisation préalable du cabinet.', [
                'size' => 8,
                'color' => '808080',
            ]
        );

        $tr = new TextRun();
        $tr->addText($companyName.' - ');
        $tr->addText($campaign->getName().' - '.($campaign->getEndDate()?$campaign->getEndDate()->format('m/Y'):'').' - '.count($participations).' participants', [
            'size' => 14,
            'color' => '365f91',
            'font' => 'Cambria',
        ]);
        $section->addTitle($tr, 0);
        //fetch House image from form submission
        if($request->request->has('download_word_image')) {
            $img = $request->request->get('download_word_image');
            $img = substr($img, strlen('data:image/png;base64,'));
            $img = base64_decode($img);

            $section->addImage($img,[
                'width'         => 561,
                'height'        => 394,
                'wrappingStyle' => 'square',
                'positioning'      => Image::POSITION_RELATIVE,
                'posHorizontal'    => Image::POSITION_HORIZONTAL_CENTER,
                'posHorizontalRel' => Image::POSITION_RELATIVE_TO_COLUMN,
                'posVertical'      => Image::POSITION_VERTICAL_TOP,
                'posVerticalRel'   => Image::POSITION_RELATIVE_TO_LINE,
            ]);
        }
        $section->addPageBreak();
        $section->addTitle('Analyse globale', 1);
        $section->addTextBreak(10);

        $wellBeingAverage = 0;
        $engagementAverage = 0;
        foreach ($participations as $participation) {
            $wellBeingAverage+= $participation->getWellBeingScore();
            $engagementAverage+= $participation->getEngagementScore();
        }
        $wellBeingAverage/= count($participations);
        $engagementAverage/= count($participations);

        $wbeTable = $section->addTable();
        $wbeRow = $wbeTable->addRow();
        $wbeLeft = $wbeRow->addCell($availableWidth*.75);
        $wbeLeft->addTitle('Indice BEE');
        $wbeLeft->addText($this->translator->trans('montgolfiere.backoffice.campaigns.restitution.wbe_score_description'), null, ['alignment' => Jc::BOTH]);
        $rightTitleStyle = ['size' => '14',];
        $rightStyle = ['size' => '12',];
        $wbeRight = $wbeRow->addCell($availableWidth*.25)->addTextRun(['alignment' => Jc::CENTER, 'lineHeight' => '1.5']);
        $wbeRight->addText('Indice d\'Engagement :', $rightTitleStyle);
        $wbeRight->addTextBreak();
        $wbeRight->addText(number_format($engagementAverage, 2).'/10', $rightStyle);
        $wbeRight->addTextBreak();
        $wbeRight->addText('Indice de Bien-Être :', $rightTitleStyle);
        $wbeRight->addTextBreak();
        $wbeRight->addText(number_format($wellBeingAverage, 2).'/10', $rightStyle);
        $section->addPageBreak();

        $section->addTitle('Analyse par thème &amp; items', 1);

        $colsSizes = [10, 10, 40, 40]; // Percents
        $colsSizes = array_map(function(float $size) use ($availableWidth): float {return $availableWidth/100*$size;}, $colsSizes);
        $createTable = function() use ($colsSizes, $section) {
            $table = $section->addTable([
                'borderColor' => '#000000',
                'borderSize' => 12,
                'cellMarginLeft' => Converter::cmToTwip(0.19),
                'cellMarginRight' => Converter::cmToTwip(0.19),
            ]);
            $tableHeader = $table->addRow(null, ['tblHeader' => true,]);
            $cellStyle = ['bgColor' => 'FFFFFF', 'valign' => 'center',];
            $textStyle = ['color' => '000000', 'bold' => true, 'size' => 16];
            $paragraphStyle = ['alignment' => Jc::CENTER,];
            $tableHeader
                ->addCell($colsSizes[0], $cellStyle)
                ->addText('Thème', $textStyle, $paragraphStyle)
            ;
            $tableHeader
                ->addCell($colsSizes[1], $cellStyle)
                ->addText('Items', $textStyle, $paragraphStyle)
            ;
            $tableHeader
                ->addCell($colsSizes[2], $cellStyle)
                ->addText('Signification', $textStyle, $paragraphStyle)
            ;
            $tableHeader
                ->addCell($colsSizes[3], $cellStyle)
                ->addText('Lecture', $textStyle, $paragraphStyle)
            ;

            return $table;
        };
        $isNewTable = true;

        $previousTheme = null;
        $i = 0;
        foreach ($analysis->getThemesAnalysis() as $themeAnalysis) {
            $houseSettings = $themeAnalysis->getHouseSettings();
            $wordSettings = $themeAnalysis->getWordSettings();
            if(!$wordSettings || $wordSettings->isSkipInItemRestitutionTable()) {
                continue;
            }
            $table = $createTable();

            foreach ($themeAnalysis->getItems() as $j => $itemAnalysis) {
                $isNewTheme = $previousTheme !== $themeAnalysis;
                $cellStyle = ['valign' => 'center',];
                if(!$isNewTable && $isNewTheme) {
                    $cellStyle['borderTopColor'] = 'D7D7D7';
                    $cellStyle['borderTopSize'] = 12;
                }
                if($j % 2 === 0) {
                    $cellStyle['bgColor'] = 'EDEDED';
                }

                $row = $table->addRow(null, ['cantSplit' => true,]);
                $imageCell = $row->addCell($colsSizes[0], ['vMerge' => $isNewTheme ? 'restart' : 'continue', 'valign' => 'center','bgColor' => $campaign->getAnalysisVersion()->getColors()[$themeAnalysis->getTrend()],] + $cellStyle);
                if($isNewTheme && $houseSettings && $houseSettings->getImage() && $houseSettings->getImage()->getPath()) {
                    $imageCell->addImage(
                        str_replace('.svg', '.png', realpath(__DIR__.'/../../../../../web/'.$houseSettings->getImage()->getPath())),
                        ['width' => Converter::cmToPoint(1), 'alignment' => Jc::CENTER,]
                    );
                    $imageCell->addText(WordExporter::convertTextToWord($themeAnalysis->getName()), ['size' => 11,'bold' => true,], ['alignment' => Jc::CENTER,]);
                }
                $row->addCell($colsSizes[1], $cellStyle)
                    ->addText(WordExporter::convertTextToWord($itemAnalysis->getName()), ['size' => 11,'bold' => true,], ['alignment' => Jc::CENTER,])
                ;
                if ($itemAnalysis->getItem()) {
                    $row->addCell($colsSizes[2], $cellStyle)
                        ->addText(WordExporter::convertTextToWord($itemAnalysis->getLongSignification()??''), ['size' => 9,'italic' => true,])
                    ;
                    $row->addCell($colsSizes[3], ['bgColor' => $campaign->getAnalysisVersion()->getColors()[$itemAnalysis->getTrend()],] + $cellStyle)
                        ->addText(WordExporter::convertTextToWord($itemAnalysis->getShortSignification()??''), ['size' => 11,'bold' => true,], ['alignment' => Jc::CENTER,])
                    ;
                }
                $previousTheme = $themeAnalysis;
            }
            $row = $table->addRow(null, ['cantSplit' => true,]);
            $row->addCell($colsSizes[0], ['vMerge' => false ? 'restart' : 'continue',]);
            $row->addCell($colsSizes[1], ['valign' => 'center'])->addText(WordExporter::convertTextToWord('Analyse globale du thème'), ['size' => 11,'bold' => true,], ['alignment' => Jc::CENTER,]);
            $row->addCell($colsSizes[2], ['valign' => 'center'])->addText(WordExporter::convertTextToWord(strip_tags($themeAnalysis->getTheme()->getDescription()['fr'], 'br')), ['size' => 9,'italic' => true,], ['alignment' => Jc::CENTER,]);
            if ($themeAnalysis->getRestitution()) {
                $row->addCell($colsSizes[3], ['valign' => 'center'])->addText(WordExporter::convertTextToWord($themeAnalysis->getRestitution()->getTrendText()), ['size' => 9,], ['alignment' => Jc::CENTER,]);
            }

            $i++;
            if($i%2 === 1) {
                $section->addPageBreak();
            }
            else {
                $section->addTextBreak();
            }
        }

        $section->addTitle('Suivi et plan d\'action');
        $actionPlanTable = $section->addTable([
            'borderColor' => '#000000',
            'borderSize' => 6,
            'cellMarginLeft' => Converter::cmToTwip(0.19),
            'cellMarginRight' => Converter::cmToTwip(0.19),
        ]);
        $actionPlanHeader = $actionPlanTable->addRow();
        $headerStyle = [
            'size' => 16,
            'italic' => true,
        ];
        $centered = [
            'alignment' => Jc::CENTER,
        ];
        $actionPlanHeader->addCell($availableWidth/100*21.7)->addText('Thème', $headerStyle, $centered);
        $actionPlanHeader->addCell($availableWidth/100*39.15)->addText('Plan d\'action', $headerStyle, $centered);
        $actionPlanHeader->addCell($availableWidth/100*39.15)->addText('Remarques', $headerStyle, $centered);

        $themeStyle = [
            'size' => 12,
            'bold' => true,
        ];
        $themeColPc = 17.2;
        $textColPc = (100-$themeColPc)/2;

        foreach ($analysis->getThemesAnalysis() as $themeAnalysis) {
            $row = $actionPlanTable->addRow();
            $row->addCell($availableWidth/100*$themeColPc)->addText(WordExporter::convertTextToWord($themeAnalysis->getName()), $themeStyle, $centered);
            $actionPlanText = $themeAnalysis->getRestitution()?$themeAnalysis->getRestitution()->getActionPlanText():'';
            $actionPlanText = WordExporter::convertTextToWord($actionPlanText??'');
            if(!empty($actionPlanText)) {
                $actionPlanText = $this->ixaTwigExtension->replaceQuestionnaireWildcards(['campaign' => $campaign], $actionPlanText);
            }
            $row->addCell($availableWidth/100*$textColPc)->addText($actionPlanText);
            $row->addCell($availableWidth/100*$textColPc); // Empty cell available for writing
        }

        return $phpWord;
    }

    /**
     * @param Campaign $campaign
     * @param CampaignParticipation[] $participations
     * @param int[]|null $allowedQuestions
     * @return PhpWord
     */
    public static function exportVerbatims(Campaign $campaign, array $participations, ?array $allowedQuestions = null): PhpWord
    {
        $document = WordExporter::createDocument();
        $section = $document->addSection();
        $segments = array_unique(array_map(function(CampaignParticipation $participation): CampaignSegment {return $participation->getSegment();}, $participations), SORT_REGULAR);

        foreach ($segments as $segment) {
            $section->addTitle(WordExporter::convertTextToWord($segment->getName()));
            foreach ($segment->getSteps() as $step) {
                if($step->getType() !== CampaignSegmentStep::TYPE_QUESTION || !$step->getQuestion() || $step->getQuestion()->getType() != Question::TYPE_OPEN) {
                    continue;
                }
                if($allowedQuestions && !in_array($step->getQuestion()->getId(), $allowedQuestions)) {
                    continue;
                }
                $section->addTitle(strip_tags($step->getQuestion()->getQuestion()), 2);
                $section->addText(strip_tags($step->getQuestion()->getDescription()??''));

                foreach ($participations as $result) {
                    if($result->getSegment() !== $segment || !($answer = $result->getAnswer($step)) || $answer->getSkipped() || empty($answer->getOpenAnswer())) {
                        continue;
                    }
                    $title = '';
                    foreach ($campaign->getSortingFactors() as $sortingFactor) {
                        $sfValue = $result->getSortingFactorValue($sortingFactor);
                        if($sfValue) {
                            $title.= $sortingFactor->getNames()[$segment->getLocale()].' : '.$sfValue->getLabels()[$segment->getLocale()].' - ';
                        }
                    }
                    $title = substr($title, 0, -3);
                    $section->addTitle(strip_tags($title), 3);
                    $section->addText(strip_tags(str_replace('&', '&amp;', $answer->getOpenAnswer())));
                }
            }
        }

        return $document;
    }

}
