<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Netrunnerdb\CardsBundle\Entity\Rulings;
use Netrunnerdb\CardsBundle\Entity\Cards;
use Netrunnerdb\CardsBundle\Entity\Rxc;
use Netrunnerdb\CardsBundle\Form\RulingsType;

/**
 * Rulings controller.
 *
 */
class RulingsController extends Controller
{
    /**
     * Lists all Rulings entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NetrunnerdbCardsBundle:Rulings')->findAll();

        return $this->render('NetrunnerdbCardsBundle:Rulings:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Rulings entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rulings')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rulings entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Rulings:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Rulings entity.
     *
     */
    public function newAction()
    {
        $entity = new Rulings();
        $form   = $this->createForm(new RulingsType(), $entity);

        return $this->render('NetrunnerdbCardsBundle:Rulings:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Rulings entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Rulings();
        $form = $this->createForm(new RulingsType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);

            $em->flush();
			/* add rxc */
			$indexkeys = $request->request->get('indexkeys') ?: '';
			$indexkeys = explode(',',$indexkeys);
			foreach($indexkeys as $indexkey)
			{
				$card = $em->getRepository('NetrunnerdbCardsBundle:Cards')->findOneBy(array("indexkey" => $indexkey));
				if($card)
				{
					$rxc = new Rxc();
					$rxc->setIdrulings($entity->getId());
					$rxc->setIdcards($card->getId());
					$em->persist($rxc);
				}
			}
            $em->flush();


            return $this->redirect($this->generateUrl('admin_rulings_show', array('id' => $entity->getId())));
        }

        return $this->render('NetrunnerdbCardsBundle:Rulings:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Rulings entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rulings')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rulings entity.');
        }

        $editForm = $this->createForm(new RulingsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NetrunnerdbCardsBundle:Rulings:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Rulings entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NetrunnerdbCardsBundle:Rulings')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Rulings entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new RulingsType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_rulings_edit', array('id' => $id)));
        }

        return $this->render('NetrunnerdbCardsBundle:Rulings:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Rulings entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NetrunnerdbCardsBundle:Rulings')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Rulings entity.');
            }

			$list = $em->getRepository('NetrunnerdbCardsBundle:Rxc')->findBy(array("idrulings" => $id));
			foreach($list as $rxc)
			{
				$em->remove($rxc);
			}

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_rulings'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
