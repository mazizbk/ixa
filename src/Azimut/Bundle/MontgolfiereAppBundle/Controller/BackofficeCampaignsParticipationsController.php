<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\QuestionnaireBasicInformationType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignWordGenerator;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ExcelExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ParticipationFilterHelper;
use Azimut\Bundle\MontgolfiereAppBundle\Util\SortingFactorManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WordExporter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Events;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Timestampable\TimestampableListener;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeCampaignsParticipationsController extends AbstractBackofficeSubEntityController implements SupportsFilteringEntityController
{
    protected static $parentClass = Campaign::class;
    protected static $parentPropertyName = 'participations';
    protected static $subEntityClass = CampaignParticipation::class;
    protected static $subEntityPropertyName = 'campaign';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/participations.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/participations_new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/participations_edit.html.twig';
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_campaigns_participations';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.participations';
    protected static $parentRouteParamName = 'id';
    protected static $parentRouteParamValue = 'id';
    protected static $subEntityRouteParamName = 'participation';
    protected static $subEntityRouteParamValue = 'id';
    protected static $disableSoftdeleteable = true;
    /**
     * @var ExcelExporter
     */
    protected $exporter;
    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    /**
     * @var ParticipationFilterHelper
     */
    protected $participationFilterHelper;

    /**
     * @var WBEManager
     */
    private $WBEManager;
    /**
     * @var CampaignAnalyser
     */
    private $campaignAnalyser;

    public function __construct(RouterInterface $router, TranslatorInterface $translator, PropertyAccessorInterface $propertyAccessor, PaginatorInterface $paginator, ExcelExporter $exporter, SerializerInterface $serializer, SortingFactorManager $sortingFactorManager, WBEManager $WBEManager, ParticipationFilterHelper $participationFilterHelper, CampaignAnalyser $campaignAnalyser)
    {
        parent::__construct($router, $translator, $propertyAccessor, $paginator, $serializer);
        $this->exporter = $exporter;
        $this->sortingFactorManager = $sortingFactorManager;
        $this->WBEManager = $WBEManager;
        $this->participationFilterHelper = $participationFilterHelper;
        $this->campaignAnalyser = $campaignAnalyser;
    }

    /**
     * @param Campaign        $entity
     * @param CampaignParticipation $subEntity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $entity->getId() === $subEntity->getCampaign()->getId();
    }

    /**
     * @ParamConverter("subEntity", converter="azimut_backoffice_subentity")
     */
    public function readAction(Campaign $campaign, CampaignParticipation $subEntity)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->disableSoftDeleteableIfConfigured();

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Campaigns/participations_read.html.twig', [
            'campaign' => $campaign,
            'participation' => $subEntity,
        ]);
    }

    public function sendEmailWBEAction(Campaign $campaign, CampaignParticipation $participation, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$participation->getEmailAddress()){
            $request->getSession()->getFlashBag()->add('error', $this->translator->trans('montgolfiere.backoffice.campaigns.participations.flash.email_not_sent'));
        }else {
            $this->WBEManager->sendEmail($participation, $participation->getEmailAddress(), 'fr');
            $request->getSession()->getFlashBag()->add('success', $this->translator->trans('montgolfiere.backoffice.campaigns.participations.flash.email_sent'));
        }

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_participations_read', ['id' => $campaign->getId(),'participation' => $participation->getId()]);
    }

    public function blockWBEAction(Campaign $campaign, CampaignParticipation $participation, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $participation->setToken(null);
        $this->getDoctrine()->getManager()->flush();
        $request->getSession()->getFlashBag()->add('success', $this->translator->trans('montgolfiere.backoffice.campaigns.participations.flash.wbe_blocked'));


        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_participations_read', ['id' => $campaign->getId(),'participation' => $participation->getId()]);
    }

    /**
     * @ParamConverter("subEntity", converter="azimut_backoffice_subentity")
     */
    public function unarchiveAction(Campaign $campaign, CampaignParticipation $subEntity)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $subEntity->setArchivedAt(null);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_participations', ['id' => $campaign->getId(),]);
    }

    protected function getFormType()
    {
        return QuestionnaireBasicInformationType::class;
    }

    /**
     * @param         $entity
     * @param         $subEntity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     * @ParamConverter("subEntity", converter="azimut_backoffice_subentity")
     */
    public function updateAction($entity, $subEntity, Request $request)
    {
        $this->disableTimestampableListener();

        return parent::updateAction($entity, $subEntity, $request);
    }

    /**
     * @param CampaignParticipation $subEntity
     * @param Campaign $entity
     * @param string $type
     * @param bool $isXHR
     * @return FormInterface
     */
    protected function createEditForm($subEntity, $entity, string $type, bool $isXHR): FormInterface
    {
        $form = parent::createEditForm($subEntity, $entity, $type, $isXHR);

        // Because sorting factors fields are unmapped, we need to set their data here so they're not lost
        foreach ($entity->getSortingFactors() as $sortingFactor) {
            $value = $subEntity->getSortingFactorValue($sortingFactor);
            $form->get('sorting_factor_'.$sortingFactor->getId())->setData($value);
        }

        return $form;
    }


    protected function getEntityQuery()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);

        return $repo->createQueryBuilder('cp')
            ->leftJoin('cp.segment', 'e')
            ->where('cp.finished = true')
            ->orderBy('cp.updatedAt')
        ;
    }

    public function getFilterForm($entity)
    {
        return $this->participationFilterHelper->getFilterForm($entity, 'GET', 'azimut_montgolfiere_app_backoffice_campaigns_participations');
    }

    public function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder, $entity)
    {
        $this->participationFilterHelper->handleFilterForm($filterForm, $queryBuilder, $entity);
    }

    public function isFiltered(FormInterface $filterForm)
    {
        // Instead of checking all form inputs, just check if we have any submitted data that isn't perpage
        return count(array_diff(array_keys($filterForm->getData()), ['perpage']))>0;
    }

    /**
     * @param Campaign $entity
     * @param Request  $request
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportAction(Campaign $entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $query = $this->getEntityQuery()
            ->andWhere('e.'.$this::$subEntityPropertyName.' = :parent')
            ->setParameter(':parent', $entity)
        ;
        $filterForm = $this->getFilterForm($entity);
        $filterForm->handleRequest($request);
        if($filterForm->isSubmitted() && $filterForm->isValid()) {
            $this->handleFilterForm($filterForm, $query, $entity);
        }
        $camelCaseToSnakeCaseConverter = new CamelCaseToSnakeCaseNameConverter();

        /** @var CampaignParticipation[] $results */
        $results = $query->getQuery()->getResult();
        $tr = function($id, array $parameters = array(), $domain = null, $locale = null) {
            return $this->translator->trans($id, $parameters, $domain, $locale);
        };

        $header = [];
        foreach ($entity->getSortingFactors() as $sortingFactor) {
            $header[] = $this->sortingFactorManager->getSortingFactorName($this->translator->getLocale(), $sortingFactor);
        }
        $header[] = $tr('montgolfiere.questionnaire.basic_information.segment');
        foreach (Campaign::$configurableFields as $configurableField) {
            if ($entity->getFieldStatus($configurableField) != Campaign::FIELD_STATUS_DISABLED){
                $header[] = $tr('montgolfiere.questionnaire.basic_information.'. $camelCaseToSnakeCaseConverter->normalize($configurableField));
            }
        }
        $header = array_merge($header, [
            $tr('montgolfiere.backoffice.campaigns.participations.date'),
            $tr('montgolfiere.backoffice.campaigns.participations.answer_duration'),
            $tr('montgolfiere.backoffice.campaigns.participations.ip_address'),
            $tr('montgolfiere.backoffice.campaigns.participations.well_being'),
            $tr('montgolfiere.backoffice.campaigns.participations.engagement'),
        ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $additionalQuestionsHeader = $header;
        $additionalQuestions = new ArrayCollection();
        $additionalQuestionsContent = [];
        foreach ($entity->getSegments() as $segment) {
            if(!$segment->isValid()) {
                continue;
            }

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr(str_replace(Worksheet::getInvalidCharacters(), '', $segment->getName()), 0, 27));
            $sheetHeader = $header;
            foreach ($segment->getSteps() as $step) {
                switch($step->getType()) {
                    case CampaignSegmentStep::TYPE_ITEM:
                        $sheetHeader[] = $step->getItem()->getName()[$request->getLocale()];
                        break;
                    case CampaignSegmentStep::TYPE_QUESTION:
                        $sheetHeader[] = strip_tags($step->getQuestion()->getQuestion());
                        if ($step->getQuestion()->getType() != Question::TYPE_SLIDER_VALUE && !$additionalQuestions->contains($step->getQuestion())){
                            $additionalQuestions->add($step->getQuestion());
                        }
                        break;
                }
            }
            $sheetContents = [$sheetHeader];
            foreach ($results as $participation) {
                if($participation->getSegment() !== $segment) {
                    continue;
                }
                $line = [];
                foreach ($entity->getSortingFactors() as $sortingFactor) {
                    $value = $participation->getSortingFactorValue($sortingFactor);
                    if(!$value) {
                        $line[] = '';
                        continue;
                    }
                    $line[] = $this->sortingFactorManager->getSortingFactorValueName($this->translator->getLocale(), $value);
                }
                $line[] = $participation->getSegment()->getName();
                if ($entity->getFieldStatus('seniority') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getSeniority())?$tr('montgolfiere.questionnaire.basic_information.seniorities.'.$participation->getSeniority()):'';
                }
                if ($entity->getFieldStatus('gender') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getGender())?$tr('montgolfiere.questionnaire.basic_information.genders.'.$participation->getGender()):'';
                }
                if ($entity->getFieldStatus('firstName') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getFirstName();
                }
                if ($entity->getFieldStatus('lastName') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getLastName();
                }
                if ($entity->getFieldStatus('emailAddress') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getEmailAddress();
                }
                if ($entity->getFieldStatus('phoneNumber') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getPhoneNumber();
                }
                if ($entity->getFieldStatus('managerName') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getManagerName();
                }
                if ($entity->getFieldStatus('education') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getEducation())?$tr('montgolfiere.questionnaire.basic_information.educations.'.$participation->getEducation()):'';
                }
                if ($entity->getFieldStatus('csp') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getCsp())?$tr('montgolfiere.questionnaire.basic_information.csps.'.$participation->getCsp()):'';
                }
                if ($entity->getFieldStatus('age') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getAge())?$tr('montgolfiere.questionnaire.basic_information.ages.'.$participation->getAge()):'';
                }
                if ($entity->getFieldStatus('maritalStatus') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getMaritalStatus())?$tr('montgolfiere.questionnaire.basic_information.marital_statuses.'.$participation->getMaritalStatus()):'';
                }
                if ($entity->getFieldStatus('managementResponsibilities') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getManagementResponsibilities())?$tr('montgolfiere.questionnaire.basic_information.management_responsibilities_values.'.$participation->getManagementResponsibilities()):'';
                }
                if ($entity->getFieldStatus('position') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = $participation->getPosition();
                }
                if ($entity->getFieldStatus('residenceState') != Campaign::FIELD_STATUS_DISABLED){
                    $line[] = !is_null($participation->getResidenceState())?$tr('montgolfiere.questionnaire.basic_information.states.'.$participation->getResidenceState()):'';
                }
                $line = array_merge($line, [
                    $participation->getUpdatedAt(),
                    NumberFormat::toFormattedString($participation->getUpdatedAt()->diff($participation->getCreatedAt())->format('%H:%I:%S'), NumberFormat::FORMAT_DATE_TIME4),
                    $participation->getIPAddress(),
                    round($participation->getWellBeingScore(), 2),
                    round($participation->getEngagementScore(), 2),
                ]);
                $additionalQuestionsContentLine = $line;
                $additionalQuestionsIndex = 0;
                foreach ($segment->getSteps() as $step) {
                    $answerValue = null;
                    switch($step->getType()) {
                        case CampaignSegmentStep::TYPE_ITEM:
                        case CampaignSegmentStep::TYPE_QUESTION:
                            $answer = $participation->getAnswer($step);
                            if(!$answer){
                                continue;
                            }
                            if($answer->getSkipped()) {
                                $answerValue = 'N/C';
                            }
                            elseif($answer->getOpenAnswer()) {
                                if(is_array($answer->getOpenAnswer())) {
                                    $answerValue = implode("\n", $answer->getOpenAnswer());
                                }
                                else {
                                    $answerValue = $answer->getOpenAnswer();
                                }
                            }
                            else {
                                $answerValue = ($answer->getValue()<0?'-':'').(10-abs($answer->getValue())).($answer->getValue()===-10?' (à gauche)':'');
                            }
                            break;
                    }
                    if ($answerValue !== null) {
                        $line[] = $answerValue;

                        if ($step->getQuestion()->getType() != Question::TYPE_SLIDER_VALUE) {
                            for ($i = $additionalQuestionsIndex; $i < count($additionalQuestions); $i++) {
                                if ($additionalQuestions->get($i)->getId() == $step->getQuestion()->getId()) {
                                    $additionalQuestionsContentLine[] = $answerValue;
                                    $additionalQuestionsIndex = $i+1;
                                    break;
                                }
                                $additionalQuestionsContentLine[] = '';
                            }
                        }
                    }
                }

                $sheetContents[] = $line;
                $additionalQuestionsContent[] = $additionalQuestionsContentLine;
            }
            $this->exporter->arrayToSheet($sheetContents, $sheet);
        }
        if (count($additionalQuestionsContent) > 0){
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Questions additionnelles');
            foreach($additionalQuestions as $question){
                $additionalQuestionsHeader[] = $question->getQuestion();
            }
            $sheetContents = [$additionalQuestionsHeader,];
            $sheetContents = array_merge($sheetContents, $additionalQuestionsContent);
            $this->exporter->arrayToSheet($sheetContents, $sheet);
        }

        return $this->exporter->spreadsheetToResponse($spreadsheet, 'Export participations '.(new \DateTime())->format('Y-m-d H:i:s').'.xlsx', 'export-participations.xlsx', ExcelExporter::FORMAT_XLSX);
    }

    /**
     * @param Campaign $entity
     * @param Request  $request
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportVerbatimsAction(Campaign $entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->disableSoftDeleteableIfConfigured();

        $query = $this->getEntityQuery()
            ->andWhere('e.'.$this::$subEntityPropertyName.' = :parent')
            ->setParameter(':parent', $entity)
        ;
        $filterForm = $this->getFilterForm($entity);
        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $this->handleFilterForm($filterForm, $query, $entity);
        }

        /** @var CampaignParticipation[] $results */
        $results = $query->getQuery()->getResult();

        $document = CampaignWordGenerator::exportVerbatims($entity, $results);

        $fileName = $this->campaignAnalyser->getFileName($entity, $filterForm, 'Verbatims') .'.docx';
        $fileName = str_replace(['\\', '/', '%'], '', $fileName);

        return WordExporter::makeResponse($document, $fileName);
    }

    protected function disableTimestampableListener()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $eventManager = $em->getEventManager();
        foreach ($eventManager->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if($listener instanceof TimestampableListener) {
                    $eventManager->removeEventListener([Events::prePersist, Events::loadClassMetadata, Events::onFlush,], $listener);
                    return;
                }
            }
        }
    }


}
