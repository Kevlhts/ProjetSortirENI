<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('pseudo')
            ->add('campus', EntityType::class,[
                        'class'=>Campus::class,
                        'choice_label'=>'nom',
                        'required'=>false,
            ])
            ->add('brochure',FileType::class, [
                'label'=>'Brochure (Fichier png)',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                       'maxSize'=> '1024k',
                        'mimeTypes' => ['image/png'],
                        'mimeTypesMessage'=> 'Téléchargez un fichier PNG valide']
                    )
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
//                    new NotBlank([
//                        'message' => 'Veuillez saisir votre mot de passe',
//                    ]),
                    new Length([
                        'min' => 8,
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new Regex ([
                        'pattern'=> '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial'
                    ])

                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }



}
