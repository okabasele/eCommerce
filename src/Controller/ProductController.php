<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Product;
use App\Form\ProductType;
use App\Notification\ContactNotification;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    // #[Route('/product', name: 'app_product')]
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $repo, CategoryRepository $repoCat, RequestStack $rs): Response
    {
        $products = $repo->findAll();
        $categories = $repoCat->findAll();
        $session = $rs->getSession();
        $session->set('categories', $categories);
        return $this->render('product/index.html.twig', [
            'products'=>$products,
        ]);
    }

    #[Route('/product/show/{id}', name: 'prod_show')]
    public function show(Product $product)
    {
        $related = $product->getCategoryId()->getProducts();
        $tmp= $related->removeElement($product);
        $related = $related->slice(0,3);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'related'=> $related
        ]);   
    }

    #[Route('/product/new', name: 'prod_create')]
    #[Route('/product/edit/{id}', name: 'prod_edit')]
    public function form(Request $request, EntityManagerInterface $manager, Product $product = null)
    {
        // la classe Request contient toutes les données des superglobales
        // dump($request);
        if(!$product)
        {
            $product = new Product;
            $product->setUserId($this->getUser());
        }
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        dump($product);
        // handleRequest() permet de faire des vérifications sur le form (méthode du formulaire ? est-ce que les champs sont remplis ?)
        // permet aussi de remplir l'objet $product avec les données du form

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($product);    // préparer l'insertion de l'Product en bdd
            $manager->flush();  // insère l'Product
            return $this->redirectToRoute('prod_show', [
                'id' => $product->getId()
            ]);
        }

        return $this->render("product/form.html.twig", [
            'editMode' => $product->getId() !== null,
            'formProduct' => $form->createView()    // createView() renvoie un objet pour afficher le formulaire
        ]);
    }

    #[Route('/contact', name: 'app_contact')]

    public function contact(Request $request, EntityManagerInterface $manager, ContactNotification $cn)
    {
        $contact = new Contact;
        $contact->setCreatedAt(new \DateTime()); 
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($contact);
            $manager->flush();
            $cn->notify($contact);
            $this->addFlash('success', 'Votre message a bien été envoyé !');
            // addFlash() permet de créer des msg de notifications
            // elle prend en param le type et le msg
            return $this->redirectToRoute('blog_contact');
            // permet de recharger la page et vider les champs du form
        }
        return $this->render("blog/contact.html.twig", [
            'formContact' => $form->createView()
        ]);
    }
}
