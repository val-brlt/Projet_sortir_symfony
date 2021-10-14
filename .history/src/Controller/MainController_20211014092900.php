<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\MonProfilType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateTime;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class MainController extends AbstractController
{

    /**
     * @Route("/main/profil/{id}", name="profil", requirements={"id":"\d+"})
     */
    public function profil(ParticipantRepository $repo,$id=0): Response
    {
        $participant = $repo->find($id);
        return $this->render('main/profil.html.twig', [
            'participant' => $participant,
        ]);
    }


    /**
     * @Route("/main", name="main")
     */
    public function index(Request $request, SortieRepository $sortieRepo, SiteRepository $siteRepo): Response
    {
        $sorties = $sortieRepo->findAll();
        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/main/creationProfil", name="app_creationProfil")
     */
    public function addForm(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response //entity manager ..... des données
    {
        // instanciation de la classe produit
        $profil = new Participant();
        // la creation du formulaire
        $form = $this->createForm(MonProfilType::class, $profil);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {
            // génerer sql insert into et ajouter dans queue

            $profil->setIsAdmin(false);
            $profil->setIsActif(false);
            $profil->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $profil,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($profil);
            // appliquer insert into dans la bdd
            $em->flush();
            // redirect vers la liste wish
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'le titre   ' . $profil->getMonprofil() . ' a été ajoute');
            //redirection pour eviter un ajout en double en cas de réactualisation de la plage par l'utilisateur
            $id = $profil->getId();
            return $this->redirectToRoute("app_monProfil", array('id' => $id = $profil->getId()));
        }
        $titre = "Sortir.com - Mon Profil";
        $formProfil = $form->createView();
        $tab = compact("titre", "formProfil");

        return $this->render('main/creationProfil.html.twig',$tab);
                }

    /**
     *@Route("/main/annuler",name="app_Annuler")
     */
    public function Annuler(Request $request): Response
    {
        return $this->render("main/base.html.twig");
    }

    /**
     *@Route("/search/{id}",name="app_search")
     */
    public function search(Request $request, SortieRepository $sortieRepo, SiteRepository $siteRepo, $id): Response
    {
        /* BARRE DE RECHERCHE CONTROLLER */
        /* 1/ Recuperer données de la barre de recherche */
        $search = $request->request->get('search');
        $dateDebut = $request->request->get('dateDebut');
        $dateLimiteInscription = $request->request->get('dateFin');
        $isSortiesOrganisateur = $request->request->get('isSortiesOrganisateur');
        $isSortiesInscrit = $request->request->get('isSortiesInscrit');
        $isSortiesNonInscrit = $request->request->get('isSortiesPasInscrit');
        $isSortiesPassees =  $request->request->get('isSortiesPassees');
        $idCurrentUser = $id;
        $idSite = $request->request->get('site');

        $sorties = [];

        /* 2/ Vérifier données vides ou non  et remplir un tableau en conséquence*/
        //  Retourne un tableau de sorties selon la recherche effectuée dans la searchbar et le site indiquée
        if (
            $search != "" && $isSortiesOrganisateur == null && $isSortiesInscrit == null && $isSortiesNonInscrit == null
            && $isSortiesPassees == null && $dateDebut == "" && $dateLimiteInscription == ""
        ) {
            $sorties = array_merge($sorties, $sortieRepo->findBySearchAndSite($search, $idSite, $search)); // NE RIEN METTRE = findAll()
        }

        // Retourne un tableau selon les dates rentrées et le site et site
        if ($dateDebut != "" && $dateLimiteInscription != "") {
            $sorties = array_merge($sorties, $sortieRepo->findByDates($dateDebut, $dateLimiteInscription, $idSite, $search));
        }

        // Retourne un tableau selon si l'user current est l'organisateur/trice et le site
        if ($isSortiesOrganisateur != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByIdOrganisateur($idCurrentUser, $idSite, $search));
        }

        // Retourne un tableau selon l'inscription de l'user current et le site
        if ($isSortiesInscrit != null) {
            $sorties =  array_merge($sorties, $sortieRepo->findByIdParticipantInscrit($idCurrentUser, $idSite, $search));
        }

        // Retourne un tableau selon la non inscription de l'user current et le site
        if ($isSortiesNonInscrit != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByIdParticipantNonInscrit($sortieRepo->findAll(), $idCurrentUser, $idSite));
        }


        // Retourne un tableau des sorties passées
        if ($isSortiesPassees != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByEtatPassees($idSite, $search));
        }



        /* 3/ Supprimer les doublons du tableau de sorties */
        $sorties = array_unique($sorties, SORT_REGULAR);

        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

     /**
     * @Route("/monProfil/{id}",name="app_modifier")
     */
    public function modifier(EntityManagerInterface $em, ParticipantRepository $repo,UserPasswordHasherInterface $userPasswordHasherInterface, $id): Response {

        // instanciation de la classe produit
        $participant = new Participant();
        // la creation du formulaire
        $form = $this->createForm(MonProfilType::class, $participant);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {
            // génerer sql insert into et ajouter dans queue
            $participant->setIsAdmin(false);
            $participant->setIsActif(false);
            $participant->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );

            // appliquer insert into dans la bdd
            $em->flush();
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'le titre   ' . $profil->getMonprofil() . ' a été modifié');


            

        }
        $participant = $repo->find($id);
        // TODO : attribuer les élements de l'update
        /*$participant ->setPseudo();
        $participant->setPrenom();
        $participant->setNom();
        $participant->setTelephone();
        $participant->setEmail();
        $participant->setPassword();*/
        // TODO : faire verif password
        //$participant->setIdSite();

        $em -> flush();
        return $this->render("main/modifProfil.html.twig");
    }

}
