<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;



use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\ProductTypeUpdate;
use App\Form\ProductUpdateType;

#[Route('/editor/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager ,SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image=$form->get('image')->getData();
            if($image){
                $originalFilename=pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename=$slugger->slug($originalFilename);
                $newFilename=$safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                try{
                    $image->move(
                        $this->getParameter('product_image_directory'),
                        $newFilename
                    );
                }catch(FileException $e){}
                $product->setImage($newFilename);
            }
            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setQte($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt((new \DateTimeImmutable()));
            $entityManager->persist($stockHistory);
            $entityManager->flush();




            $this->addFlash('success', 'Product created successfully!');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
            
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager,SluggerInterface $sluggerInterface): Response
        
        {
        $form = $this->createForm(ProductUpdateType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->get('_token'))) {
            
            $entityManager->remove($product);
            $historyRepository = $entityManager->getRepository(AddProductHistory::class);
            $histories = $historyRepository->findBy(['product' => $product]);
            foreach ($histories as $history) {
                $entityManager->remove($history);
            }

            $entityManager->flush();
            $this->addFlash('danger', 'Product deleted successfully!');
            
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/add/product/{id}/stock', name: 'app_product_add_stock', methods: ['GET', 'POST'])]
    public function addStock($id,Request $request, Product $product, EntityManagerInterface $entityManager,ProductRepository $productRepository): Response
    {
        $stockHistory = new AddProductHistory();
        $addstock = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $addstock);
        $form->handleRequest($request);
        $product = $productRepository->find($product->getId());

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('qte')->getData()<=0){
                $this->addFlash('danger', 'Please enter a positive number!');
                return $this->redirectToRoute('app_product_add_stock', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            
            }
            else{$stockHistory->setProduct($product);
                $stockHistory->setCreatedAt((new \DateTimeImmutable()));
                $newqte=$form->get('qte')->getData()+$product->getStock();
                $product->setStock($newqte);
                $stockHistory->setQte($newqte);
                $entityManager->persist($product);
                $entityManager->persist($stockHistory);
                $entityManager->flush();
    
                $this->addFlash('success', 'Stock added successfully!');
    
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
                
            }
            
        }

        return $this->render('product/add_stock.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
}
