<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    //Injection de dépendances hors d'un controller : on a besoin d'un constructeur
    private $repo;
    private $rs;

    public function __construct(ProductRepository $repo, RequestStack $rs)
    {
        $this->repo = $repo;
        $this->rs = $rs;
    }
    public function add($id)
    {
        //RequestStack est une classe qui contient la session
        $session = $this->rs->getSession();

        //je recupère l'attribut de session 'cart' s'il existe, sinon un tableau vide
        $cart = $session->get('cart', []);

        //si le produit est déjà ajouté dans le panier, on incrémente la quantité
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else { // sinon on l'ajoute au panier
            $cart[$id] = 1;
        }

        //je sauvegarde l'état de mon panier en session à l'attribut de session 'cart'
        $session->set('cart', $cart);

        //dd() = dump & die : afficher des infos et tuer l'exécution du code
        // dd($session->get('cart'));
    }
    public function remove($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        //si l'id existe dans mon panier je le supprime du tab via unset()
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
    }

    public function getCardWithData()
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        // $qt est une var qui contiendra le nb total de produit
        $qt = 0;

        //créer un nouveau tableau qui contiendra des objets Product et les quantités
        //$cartWithData est un tableau multidimensionnel car chaque case du tableau est un tableau de 2 cases (Produit, quantité)
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $this->repo->find($id),
                'quantity' => $quantity
            ];
            $qt += $quantity;
        }
        //créer attr qt s'il n'existe pas ou l'actualiser s'il existe
        $session->set('qt', $qt);

        return $cartWithData;
    }

    public function getTotal()
    {
        //calculer le total du panier
        $total = 0;

        //pour chaque produit dans mon panier, j'ajoute mon total de mon produit au total final
        foreach ($this->getCardWithData() as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }
        return $total;
    }
}
