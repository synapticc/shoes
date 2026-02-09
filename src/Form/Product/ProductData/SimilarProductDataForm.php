<?php

// src/Form/Product/ProductData/SimilarProductDataForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductData\SimilarProductData;
use App\Repository\Product\ProductData\ProductDataRepository as PDRepo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as Checkbox;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as Choice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as Integer;
use Symfony\Component\Form\Extension\Core\Type\NumberType as Number;
use Symfony\Component\Form\FormBuilderInterface as Builder;
use Symfony\Component\HttpFoundation\RequestStack as Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimilarProductDataForm extends AbstractType
{
    use Attributes;

    public function __construct(private Request $r, private PDRepo $repo)
    {
    }

    public function buildForm(Builder $builder, array $options): void
    {
        $r = $this->r->getCurrentRequest();
        $route = $r->get('_route');
        $new = 'admin_product_data_new';
        $pr = 'product';
        $pd = 'pd';

        // Retrieve Product Object using the id
        $id = (int) $r->get($pr);
        $p = $this->repo->find($id);

        if (!empty($p)) {
            $pr = $p->getProduct();
            $sizeOptions = $this->getSizesByType($pr);
        } else {
            $sizeOptions = $this->sizeFull();
        }

        $builder
            ->add('qtySlider', Integer::class, [
                'required' => true,
            ])
            ->add('sameSize', Checkbox::class, [
                'required' => false, ])
            ->add('sizes', Choice::class, [
                'placeholder' => 'Choose size',
                'choices' => $sizeOptions,
                'multiple' => true,
                'attr' => ['data-label' => 'size'],
            ])
            ->add('qtySize', Number::class, [
                'required' => true,
            ])
            ->add('samePrice', Checkbox::class, [
                'required' => false, ])
            ->add('minPrice', Number::class, [
                'required' => true,
                'attr' => ['data-min-price' => ''],
            ])
            ->add('maxPrice', Number::class, [
                'required' => true,
                'attr' => ['data-max-price' => ''],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SimilarProductData::class,
        ]);
    }
}
