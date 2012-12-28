<?php

namespace Oro\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;
use Oro\Bundle\DataModelBundle\Model\Attribute\Type\AttributeTypeString;
use Oro\Bundle\DataModelBundle\Model\Attribute\Type\AttributeTypeList;
use Oro\Bundle\DataModelBundle\Model\Attribute\Type\AttributeTypeInteger;
use Oro\Bundle\DataModelBundle\Model\Attribute\Type\AttributeTypeText;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/attribute")
 */
class AttributeController extends Controller
{

    /**
     * Get product manager
     *
     * @return FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $attributes = $this->getProductManager()->getAttributeRepository()
            ->findBy(array('entityType' => $this->getProductManager()->getEntityShortname()));

        return array('attributes' => $attributes);
    }

    /**
     * @Route("/insert")
     *
     * @return multitype
     */
    public function insertAction()
    {
        $messages = array();

        // force in english
        $this->getProductManager()->setLocaleCode('en');

        // prepare attribute types
        $attTypeString = new AttributeTypeString();
        $attTypeText = new AttributeTypeText();
        $attTypeInteger = new AttributeTypeInteger();
        $attTypeList = new AttributeTypeList();

        // attribute name (if not exists)
        $attributeCode = 'name';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->getNewAttributeInstance();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Name');
            $attribute->setAttributeType($attTypeString);
            $attribute->setTranslatable(true);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute description (if not exists)
        $attributeCode = 'description';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->getNewAttributeInstance();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Description');
            $attribute->setAttributeType($attTypeText);
            $attribute->setTranslatable(true);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute size (if not exists)
        $attributeCode= 'size';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->getNewAttributeInstance();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Size');
            $attribute->setAttributeType($attTypeInteger);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute color (if not exists)
        $attributeCode= 'color';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->getNewAttributeInstance();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Color');
            $attribute->setAttributeType($attTypeList);
            $attribute->setTranslatable(false); // only one value but option can be translated in option values
            // add option and related value "Red", "Blue", "Green"
            $colors = array("Red", "Blue", "Green");
            foreach ($colors as $color) {
                $option = $this->getProductManager()->getNewAttributeOptionInstance();
                $optionValue = $this->getProductManager()->getNewAttributeOptionValueInstance();
                $optionValue->setValue($color);
                $option->addOptionValue($optionValue);
                $attribute->addOption($option);
            }
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        $this->getProductManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_attribute_index'));
    }

}
