<?php

namespace App\Controller;

use App\Repository\ParametersRepository;
use App\Service\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{

    private $siteParameters = null;
    public function __construct(ParametersRepository $paramRepo,)
    {
        $this->siteParameters = Utils::getParametersReorganised($paramRepo->findAll());
        
    }
    public function myRender(string $view, array $parameters=[], ?Response $response = null){
        $parameters['currentUser']=$this->getUser();
        $parameters['uploadPath']=$this->getParameter('upload_directory');
        $parameters['siteOptions']=$this->siteParameters;
        return $this->render($view, $parameters);
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->myRender('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
