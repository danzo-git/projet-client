<?php

namespace App\EventSubscriber;

use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class TwigGlobalVariablesSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $categoryRepository;
    private $subCategoryRepository;

    public function __construct(
        Environment $twig,
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository
    ) {
        $this->twig = $twig;
        $this->categoryRepository = $categoryRepository;
        $this->subCategoryRepository = $subCategoryRepository;
    }

    public function onKernelController(ControllerEvent $event)
    {
        // Ajouter les variables globales à Twig
        $this->twig->addGlobal('global_categories', $this->categoryRepository->findAll());
        $this->twig->addGlobal('global_subcategories', $this->subCategoryRepository->findAll());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
} 