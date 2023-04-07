<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\AdminRegistrationFormType;
use App\Form\CampusType;
use App\Form\CSVRegistrationFormType;
use App\Form\ParticipantType;

use App\Form\VillesCollectionType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Container7MuBGso\getDoctrine_UlidGeneratorService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {

        return $this->render('admin/administration.html.twig', [

        ]);
    }
    #[Route('/admin/villes', name: 'admin_villes')]
    public function villes(EntityManagerInterface $entityManager,
                        Request $request, VilleRepository $villeRepository): Response
    {
        $ville = new Ville();
        $villes = $villeRepository->findALl();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
            return $this->redirectToRoute('admin_villes');
        }




        return $this->render('admin/villes.html.twig', [
            'villes' => $villes,
            'form' => $villeForm
        ]);
    }


    #[Route('/admin/villes/supprimer/{id}', name: 'admin_villes_suppr')]
    public function villeSupprimer(EntityManagerInterface $entityManager,
                           Request $request, VilleRepository $villeRepository, Ville $id): Response
    {
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('admin_villes');
    }


    #[Route('/admin/campus', name: 'admin_campus')]
    public function campus(EntityManagerInterface $entityManager, CampusRepository $campusRepository, Request $request): Response
    {
        $campus = $campusRepository->findAll();
        $nouveauCampus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $nouveauCampus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($nouveauCampus);
            $entityManager->flush();
            return $this->redirectToRoute('admin_campus');
        }


        return $this->render('admin/campus.html.twig', [
            'campus' => $campus,
            'form'=> $campusForm,
        ]);
    }

    #[Route('/admin/campus/supprimer/{id}', name: 'admin_campus_suppr')]
    public function campusSupprimer(EntityManagerInterface $entityManager,
                                   Request $request, CampusRepository $campusRepository, Campus $id): Response
    {
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('admin_campus');
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(EntityManagerInterface $entityManager,
                                 Request $request, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->findAll();

        return $this->render('admin/utilisateurs.html.twig', [
            'participants'=>$participant,
        ]);
    }

    #[Route('/admin/utilisateurs/detail/{id}', name: 'admin_utilisateurs_detail')]
    public function utilisateursModifier(EntityManagerInterface $entityManager, Participant $id,
                                         Request $request, ParticipantRepository $participantRepository): Response
    {
        $participant = new Participant();
        $participant = $participantRepository->find($id);



        return $this->render('admin/detail.html.twig', [

        'participant'=>$participant
        ]);
    }

    #[Route('/admin/utilisateur/supprimer/{id}', name: 'admin_util_suppr')]
    public function utilisateurSupprimer(EntityManagerInterface $entityManager,
                                    Participant $id): Response
    {
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/admin/utilisateur/ajouter}', name: 'admin_util_ajout')]
    public function utilisateurAjouter(EntityManagerInterface $entityManager,
                                        UserPasswordHasherInterface $userPasswordHasher, Request $request): Response
    {
        $messageErreur = [];
        $listeUserInsert = [];
        $user = new Participant();
        $form = $this->createForm(AdminRegistrationFormType::class,$user);
        $form2 = $this->createForm(CSVRegistrationFormType::class);


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('admin_utilisateurs');
        }


        $form2->handleRequest($request);
        if ($form2->isSubmitted() && $form2->isValid()) {

            $csv = $form2->get('csv')->getData();
            $row = 2;
            if (($handle = fopen($csv, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $user2 = new Participant();
                    if(filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                        $user2->setEmail($data[0]);
                    }
                    $user2->setNom($data[1]);
                    $user2->setPrenom($data[2]);
                    $user2->setPseudo($data[3]);
                    if(preg_match('/^\d{10}$/', $data[4])){
                        $user2->setTelephone($data[4]);
                    }

                    try{

                        $entityManager->persist($user2);
                        $entityManager->flush();
                        $listeUserInsert[] = $user2;
                    }catch (\Exception $exception){
                        $messageErreur[]='Erreur à la ligne '.$row;
                    }



                    $row++;

                }
                fclose($handle);
            }
            return $this->render('admin/resultatInsertion.html.twig',[
                'listeUser'=>$listeUserInsert,
                'erreurs'=>$messageErreur,
            ]);
        }

        return $this->render('admin/ajoutUser.html.twig',[
            'form'=>$form,
            'form2'=>$form2,
        ]);
    }


    #[Route('/admin/utilisateur/sorties/{id}', name: 'admin_util_sorties')]
    public function adminUtilisateurSorties(EntityManagerInterface $entityManager,
                                         Participant $id, SortieRepository $sortieRepository): Response
    {


        $sorties = $sortieRepository->findBy(["organisateur" => $id]);

        return $this->render('admin/sortie_utilisateur.html.twig', compact('sorties')

        );
    }

    #[Route('/admin/utilisateur/disable/{id}', name: 'admin_util_disable')]
    public function adminUtilisateurDisable(EntityManagerInterface $entityManager,
                                            Participant $id): Response
    {



        $id->setRoles(["ROLE_INACTIF"]);
        $entityManager->persist($id);
        $entityManager->flush();
        return $this->redirectToRoute('admin_utilisateurs');


    }

    #[Route('/admin/utilisateur/enable/{id}', name: 'admin_util_enable')]
    public function adminUtilisateurEnable(EntityManagerInterface $entityManager,
                                            Participant $id): Response
    {



        $id->setRoles([]);
        $entityManager->persist($id);
        $entityManager->flush();
        return $this->redirectToRoute('admin_utilisateurs');


    }
}
