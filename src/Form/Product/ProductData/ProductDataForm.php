<?php

// src/Form/Product/ProductData/ProductDataForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductData\ProductData;
use App\Form\Supplier\SupplierDataForm;
use App\Repository\Product\ProductColor\ProductColorRepository as pdcRepo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDataForm extends AbstractType
{
    use Attributes;

    public function __construct(private RequestStack $r, private pdcRepo $pcRepo)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
          $this->r : Request
          $p : Product
          $c : Category
          $t : Type
          $pd : ProductData
          $cp : Cost price
          $sp : Selling price
        */
        $route = $this->r->getCurrentRequest()->get('_route');
        $pd = $builder->getData();

        /* Calculate profit */
        $profit = '';
        if ('admin_product_data_edit' == $route) {
            $cp = $pd->getCostPrice();
            $sp = $pd->getSellingPrice();

            if ($cp > 0 && $sp > 0) {
                $profit = (float) number_format((($sp - $cp) / $cp) * 100, 2);
            }
        }

        $product = $builder->getData()->getProduct();
        $sizeOptions = $this->getSizesByType($product);

        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'required' => true,
                // 'query_builder' => function (ProductRepository $er) {
                //     return $er->createQueryBuilder('u')
                //         ->orderBy('u.id', 'ASC');
                // },
                // 'choice_label' => 'name',
            ])
            ->add('supplier', SupplierDataForm::class, [
                'label' => true,
            ])
            ->add('color', EntityType::class, [
                'class' => ProductColor::class,
                'placeholder' => 'Choose color',
                // 'mapped' => false,
                'required' => true,
                // 'choices' => $this->pdcRepo->fetchByProduct($product->getId())],
                // pass parameters into querybuilder [ use ($product) ]
                'query_builder' => fn (pdcRepo $er) => $er->createQueryBuilder('c')
                          ->andWhere('c.product IN (:product)')
                          ->setParameter('product', $product)
                          ->orderBy('c.id', 'ASC'),
                // 'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('size', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Choose size',
                'choices' => $sizeOptions,
                'choice_attr' => function ($choice, $key) {
                    return ['data-value' => $choice, 'data-label' => $key];
                },
                'attr' => ['data-label' => 'size'],
            ])

            ->add('profit', PercentType::class, [
                'required' => false,
                'mapped' => false,
                'symbol' => false,
                'attr' => [
                    'value' => $profit,
                ],
            ])
            ->add('costPrice', NumberType::class, [
                'label' => 'Cost price: ',
                'required' => true,
            ])
            ->add('sellingPrice', NumberType::class, [
                'label' => 'Price displayed: ',
                'required' => true,
            ])
            ->add('qtyInStock', IntegerType::class, [
                'label' => 'Quantity (in stock)',
                'required' => true,
            ])
            ->add('productDataOrder', ProductDataOrderForm::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('similarProductData', SimilarProductDataForm::class, [
                'required' => false,
            ])
            ->add('new', SubmitType::class)
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductData::class,
        ]);
    }
}
