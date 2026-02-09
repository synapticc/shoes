<?php

// src/Form/Search/SearchForm.php

namespace App\Form\Search;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Entity\Supplier\Supplier;
use App\Repository\Supplier\SupplierRepository as SupplierRepo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['maxlength' => 300],
            ])
            ->add('search_brands', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->brandSet(),
                'attr' => [],
            ])
            ->add('search_categories', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getCategorySet(),
                'attr' => [],
            ])
            ->add('search_occasions', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getOccasionSet(),
                'attr' => [],
            ])
            ->add('search_types', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getTypeSet(),
                'attr' => [],
            ])
            ->add('search_colors', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getColorSet(),
                'attr' => [],
            ])
            ->add('search_fabrics', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getFabricSet(),
                'attr' => [],
            ])
            ->add('search_textures', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getTextureSet(),
                'attr' => [],
            ])
            ->add('search_tags', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->getTagSet(),
                'attr' => [],
            ])
            ->add('_exact', CheckboxType::class, [
                'required' => false,
            ])
            ->add('_id', CheckboxType::class, [
                'required' => false,
            ])
            ->add('search_suppliers', EntityType::class, [
                'class' => Supplier::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => fn (SupplierRepo $er) => $er->createQueryBuilder('s')
                       ->orderBy('s.id', 'ASC'),
                'choice_label' => 'name',
                'attr' => [],
            ])
            ->add('search_country', CountryType::class, [
                'attr' => [],
                'required' => false,
                'placeholder' => 'Country',
            ])
            ->add('search_start_date', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'required' => false,
                'format' => 'yyyy-MM-dd HH:mm',

                /* Other date format */
                // 'format' => 'dd/MM/yyyy',
                // 'format' => 'dd-MM-yyyy HH:mm',
                // 'date_format'=>"dd/MM/yyyy hh:mm",
            ])
            ->add('search_end_date', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'required' => false,
                'format' => 'yyyy-MM-dd HH:mm',

                /* Other date format */
                // 'format' => 'dd/MM/yyyy',
                // 'format' => 'dd-MM-yyyy HH:mm',
                // 'date_format'=>"dd/MM/yyyy hh:mm",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
