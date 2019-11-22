<?php

namespace App\Form;

use App\Entity\Attribut;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('key')
            ->add('value')
            ->add('dateCreate')
            ->add('dateUpdate')
            ->add('platform')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attribut::class,
        ]);
    }
}
