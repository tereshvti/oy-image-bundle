<?php

namespace Olabs\MIRBundle\Form\Type\Info\Image;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Olabs\MIRBundle\Form\Image\EditType as ImageEditType;

class EditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
//        $builder->add('id', 'hidden', array('required' => true));
        $builder->add('image', new ImageEditType(), array('required' => false));
    }

    public function getName()
    {
        return 'image_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Olabs\MIRBundle\Entity\EntityImage',
                'csrf_protection' => false,
            )
        );
    }
}