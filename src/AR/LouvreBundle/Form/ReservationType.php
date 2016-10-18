<?php

namespace AR\LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ReservationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //la gestion de l'email se fait à l'étape suivante via ResaMailType
            ->add('dateresa', DateType::class, array(
                'widget'=>'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy'
            ))
            ->add('demijournee', ChoiceType::class, array(
                'choices' => array(
                    'journée' => false,
                    'demi-journée' => true
                )
            ))
            ->add('nbBillets', IntegerType::class, array('attr' => array(
                'min' => '1',
                'max' => '20'
            )))
            ->add('Suivant', SubmitType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AR\LouvreBundle\Entity\Reservation'
        ));
    }
}
