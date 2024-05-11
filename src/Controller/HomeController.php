<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home',methods: ['GET'])]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $productRepository->findBy([],['id'=>'DESC']),
            'categories'=>$categories
        ]);
    }

    #[Route('/home/product/{id}/show', name: 'app_home_show_product',methods: ['GET'])]
    public function show(Product $product,ProductRepository $productRepository,$id,CategoryRepository $categoryRepository): Response
    {
        $lastProducts = $productRepository->findBy([],['id'=>'DESC'],4);
        return $this->render('home/show.html.twig', [
            
            'product'=>$product,
            'products'=>$lastProducts,
            'categories'=>$categoryRepository->findAll()
            
            
        ]);
    }

    #[Route('/home/product/subcategory/{id}/filter', name: 'app_home_product_filter',methods: ['GET'])]
    public function filter($id,SubCategoryRepository $subCategoryRepository,CategoryRepository $categoryRepository): Response
    {
        $product=$subCategoryRepository->find($id)->getProducts();
        $subCategory = $subCategoryRepository->find($id);
        return $this->render('home/filter.html.twig', [
            'products'=>$product,
            'subCategory'=>$subCategory,
            'categories'=>$categoryRepository->findAll()

            
        ]);
    }

}
