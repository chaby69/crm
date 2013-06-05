<?php

namespace OroCRM\Bundle\ContactBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactDatagridManager;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactAccountDatagridManager;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactAccountUpdateDatagridManager;

/**
 * @Acl(
 *      id="orocrm_contact",
 *      name="Contact manipulation",
 *      description="Contact manipulation",
 *      parent="root"
 * )
 */
class ContactController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_contact_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_contact_view",
     *      name="View Contact",
     *      description="View contact",
     *      parent="orocrm_contact"
     * )
     */
    public function viewAction(Contact $contact)
    {
        /** @var $accountDatagridManager ContactAccountDatagridManager */
        $accountDatagridManager = $this->get('orocrm_contact.account.view_datagrid_manager');
        $accountDatagridManager->setContact($contact);
        $accountDatagridManager->getRouteGenerator()->setRouteParameters(array('id' => $contact->getId()));

        $datagridView = $accountDatagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView,
            'contact'  => $contact,
        );
    }

    /**
     * Create contact form
     *
     * @Route("/create", name="orocrm_contact_create")
     * @Template("OroCRMContactBundle:Contact:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_create",
     *      name="Create Contact",
     *      description="Create contact",
     *      parent="orocrm_contact"
     * )
     */
    public function createAction()
    {
        /** @var Contact $contact */
        $contact = $this->getManager()->createEntity();
        return $this->updateAction($contact);
    }

    /**
     * Update user form
     *
     * @Route("/update/{id}", name="orocrm_contact_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_contact_update",
     *      name="Update Contact",
     *      description="Update contact",
     *      parent="orocrm_contact"
     * )
     */
    public function updateAction(Contact $entity)
    {
        $backUrl = $this->generateUrl('orocrm_contact_index');

        if ($this->get('orocrm_contact.form.handler.contact')->process($entity)) {
            $this->getFlashBag()->add('success', 'Contact successfully saved');
            return $this->redirect($backUrl);
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_contact.form.contact')->createView(),
            'datagrid' => $this->getAccountUpdateDatagridManager($entity)->getDatagrid()->createView(),
        );
    }

    /**
     * Get grid data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="orocrm_contact_update_accounts_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @AclAncestor("orocrm_contact_update")
     */
    public function gridDataAction(Contact $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        $datagridView = $this->getAccountUpdateDatagridManager($entity)->getDatagrid()->createView();

        return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
    }

    /**
     * @param Contact $contact
     * @return ContactAccountUpdateDatagridManager
     */
    protected function getAccountUpdateDatagridManager(Contact $contact)
    {
        /** @var $datagridManager ContactAccountUpdateDatagridManager */
        $datagridManager = $this->get('orocrm_contact.account.update_datagrid_manager');
        $datagridManager->setContact($contact);
        $datagridManager->getRouteGenerator()->setRouteParameters(array('id' => $contact->getId()));

        return $datagridManager;
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_contact_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="orocrm_contact_list",
     *      name="View List of Contacts",
     *      description="View list of contacts",
     *      parent="orocrm_contact"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ContactDatagridManager */
        $gridManager = $this->get('orocrm_contact.contact.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    protected function getManager()
    {
        return $this->get('orocrm_contact.contact.manager');
    }
}
