<?php

namespace UserBundle\Controller;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;
use FOS\RestBundle\Util\Codes;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * User controller.
 *
 * @Route("/api/user/security")
 */
class SecurityController extends Controller
{

    /**
     * Array of user entities.
     *
     * @Rest\Post("/login.{_format}", defaults = {
     *     "username" = "1",
     *     "_format" = "json"
     * })
     * @Rest\View(serializerGroups={"user","mod","admin"})
     *
     * @ApiDoc(
     *  resource="/api/user/",
     *  description="Returns access token",
     *
     *  output={
     *   "class"="UserBundle\Entity\User",
     *   "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *   "groups"={"user","mod","admin"}
     *  }
     * )
     * @param Request $request
     * @return View
     */
    public function loginAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        return $this->get('myproject_user.user_service')->login($username, $password);
    }

}
