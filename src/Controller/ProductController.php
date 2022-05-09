<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    // #[Route('/product', name: 'app_product')]
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $repo, CategoryRepository $repoCat): Response
    {
        $products = $repo->findAll();
        $categories = $repoCat->findAll();
        return $this->render('product/index.html.twig', [
            'nbArticlePanier'=> 2,
            'totalPanier'=> '200 ',
            'products'=>$products,
            'categories'=> $categories
        ]);
    }
}
