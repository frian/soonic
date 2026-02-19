<?php

namespace App\Form;

use App\Entity\Config;
use App\Entity\Language;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'select language',
                'required' => true,
                'query_builder' => static fn ($repository) => $repository->createQueryBuilder('l')->orderBy('l.name', 'ASC'),
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'select theme',
                'required' => true,
                'query_builder' => static fn ($repository) => $repository->createQueryBuilder('t')->orderBy('t.name', 'ASC'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
