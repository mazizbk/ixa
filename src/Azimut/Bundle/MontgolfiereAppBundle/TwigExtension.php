<?php


namespace Azimut\Bundle\MontgolfiereAppBundle;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\SortingFactorManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var ThemesManager
     */
    protected $themesManager;

    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    /**
     * @var EntrypointLookupInterface
     */
    protected $entrypointLookup;

    /**
     * @var string
     */
    protected $publicDir;


    public function __construct(ThemesManager $themesManager, SortingFactorManager $sortingFactorManager, EntrypointLookupInterface $entrypointLookup, string $publicDir)
    {
        $this->themesManager = $themesManager;
        $this->sortingFactorManager = $sortingFactorManager;
        $this->entrypointLookup = $entrypointLookup;
        $this->publicDir = $publicDir;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('ixa_wbe_well_being_profile', [WBEManager::class, 'getWellBeingProfileFromParticipation']),
            new TwigFunction('ixa_wbe_engagement_profile', [WBEManager::class, 'getEngagementProfileFromParticipation']),
            new TwigFunction('ixa_sorting_factor_name', [$this, 'getSortingFactorName'], ['needs_context' => true,]),
            new TwigFunction('ixa_sorting_factor_value_name', [$this, 'getSortingFactorValueName'], ['needs_context' => true,]),
            new TwigFunction('ixa_progress_color', [CampaignManager::class, 'getProgressColor']),
            new TwigFunction('ixa_trend_color', [CampaignAnalyser::class, 'getTrendColor']),
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('extractPossibleValues', [$this, 'extractPossibleValues']),
            new TwigFilter('ixa_questionnaire_wildcard', [$this, 'replaceQuestionnaireWildcards'], ['needs_context' => true,]),
            new TwigFilter('ixa_trans', [$this, 'ixaTrans'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('ixa_wbe_low_score', [WBEManager::class, 'hasLowWBEScore',]),
            new TwigTest('in_the_future', function(\DateTimeInterface $date): bool {$dateClass = get_class($date); return $date > new $dateClass('now');})
        ];
    }

    public function getGlobals()
    {
        return [
            'ixa_trends_cuts' => CampaignAnalyser::getTrendsCuts(),
        ];
    }

    public function extractPossibleValues($values)
    {
        $lines = explode("\n", $values);
        $result = [];
        foreach ($lines as $line) {
            if(($separatorPos = strpos($line, '|')) === false) {
                $result[] = ['label' => $line,];
            }
            else {
                $result[] = ['label' => substr($line, $separatorPos+1), 'value' => substr($line, 0, $separatorPos)];
            }
        }

        return $result;
    }

    public function getSortingFactorName(array $context, CampaignSortingFactor $sortingFactor): string
    {
        /** @var Request $request */
        $request = $context['app']->getRequest();
        return $this->sortingFactorManager->getSortingFactorName($request->attributes->getAlpha('_locale'), $sortingFactor);
    }

    public function getSortingFactorValueName(array $context, CampaignSortingFactorValue $value): string
    {
        /** @var Request $request */
        $request = $context['app']->getRequest();
        return $this->sortingFactorManager->getSortingFactorValueName($request->attributes->getAlpha('_locale'), $value);
    }

    public function replaceQuestionnaireWildcards(array $context, string $string): string
    {
        /** @var Campaign $campaign */
        $campaign = $context['campaign'];
        $company = $campaign->getClient();
        $expressionContext = [
            'companyName' => $company->getTradingName()??$company->getCorporateName(),
            'companyTradingName' => $company->getTradingName(),
            'companyCorporateName' => $company->getCorporateName(),
            'companyQuestionName' => $company->getQuestionName(),
        ];

        return preg_replace_callback('`{(\w+)}`', function(array $matches) use ($expressionContext) {
            if(array_key_exists($matches[1], $expressionContext)) {
                return $expressionContext[$matches[1]];
            }
            return $matches[0];
        }, $string);
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $this->entrypointLookup->reset();
        $files = $this->entrypointLookup->getCssFiles($entryName);
        $source = '';

        foreach ($files as $file) {
            // In dev, when using dev-server, $file is an URL
            // In prod, it always is a path relative to the public dir
            if(stripos($file, 'http')!==0) {
                $file = $this->publicDir.'/'.$file;
            }
            $source .= file_get_contents($file);
            // Remove sourcemap if present
            $source = preg_replace('~//[#@]\s(source(?:Mapping)?URL)=\s*(\S+)~', '', $source);
        }

        return $source;
    }

    public function ixaTrans(string $string): string
    {
        $string = preg_replace('/`(.*)`/U', '<span class="text-primary">$1</span>', $string);
        $string = preg_replace('/_(.*)_/U', '<strong>$1</strong>', $string);

        return $string;
    }

}
