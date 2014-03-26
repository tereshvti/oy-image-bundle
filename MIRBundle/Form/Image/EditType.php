<?php

namespace Olabs\MIRBundle\Form\Image;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('file', 'file', array('label' => 'File', 'required' => false));
    }

    public function getName()
    {
        return 'image_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Olabs\MIRBundle\Entity\Image',
                'csrf_protection' => false,
            )
        );
    }
}