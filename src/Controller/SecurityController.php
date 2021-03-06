<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Event\UserCreatedEvent;
use App\Handler\RegisterHandler;
use App\Form\Type\LostPasswordType;
use App\Form\Type\ResetPasswordType;
use App\Handler\LostPasswordHandler;
use App\Event\UserResetPasswordEvent;
use App\Form\Type\ChangePasswordType;
use App\Handler\ResetPasswordHandler;
use App\Handler\ChangePasswordHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 *
 * @author Mathieu GUILLEMINOT <guilleminotm@gmail.com>
 */
class SecurityController extends AbstractController
{
	/**
     * @param AuthenticationUtils $authUtils
     *
	 * @Route("/login", methods={"GET", "POST"}, name="security_login")
     *
	 * @return Response
	 */
	public function loginAction(AuthenticationUtils $authUtils): Response
	{
		$error = $authUtils->getLastAuthenticationError();

		$lastUsername = $authUtils->getLastUsername();

		return $this->render('Security/login.html.twig', array(
			'last_username' => $lastUsername,
			'error'			=> $error
		));
	}

	/**
	 * @param Request $request
	 * @param RegisterHandler $registerHandler
	 * @param EventDispatcherInterface $dispatcher
     *
	 * @Route("/register", methods={"GET", "POST"}, name="security_register")
     *
	 * @return Response
	 */
	public function registerAction(Request $request, RegisterHandler $registerHandler, EventDispatcherInterface $dispatcher): Response
	{
		$user = new User();
		$form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($registerHandler->handle($form, $user)) {
            $event = new UserCreatedEvent($user);
            $dispatcher->dispatch(UserCreatedEvent::NAME, $event);

            $this->addFlash('success', "Nous vous avons envoyé un email de confirmation. Vous devez valider votre compte pour vous connecter.");
            return $this->redirectToRoute('security_login');
        }

		return $this->render('Security/register.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @throws \Exception
     *
	 * @Route("/logout", name="security_logout")
	 */
	public function logout()
	{
		throw new \Exception('This should never be reached!');
	}

	/**
	 * @param string $token
     *
	 * @Route("/confirm/{token}", methods={"GET"}, name="security_confirm")
     *
	 * @return Response
	 */
	public function confirmAction(string $token)
	{
		$user = $this->getDoctrine()
			->getRepository(User::class)
			->findOneBy(['confirmationToken' => $token]);

		if(!$user)
			throw $this->createNotFoundException('Ce token n\'est pas valide');

		$em = $this->getDoctrine()->getManager();
		$user->setConfirmationToken(null);
		$user->setActive(true);
		$em->flush();

		$this->addFlash('success', "Votre compte est validé, vous pouvez vous connecter.");

		return $this->redirectToRoute('security_login');
	}

	/**
	 * @param Request $request
     * @param LostPasswordHandler $lostPasswordHandler
	 * @param EventDispatcherInterface $dispatcher
     *
	 * @Route("/lost-password", methods={"GET", "POST"}, name="security_lost_password")
     *
	 * @return Response
	 */
	public function lostPasswordAction(Request $request, LostPasswordHandler $lostPasswordHandler, EventDispatcherInterface $dispatcher)
	{
		$userForm = new User();
		$form = $this->createForm(LostPasswordType::class, $userForm)->handleRequest($request);

        $user = $lostPasswordHandler->handle($form, $userForm);
        if ($user instanceof User) {
            $event = new UserResetPasswordEvent($user);
            $dispatcher->dispatch(UserResetPasswordEvent::NAME, $event);

            $this->addFlash('success', "Un token de réinitialisation vous a été envoyé par mail");
			return $this->redirectToRoute('security_reset_password');
		}

		return $this->render('Security/lost-password.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @param Request $request
     * @param ResetPasswordHandler $resetPasswordHandler
     *
	 * @Route("/reset-password", methods={"GET", "POST"}, name="security_reset_password")
     *
	 * @return Response
	 */
	public function resetPasswordAction(Request $request, ResetPasswordHandler $resetPasswordHandler)
	{
		$user = new User();
		$form = $this->createForm(ResetPasswordType::class, $user)->handleRequest($request);

        if ($resetPasswordHandler->handle($form, $user)) {
            $this->addFlash('success', "Votre mot de passe a été changé, vous pouvez vous connecter.");
            return $this->redirectToRoute('security_login');
        }

		return $this->render('Security/lost-password.html.twig', array(
			'form' => $form->createView()
		));
	}

    /**
     * @param Request $request
     * @param ChangePasswordHandler $changePasswordHandler
     *
     * @Route("/change-password", methods={"GET", "POST"}, name="security_change_password")
     *
     * @return Response
     */
	public function changePasswordAction(Request $request, ChangePasswordHandler $changePasswordHandler)
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user)->handleRequest($request);

        if ($changePasswordHandler->handle($form, $user)) {
            $this->addFlash('success', "Votre mot de passe a bien été changé.");
            return $this->redirectToRoute('trick_list');
        }

        return $this->render('Security/change-password.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
