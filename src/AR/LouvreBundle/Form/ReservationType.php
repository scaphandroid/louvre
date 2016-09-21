<?php

namespace AR\LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            //TODO gestion de l'email à l'étape suivante->add('email', EmailType::class)
            ->add('dateresa', DateType::class, array(
                'widget'=>'single_text','input' => 'datetime', 'format' => 'dd/MM/yyyy'
            ))
            ->add('demijournee', ChoiceType::class, array(
                'choices' => array(
                    'journée' => false,
                    'demi-journée' => true
                )
            ))
            ->add('nbBillets', IntegerType::class)
            ->add('save', SubmitType::class)
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
