<?php
namespace App\Form;


use App\Entity\ConsumptionImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumptionImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'file',
                FileType::class,
                array(
                    'label' => ' ',
                    'attr' => array(
                        'placeholder'=> 'form.import.placeholder'
                    ),
                )
            )
            ->add('upload', SubmitType::class, array(
                'label' => 'btn.import'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ConsumptionImport::class,
        ));
    }
}