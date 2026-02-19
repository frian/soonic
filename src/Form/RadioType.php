<?php

namespace App\Form;

use App\Entity\Radio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RadioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'trim' => true,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('streamUrl', UrlType::class, [
                'trim' => true,
                'empty_data' => '',
                'default_protocol' => 'https',
                'attr' => [
                    'maxlength' => 512,
                    'placeholder' => 'https://stream.example.tld/live',
                ],
            ])
            ->add('homepageUrl', UrlType::class, [
                'required' => false,
                'trim' => true,
                'empty_data' => '',
                'default_protocol' => 'https',
                'attr' => [
                    'maxlength' => 512,
                    'placeholder' => 'https://www.example.tld',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Radio::class,
        ]);
    }
}
