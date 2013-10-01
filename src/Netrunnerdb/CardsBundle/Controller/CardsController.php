<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Netrunnerdb\CardsBundle\Entity\Cards;
use Netrunnerdb\CardsBundle\Form\CardsType;

/**
 * Cards controller.
 *
 */
class CardsController extends Controller
{
    /**
     * Lists all Cards entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NetrunnerdbCardsBundle:Cards')->findAll();

        return $this->render('NetrunnerdbCardsBundle:Cards:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Cards entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Cards')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cards entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Cards:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Cards entity.
     *
     */
    public function newAction()
    {
        $entity = new Cards();
        $form   = $this->createForm(new CardsType(), $entity);

        return $this->render('NetrunnerdbCardsBundle:Cards:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Cards entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Cards();
        $form = $this->createForm(new CardsType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_cards_show', array('id' => $entity->getId())));
        }

        return $this->render('NetrunnerdbCardsBundle:Cards:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Cards entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Cards')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cards entity.');
        }

        $editForm = $this->createForm(new CardsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Cards:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Cards entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Cards')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cards entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new CardsType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_cards_edit', array('id' => $id)));
        }

        return $this->render('NetrunnerdbCardsBundle:Cards:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Cards entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NetrunnerdbCardsBundle:Cards')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Cards entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_cards'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
