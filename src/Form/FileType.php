<?php

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType as SymfonyFileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as ConstraintFile;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('public')
            ->add('file', SymfonyFileType::class, [
               'mapped' => false,
               'required' => true,
               'constraints' => [
                   new ConstraintFile([
                       'maxSize' => '5M',
                       'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ]
                   ])
               ],
            ])
            ->add("Envoyer", SubmitType::class)
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
