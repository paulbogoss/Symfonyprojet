<?php

namespace App\Controller;


use App\Entity\Proprietaire;
use App\Entity\Categorie;
use App\Form\ProprietaireType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProprietaireController extends AbstractController
{
    /**
     * @Route("/proprietaires", name="app_proprietaire")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $repo = $doctrine->getRepository(Proprietaire::class);
        $proprietaires = $repo->findAll();

        return $this->render('proprietaire/index.html.twig', [
            'proprietaires' => $proprietaires,
        ]);
    }

/**
 * @Route("/proprietaire/ajouter", name="app_proprietaire_ajouter")
 */
public function ajouter(ManagerRegistry $doctrine, Request $request): Response
{
    $proprietaire = new Proprietaire();

    $form = $this->createForm(ProprietaireType::class, $proprietaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em = $doctrine->getManager();
        $em->persist($proprietaire);
        $em->flush();

        //retour à l'accueil
        return $this->redirectToRoute("app_proprietaire");
    }

    return $this->render("proprietaire/ajouter.html.twig", [
        'formulaire' => $form->createView()
    ]);
}

/**
 * @Route("/proprietaire/modifier/{id}", name="app_proprietaire_modifier")
 */
public function modifier($id, ManagerRegistry $doctrine, Request $request): Response
{
    $proprietaire = $doctrine->getRepository(Proprietaire::class)->find($id);

    if(!$proprietaire){
        throw $this->createNotFoundException("Pas de propriétaire associé à cet identifiant");
    }

    $form = $this->createForm(ProprietaireType::class, $proprietaire);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){

        $em = $doctrine->getManager();

        $em->persist($proprietaire);

        $em->flush();
        return $this->redirectToRoute("app_proprietaire");
    }

    return $this->render("proprietaire/modifier.html.twig", [
        'proprietaire' =>$proprietaire,
        'formulaire' => $form->createView()
    ]);
}

/**
 * @Route("/proprietaire/supprimer/{id}", name="app_proprietaire_supprimer")
 */
public function supprimer($id, ManagerRegistry $doctrine, Request $request): Response{
    //créer le formulaire sur le même principe que dans ajouter
    //mais avec une catégorie existante
    $proprietaire = $doctrine->getRepository(Proprietaire::class)->find($id);

    //je vais gérer le fait que l'id n'existe pas
    if (!$proprietaire){
        throw $this->createNotFoundException("Pas de propriétaire avec l'id $id");
    }

    //Si j'arrive là c'est qu'elle existe en BDD
    //à partir de ça je crée le formulaire
    $form=$this->createForm(ProprietaireSupprimerType::class, $proprietaire);

    //On gère le retour du formulaire tout de suite
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        //l'objet catégorie est rempli
        //on va utiliser l'entity manager de doctrine
        $em=$doctrine->getManager();
        //on lui dit qu'on supprimer la catégorie
        $em->remove($proprietaire);

        //on génère l'appel SQL (update ici)
        $em->flush();

        //on revient à l'accueil
        return $this->redirectToRoute("app_proprietaire");
    }

    return $this->render("proprietaire/supprimer.html.twig",[
        "proprietaire"=>$proprietaire,
        "formulaire"=>$form->createView()
    ]);
}
}
