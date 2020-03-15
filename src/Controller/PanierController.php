<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\LignePanier;
use App\Form\PanierType;
use App\Entity\Produit;
use App\Form\ProduitsType;
use Symfony\Component\Validator\Constraints\DateTime;


class PanierController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function produit(Request $request, EntityManagerInterface $entityManager)
    {
        $produit = new Produit();
        
        $produitRepository = $this->getDoctrine()
        ->getRepository (Produit::class)
        ->findAll();

        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $produit = $form->getData();
            
            $image = $produit->getPhoto();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_files'), $imageName);
            $produit->setPhoto($imageName);

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit');
        }
        return $this->render('panier/produit.html.twig', [
            'produits' => $produitRepository,
            'formProduit' => $form->createView(),
        ]);
    }
    /**
     * @Route("/SingleProduit/{id}", name="singleProduit")
     */
    public function singleProduit($id, Request $request, EntityManagerInterface $entityManager){
        
        $panier = new LignePanier();
        
        $produit = $this-> getDoctrine()
            ->getRepository(Produit::class)
            ->find($id);

        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $panier = $form->getData();
            $panier->setDateAjout(new \DateTime())
            ->setEtat(false)
            ->setProduit($produit);
        
            $entityManager->persist($panier);
            $entityManager->flush();

           return $this->redirectToRoute('produit');
        }

        return $this->render('panier/singleProduit.html.twig',[
            'produit' => $produit,
            'formPanier' => $form->createView(),
            'panier' => $panier,
        ]);
    }
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, EntityManagerInterface $entityManager){
        
        $panierRepository = $this->getDoctrine()
        ->getRepository (LignePanier::class)
        ->findAll();

        
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository,
        ]);
    }
    public function update()
        {
            $panierRepository = $this->getDoctrine()->getManager();
            $panier = $panierRepository->getRepository(LignePanier::class)->findAll();
        
            $panier->setEtat(true);
            $panierRepository->flush();
        
            return $this->redirectToRoute('panier/index.html.twig', [
                'id' => $panier->getId()
            ]);
        }
}