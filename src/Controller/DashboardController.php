<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\Parameters;
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\User;
use App\Form\PlayerAddType;
use App\Form\PlayerBuyType;
use App\Form\PlayerEditType;
use App\Form\ProfilePasswordChangeType;
use App\Form\ProfilePicChangeType;
use App\Form\SettingsType;
use App\Form\TeamEditType;
use App\Form\TeamType;
use App\Repository\OperationRepository;
use App\Repository\ParametersRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\Utils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DashboardController extends AbstractController
{

    private $siteParameters = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        ParametersRepository $paramRepo,
        private SluggerInterface $slugger,
    ) {
        $this->siteParameters = Utils::getParametersReorganised($paramRepo->findAll());
    }
    public function myRender(string $view, array $parameters = [], ?Response $response = null)
    {

        $parameters['currentUser'] = $this->getUser();
        $parameters['siteOptions'] = $this->siteParameters;
        $parameters['uploadPath'] = $this->getParameter('upload_directory');
        return $this->render($view, $parameters);
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(OperationRepository $operationRepo, PlayerRepository $playerRepo, TeamRepository $teamRepo): Response
    {
        $totalPlayers = $playerRepo->getTotalPlayers();
        $totalTeams = $teamRepo->getTotalTeams();
        $todaysTransactions = $operationRepo->getTotalOperationDetailsPerDate(Utils::getDebitOperationName());
        $allTimeTransactions = $operationRepo->getTotalOperationDetails(Utils::getDebitOperationName());

        $rawTeamsData = $teamRepo->getAllTeamsForTeamsPage();
        $beautifyTeamsData = Utils::getReorganisedTeamPageData($rawTeamsData);
        $playersData = Utils::getPlayersDataFromReorganisedTeamData($beautifyTeamsData, 3);
        return $this->myRender('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'todaysGeneratedRevenue' => $todaysTransactions['total_amount'],
            'todaysTransactionsNbr' => $todaysTransactions['total_nbr'],
            'totalPlayers' => $totalPlayers,
            'totalTeams' => $totalTeams,
            'allTimeGeneratedRevenue' => $allTimeTransactions['total_amount'],
            'allTimeTransactionsNbr' => $allTimeTransactions['total_nbr'],
            'teams' => $beautifyTeamsData,
            'players' => $playersData
        ]);
    }


    #[Route('/players/add', name: 'app_player_add')]
    public function playersAddPage(Request $request, PlayerRepository $playerRepo, TeamRepository $team): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerAddType::class, $player);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('notice', 'Please, fill out the form correctly');
                //return $this->redirectToRoute('app_player_add');
            } else {
                $playersTeam = $team->findOneBy(['id' => $form->get('team')->getData()]);
                if (!$playersTeam) {
                    $this->addFlash('notice', 'Please, choose a valid team for the player');
                    return $this->redirectToRoute('app_player_add');
                }
                $player->setTeam($playersTeam);
                $player->setUser($this->getUser());
                $player->setCreatedAt(new DateTimeImmutable());
                //PHOTO UPLOAD
                if ($form->has('photoFile') && $form->get('photoFile')->getData()) {
                    $playerPhoto = $form->get('photoFile')->getData();
                    $player->setPhoto($this->uploadFile($playerPhoto, 'app_player_add'));
                }
                $playerRepo->save($player, true);
                $this->addFlash('notice', 'Player ' . $player->getSurname() . ' created successfully');
                //return $this->redirectToRoute('app_player_add');
                return $this->redirectToRoute('app_player_edit', ['id' => $player->getId()]);
            }
        }

        return $this->myRender('dashboard/player/add.html.twig', [
            'controller_name' => 'DashboardController',
            'playerForm' => $form->createView()
        ]);
    }

    #[Route('/players/edit/{id}', name: 'app_player_edit', methods: ['GET', 'POST'])]
    public function playersEditPage(Request $request, Player $player, PlayerRepository $playerRepo): Response
    {
        $form = $this->createForm(PlayerEditType::class, $player);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('notice', 'Please, fill out the form correctly');
                //return $this->redirectToRoute('app_player_edit', ['id'=>$player->getId()]);
            } else {
                //PHOTO UPLOAD
                if ($form->has('photoFile') && $form->get('photoFile')->getData()) {
                    $playerPhoto = $form->get('photoFile')->getData();
                    $player->setPhoto($this->uploadFile(
                        $playerPhoto,
                        'app_player_add',
                        ['id' => $player->getId()]
                    ));
                }
                $playerRepo->save($player, true);
                $this->addFlash('notice', 'Player ' . $player->getSurname() . ' updated successfully');
                return $this->redirectToRoute('app_player_edit', ['id' => $player->getId()]);
                //return $this->redirectToRoute('app_player_edit', ['id'=>$player->getId()]);
            }
        }

        return $this->myRender('dashboard/player/edit.html.twig', [
            'controller_name' => 'DashboardController',
            'playerForm' => $form->createView(),
            'playerData' => $player
        ]);
    }

    #[Route('/players/buy-sell/{id}', name: 'app_player_transfer')]
    public function buyPlayerPage(
        Request $request,
        Player $player,
        PlayerRepository $playerRepo,
        TeamRepository $teamRepo,
        EntityManagerInterface $entityManager,
        OperationRepository $operationRepo
    ): Response {
        $otherTeams = $teamRepo->findExcept($player->getTeam()->getId());
        $form = $this->createForm(PlayerBuyType::class, [
            'teamsSelectChoices' => Utils::getTeamsForSelect($otherTeams)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('notice', 'Please fill out the form correctly');
                //return $this->redirectToRoute('app_player_transfer', ['id'=> $player->getId()] );
            } else {
                $newPlayersTeam = $teamRepo->findOneBy(['id' => $form->get('team')->getData()]);
                if (!$newPlayersTeam || $player->getTeam()->getId() == $newPlayersTeam->getId()) {
                    $this->addFlash('notice', "Error in targeting new player's team. Try again!");
                    return $this->redirectToRoute('app_player_transfer', ['id' => $player->getId()]);
                }
                $amount = $form->get('amount')->getData();
                $newTeamBalance = $operationRepo->getTeamBalance($newPlayersTeam->getId());
                if ((float) $amount > (float) $newTeamBalance) {
                    $this->addFlash('notice', "Team to transfer player to, doesn't have enough balance for the transaction!");
                    return $this->redirectToRoute('app_player_transfer', ['id' => $player->getId()]);
                }
                $oldPlayersTeam = $player->getTeam();
                $description = $form->get('description')->getData();
                //CREDIT OPERATION
                $operation = new Operation();
                $operation->setUser($this->getUser());
                $operation->setPlayer($player);
                $operation->setTeam($oldPlayersTeam);
                $operation->setAmount($amount);
                $operation->setDescription($description);
                $operation->setCreatedAt(new DateTimeImmutable());
                $operation->setName(Utils::getCreditOprationName());
                $entityManager->persist($operation);
                //DEBIT OPERATION
                $player->setTeam($newPlayersTeam);
                $operation = new Operation();
                $operation->setUser($this->getUser());
                $operation->setPlayer($player);
                $operation->setTeam($newPlayersTeam);
                $operation->setAmount($amount);
                $operation->setDescription($description);
                $operation->setCreatedAt(new DateTimeImmutable());
                $operation->setName(Utils::getDebitOperationName());
                $entityManager->persist($operation);
                $entityManager->flush();
                //UPDATING PLAYER
                $playerRepo->save($player, true);

                $this->addFlash('notice', "Player " .
                    $player->getSurname() . " transfered successfully to " . $newPlayersTeam->getName());
                return $this->redirectToRoute('app_team');
            }
        }
        return $this->myRender('dashboard/player/buy.html.twig', [
            'controller_name' => 'DashboardController',
            'playerForm' => $form->createView(),
            'playerData' => $player
        ]);
    }


    #[Route('/teams', name: 'app_team', methods: ['GET', 'POST'])]
    public function teamListPage(TeamRepository $teamRepo): Response
    {
        $rawTeamsData = $teamRepo->getAllTeamsForTeamsPage();
        $beautifyTeamsData = Utils::getReorganisedTeamPageData($rawTeamsData);
        return $this->myRender('dashboard/team/index.html.twig', [
            'controller_name' => 'DashboardController',
            'teams' => $beautifyTeamsData
        ]);
    }

    #[Route('/teams/add', name: 'app_team_add')]
    public function addTeamPage(Request $request, TeamRepository $teamRepo, OperationRepository $operationRepo): Response
    {
        $form = $this->createForm(TeamType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('notice', 'Fill out the form correctly');
            } else {
                $teamName = $form->get('name')->getData();
                $team = new Team();
                $team->setName($teamName);
                $team->setDescription($form->get('description')->getData());
                $team->setCountry($form->get('country')->getData());
                $team->setCreatedAt(new DateTimeImmutable());
                $team->setUser($this->getUser());
                //LOGO UPLOAD
                if ($form->has('logoFile') && $form->get('logoFile')->getData()) {
                    $teamLogo = $form->get('logoFile')->getData();
                    $team->setLogo($this->uploadFile($teamLogo, 'app_team_add'));
                }
                $teamRepo->save($team, true);
                $operation = new Operation();
                $operation->setAmount($form->get('balance')->getData());
                $operation->setTeam($team);
                $operation->setUser($this->getUser());
                $operation->setName(Utils::getCreditOprationName());
                $operation->setCreatedAt(new DateTimeImmutable());
                $operation->setDescription(Utils::getInitialDepositDescription());
                $operationRepo->save($operation, true);
                $this->addFlash('notice', 'Team ' . $teamName . ' added successfully');
                //return $this->redirectToRoute('app_team_add');
                return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
            }
        }

        return $this->myRender('dashboard/team/add.html.twig', [
            'controller_name' => 'DashboardController',
            'teamForm' => $form->createView()
        ]);
    }



    #[Route('/teams/edit/{id}', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, TeamRepository $teamRepo, OperationRepository $operation): Response
    {
        $form = $this->createForm(TeamEditType::class, $team);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('notice', 'Fill out the form correctly');
                //return  $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
            } else {
                $teamName = $form->get('name')->getData();
                $team->setName($teamName);
                $team->setDescription($form->get('description')->getData());
                $team->setCountry($form->get('country')->getData());
                //LOGO UPLOAD
                if ($form->has('logoFile') && $form->get('logoFile')->getData()) {
                    $teamLogo = $form->get('logoFile')->getData();
                    $team->setLogo($this->uploadFile($teamLogo, 'app_team_edit', ['id' => $team->getId()]));
                }
                $teamRepo->save($team, true);
                $this->addFlash('notice', 'Team ' . $teamName . ' updated successfully');
                return $this->redirectToRoute('app_team_edit', ['id' => $team->getId()]);
            }
        }

        return $this->myRender('dashboard/team/edit.html.twig', [
            'controller_name' => 'DashboardController',
            'teamForm' => $form->createView(),
            //'isEdit'=>true,
            'teamData' => $team,
            'teamBalance' => $operation->getTeamBalance($team->getId())
        ]);
    }


    #[Route('/settings', name: 'app_settings')]
    public function settings(
        Request $request,
        ParametersRepository $paramRepo,
        EntityManagerInterface $entityManager
    ): Response {
        $settingsForm = $this->createForm(SettingsType::class, $this->siteParameters);
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted()) {
            if (!$settingsForm->isValid()) {
                //dd($errors);
                $this->addFlash('notice', 'Fill out the form correctly');
                //return $this->redirectToRoute('app_settings');
            } else {
                $options = [
                    'siteName' => $settingsForm->get('siteName')->getData(),
                    'siteDescription' => $settingsForm->get('siteDescription')->getData(),
                    'siteCurrency' => $settingsForm->get('siteCurrency')->getData(),
                    'siteLogo' => $settingsForm->get('siteLogo')->getData(),
                ];
                //UPLOAD LOGO
                if ($settingsForm->has('siteLogoFile') && $settingsForm->get('siteLogoFile')->getData()) {
                    $siteLogo = $settingsForm->get('siteLogoFile')->getData();
                    $options["siteLogo"] = $this->uploadFile($siteLogo, 'app_settings');
                }
                $paramRepo->deleteAll();
                $entityManager->flush();
                foreach ($options as $key => $option) {
                    $singleParam = new Parameters();
                    $singleParam->setLabel($key);
                    $singleParam->setValue($option);
                    $entityManager->persist($singleParam);
                }
                $entityManager->flush();
                $this->addFlash('notice', "Site parameters updated successfully");
                return $this->redirectToRoute('app_settings');
            }
        }
        return $this->myRender('dashboard/settings.html.twig', [
            'controller_name' => 'DashboardController',
            'settingsForm' => $settingsForm->createView(),
        ]);
    }


    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepo,
        SluggerInterface $slugger
    ): Response {
        $passwordForm = $this->createForm(ProfilePasswordChangeType::class);
        $pictureForm = $this->createForm(ProfilePicChangeType::class);
        $passwordForm->handleRequest($request);
        $pictureForm->handleRequest($request);

        if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $this->addFlash('notice', 'Please fill out the form correctly');
                //return $this->redirectToRoute('app_profile');
            } else {
                $currentPassword = $passwordForm->get('currentPassword')->getData();
                $user = $this->getUser();
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    //INCORRECT PASSWORD
                    $this->addFlash('notice', 'Your current password is incorrect');
                    return $this->redirectToRoute('app_profile');
                }
                $newPassword = $passwordForm->get('newPassword')->getData();
                $hashedNewPassword = $passwordHasher->hashPassword(
                    $user,
                    $newPassword
                );
                $user->setPassword($hashedNewPassword);
                $userRepo->save($user, true);
                $this->addFlash('notice', 'Password changed successfully');
                return $this->redirectToRoute('app_profile');
                //if($passwordForm->has('currentPassword') && $passwordForm->has('newPassword')){
            }
        }
        if ($pictureForm->isSubmitted()) {
            if (!$pictureForm->isValid()) {
                $this->addFlash('notice', 'Ooops. Something went wrong. Try again');
                //return $this->redirectToRoute('app_profile');
            } else {
                $pictureFile = $pictureForm->get('photo')->getData();
                $newFilename = $this->uploadFile($pictureFile, 'app_profile');
                $user = $this->getUser();
                $user->setPhoto($newFilename);
                $userRepo->save($user, true);
                $this->addFlash('notice', "User Profile Pic updated successfully");
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->myRender('dashboard/profile.html.twig', [
            'controller_name' => 'DashboardController',
            'profileForm' => $passwordForm->createView(),
            'pictureForm' => $pictureForm->createView(),
            'extensions' => Utils::getUploadImageExtensions(),
            'maxFileSize' => Utils::getUploadMaxSize()
        ]);
    }

    public function uploadFile($fileToUpload, string $redirectRoute, array $redirectArray = [])
    {
        $originalFilename = pathinfo($fileToUpload->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        //$slugger = new SluggerInterface();
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $fileToUpload->guessExtension();
        try {
            $fileToUpload->move(
                $this->getParameter('upload_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            $this->addFlash('notice', "Ooops. Something went wrong. Try again");
            return $this->redirectToRoute($redirectRoute, $redirectArray);
            // ... handle exception if something happens during file upload
        }
        return $newFilename;
    }
}
