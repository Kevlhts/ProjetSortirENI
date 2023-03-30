<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Etat;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('duree')
            ->add('dateLimiteInscription', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')
/*            ->add('etat', EntityType::class,
                [
                    'class' => Etat::class,
                    'choice_label' => 'libelle',
                ])*/

              ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
