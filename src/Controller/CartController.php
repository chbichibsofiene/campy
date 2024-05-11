<?php
namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
class CartController extends AbstractController
{

public function index(Request $request ,ProductRepository $productRepository): Response
{
    $panierData = [];
    $session = $request->getSession();
    $panier = $session->get('panier', []);
    foreach ($panier as $productId => $quantity) {
        $product = $productRepository->find($productId);
        $panierData[] = [
            'productId' => $productId,
            'productName' => $product->getName(),
            'quantite' => $quantity,
            'price' => $product->getPrice()
        ];
    }
    return $this->render('cart/index.html.twig', ['items' => $panierData]);
}

public function deleteitem($id, Request $request): Response
{
    $session = $request->getSession();
    $panier = $session->get('panier', []);
    if (!empty($panier[$id]))
        unset($panier[$id]);        
    $session->set('panier', $panier);
    return $this->redirectToRoute('cart');
}   
/**
* @Route("/add", name="app_add")***/
public function additem($id, Request $request, ProductRepository $productRepository): Response
{
    $session = $request->getSession();
    $panier = $session->get('panier', []);
    if (!empty($panier[$id])) {
        $product = $productRepository->find($id);
        return $this->redirectToRoute('cart');
    }
    if (!empty($panier[$id])) {
        $panier[$id]++;
    } else {
        $panier[$id] = 1;
    }
    $session->set('panier', $panier);
    return $this->redirectToRoute('cart');
}
}