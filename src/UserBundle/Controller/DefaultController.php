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
 * @Route("/api/user")
 */
class DefaultController extends Controller
{

    /**
     * Array of user entities.
     *
     * @Rest\Get("/{_format}", defaults = { "_format" = "json" })
     * @Rest\View(serializerGroups={"user","mod","admin"})
     *
     * @ApiDoc(
     *  resource="/api/user/",
     *  description="Returns contents",
     *
     *  output={
     *   "class"="UserBundle\Entity\User",
     *   "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *   "groups"={"user","mod","admin"}
     *  }
     * )
     * @return View
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('UserBundle:User')->findAll();

        $view = View::create()
            ->setStatusCode(Codes::HTTP_OK)
            ->setTemplate("UserBundle:Default:index.html.twig")
            ->setTemplateVar('users')
            ->setSerializationContext(SerializationContext::create()->setGroups(['user']))
            ->setData($users);

        return $this->get('fos_rest.view_handler')->handle($view);


    }

    /**
     * Creates a new User entity.
     *
     * @Rest\Post("/new/{_format}", defaults = { "_format" = "json" })
     * @Rest\View(serializerGroups={"user","mod","admin"})
     *
     * @ApiDoc(
     *  resource="/api/content/",
     *  description="Creates new content",
     *
     *  input={
     *     "class"="UserBundle\Form\UserType",
     *  },
     *
     *  output={
     *   "class"="UserBundle\Entity\User",
     *   "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *   "groups"={"user","mod","admin"}
     *  }
     * )
     * @return View
     */
    public function newAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm('UserBundle\Form\UserType', $user);
        $form->handleRequest($request);

        $view = View::create()
            ->setSerializationContext(SerializationContext::create()->setGroups(['user']));

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $view
                ->setStatusCode(Codes::HTTP_OK)
                ->setTemplate("UserBundle:Default:show.html.twig")
                ->setTemplateVar('user')
                ->setData($user);

        } else {
            $view
                ->setStatusCode(Codes::HTTP_BAD_REQUEST)
                ->setTemplateVar('error')
                ->setData($form)
                ->setTemplateData(['message' => $form->getErrors(true)])
                ->setTemplate('UserBundle:Default:new.html.twig');
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * @Rest\Get(
     *     "/{user}.{_format}",
     *     requirements={"user" = "\d+"},
     *     defaults = { "_format" = "json" }
     * )
     * @Rest\View(serializerGroups={"user","mod","admin"})
     * @param User $user
     * @return View
     * @throws \NotFoundHttpException
     *
     * @ApiDoc(
     *  resource="/api/user/",
     *  description="Returns user information",
     *
     *  output={
     *   "class"="UserBundle\Entity\User",
     *   "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *   "groups"={"user","mod","admin"}
     *  }
     * )
     */
    public function showAction(User $user)
    {
        if ($user) {
            $view = View::create()
                ->setStatusCode(Codes::HTTP_OK)
                ->setSerializationContext(SerializationContext::create()->setGroups(['user']))
                ->setTemplate("UserBundle:Default:show.html.twig")
                ->setTemplateVar('user')
                ->setData($user);

            return $this->get('fos_rest.view_handler')->handle($view);
        }

        throw new \NotFoundHttpException();

    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Rest\Patch(
     *     "/{user}.{_format}",
     *     requirements={"user" = "\d+"},
     *     defaults = { "_format" = "json" }
     * )
     *
     * @Rest\View(serializerGroups={"admin"})
     * @param User $user
     * @return View
     * @throws \NotFoundHttpException*
     *
     * @ApiDoc(
     *  resource="/api/content/",
     *  description="Updates content data",
     *
     *  input="UserBundle\Form\UserType",
     *
     *  output={
     *   "class"="UserBundle\Entity\User",
     *   "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *   "groups"={"admin"}
     *  }
     * )
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('UserBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('api_user_edit', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a User entity.
     *
     * @Rest\Delete(
     *     "/{user}.{_format}",
     *     requirements={"user" = "\d+"},
     *     defaults = { "_format" = "json" }
     * )
     *
     * @Rest\View(serializerGroups={"user","mod","admin"})
     * @param User $user
     * @return View
     * @throws \NotFoundHttpException*
     *
     * @ApiDoc(
     *  resource="/api/content/",
     *  description="Deletes content"
     * )
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->submit($request->request->get($form->getName()));

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();

            return View::create()
                ->setStatusCode(Codes::HTTP_OK)
                ->setSerializationContext(SerializationContext::create()->setGroups(['user']))
                ->setData(['status'=>true]);
        }

        return View::create()
            ->setStatusCode(Codes::HTTP_BAD_REQUEST)
            ->setSerializationContext(SerializationContext::create()->setGroups(['user']))
            ->setData($form);
    }

    /**
     * Creates a form to delete a User entity.
     *
     * @param User $user The User entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('api_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
