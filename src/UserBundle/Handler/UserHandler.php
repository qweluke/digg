<?php
/**
 * Created by PhpStorm.
 * User: lmalicki
 * Date: 31.05.16
 * Time: 22:30
 */

namespace UserBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Entity\User;

class UserHandler implements UserHandlerInterface
{
    private $container;
    private $manager;

    public function __construct(ContainerInterface $container, $entityClass)
    {
        $this->container = $container;
        $this->manager = $this->container->get('fos_user.user_manager');
    }

    public function login($username, $password)
    {
        $code = 0;

        // check the arguments here.

        $user = $this->manager->findUserByUsername($username);

        if ($user === null) {
            $user = $this->manager->findUserByEmail($username);
        }

        if ($user === null) {
            $code = 224;

            return new JsonResponse([
                'code' => $code,
                'username' => $username
            ]);
        }

        // check the user password
        if ($this->checkUserPassword($user, $password) === false) {
            $code = 225;

            return new JsonResponse([
                'code' => $code,
                'username' => null
            ]);
        }

        // log the user
        $this->loginUser($user);


        return new JsonResponse([
            'success' => true,
            'username' => $user
        ]);

    }

    protected function loginUser(User $user)
    {
        $security = $this->container->get('security.token_storage');
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $roles = $user->getRoles();
        $token = new UsernamePasswordToken($user, null, $providerKey, $roles);
        $security->setToken($token);
    }

    protected function checkUserPassword(User $user, $password)
    {
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);

        if (!$encoder) {
            return false;
        }

        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }

}
