<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(HttpFoundationRequest $request, ProductRepository $productRepository): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);
        $panierData = [];
        $totalPrice = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productRepository->find($id);
            $panierData[] = [
                'id' => $id,
                'quantity' => $quantity,
                'name' => $name = $product ? $product->getName() : 'Unknown Product',
                'price' => $product ? $product->getPrice() : 0,
            ];
            $totalPrice += $product ? $product->getPrice() * $quantity : 0;
        }

        return $this->render('cart/index.html.twig', [
            'items' => $panierData,
            'totalPrice' => $totalPrice,
        ]);
    }
    #[Route('/cart/{id}/add', name: 'app_cart_add')]
    public function add($id, HttpFoundationRequest $request)
    {
    $session = $request->getSession();
    $panier = $session->get('panier',[]);
    if(!empty($panier[$id]))
    $panier[$id]++;
    else
    $panier[$id]=1;
    
    $session->set('panier',$panier);
    $this->addFlash('success', 'Item added to Cart Successfully');
    return $this->redirectToRoute('cart');
    }
    #[Route('/cart/{id}/remove', name: 'app_cart_remove')]
    public function remove($id, HttpFoundationRequest $request)
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);
        
        if (!empty($panier[$id])) {
            unset($panier[$id]);
            $session->set('panier', $panier);
        }
        $this->addFlash('success', 'Item re Successfully');
        return $this->redirectToRoute('app_cart');
    }
    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(HttpFoundationRequest $request)
    {
        $session = $request->getSession();
        $session->remove('panier');
        $this->addFlash('danger', 'Cart cleared Successfully');
        return $this->redirectToRoute('app_cart');
    }
}

