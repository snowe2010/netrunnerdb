<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Netrunnerdb\CardsBundle\Entity\Rxc;
use Netrunnerdb\CardsBundle\Form\RxcType;

/**
 * Rxc controller.
 *
 */
class RxcController extends Controller
{
    /**
     * Lists all Rxc entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->findAll();

        return $this->render('NetrunnerdbCardsBundle:Rxc:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Rxc entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rxc entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Rxc:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Rxc entity.
     *
     */
    public function newAction()
    {
        $entity = new Rxc();
        $form   = $this->createForm(new RxcType(), $entity);

        return $this->render('NetrunnerdbCardsBundle:Rxc:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Rxc entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Rxc();
        $form = $this->createForm(new RxcType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_rxc_show', array('id' => $entity->getId())));
        }

        return $this->render('NetrunnerdbCardsBundle:Rxc:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Rxc entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rxc entity.');
        }

        $editForm = $this->createForm(new RxcType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Rxc:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Rxc entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rxc entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new RxcType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_rxc_edit', array('id' => $id)));
        }

        return $this->render('NetrunnerdbCardsBundle:Rxc:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Rxc entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Rxc entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_rxc'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
