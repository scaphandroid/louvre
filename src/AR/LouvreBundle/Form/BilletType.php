<?php

namespace AR\LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BilletType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('pays', CountryType::class, array(
                'preferred_choices' => array('FR')
            ))
            ->add('dateNaissance', DateType::class, array(
                'days' => range(1,31),
                'months' => range(1, 12),
                'years' => range(1902, date('Y')),
                'format' => 'dd-MM-yyyy'
            ))
            ->add('tarifReduit', CheckboxType::class, array(
                'label'    => 'Tarif réduit ?',
                'required' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AR\LouvreBundle\Entity\Billet'
        ));
    }
}
