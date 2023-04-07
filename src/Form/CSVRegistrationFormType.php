<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CSVRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csv', FileType::class, [
                'label'=>'Fichier CSV',
                'mapped'=>false,
//                'required'=>false,
                'constraints'=>[
                    new File(
                        maxSize: '1000k',
                        mimeTypes: ['text/csv','text/plain'],
                        mimeTypesMessage: 'Téléchargez un fichier CSV valide'

                    )
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
