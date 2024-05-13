<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Add this import statement
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile; // Add this import statement

class ProductUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('image',FileType::class,[
                'mapped'=>false,
                'required'=>false,
                "constraints"=>[
                    new ConstraintsFile([
                        'maxSize'=>'2048k',
                        'mimeTypes'=>[
                            'image/*'
                        ],
                        'maxSizeMessage'=>'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ maxSize }} {{ suffix }}',
                        'mimeTypesMessage'=>'Please upload a valid image file'
                    ])]])
            //->add('stock')
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
'choice_label' => 'id',
'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
