<?php

namespace App\Service\Admin;

use App\Repository\Admin\LanguageRepository;
use App\Repository\Admin\ProjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectService
{
    private $projectRepository;
    private $languageRepository;
    private $requestStack;

    public function __construct(
        ProjectRepository $projectRepository,
        LanguageRepository $languageRepository,
        RequestStack $requestStack,
    )
    {
        $this->projectRepository = $projectRepository;
        $this->languageRepository = $languageRepository;
        $this->requestStack = $requestStack;
    }
    
    /**
     * getProject
     *
     * předpoklad je, že project id je vždy 1
     * 
     * @return void
     */
    public function getProject()
    {
        $request = $this->requestStack->getCurrentRequest();
        
        $language = $this->languageRepository->findOneBy(['urlAlias' => $request->getLocale()]);
        return $this->projectRepository->getProject(1, $language->getId());
    }
}
