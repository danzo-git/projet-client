<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    private $categoryRepository;
    private $subCategoryRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->subCategoryRepository = $subCategoryRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_all_categories', [$this, 'getAllCategories']),
            new TwigFunction('get_all_subcategories', [$this, 'getAllSubCategories']),
        ];
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->findAll();
    }

    public function getAllSubCategories()
    {
        return $this->subCategoryRepository->findAll();
    }
} 