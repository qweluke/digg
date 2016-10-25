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
    private $om;
    private $entityClass;
    private $repository;
    private $container;
    private $manager;

    public function __construct(ObjectManager $om, Container $container, $entityClass)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->container = $container;
        $this->manager = $this->container->get('fos_user.user_manager');
    }

    public function login($username, $password)
    {
        $jsonErrorCreator = $this->container->get('myproject_api.create_error_json');
        $code = 0;

        // check the arguments here.

        $user = $this->manager->findUserByUsername($username);

        if ($user === null) {
            $user = $this->manager->findUserByEmail($username);
        }

        if ($user === null) {
            $code = 224;

            return ($jsonErrorCreator->createErrorJson($code, $username));
        }

        // check the user password
        if ($this->checkUserPassword($user, $password) === false) {
            $code = 225;

            return ($jsonErrorCreator->createErrorJson($code, null));
        }

        // log the user
        $this->loginUser($user);

        $jsonCreator = $this->container->get('myproject_api.create_json');
        $response = $jsonCreator->createJson(array('success' => true, 'user' => $user));

        return $response;
    }

    protected function loginUser(User $user)
    {
        $security = $this->container->get('security.context');
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