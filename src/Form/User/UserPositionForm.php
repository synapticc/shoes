<?php

// src/Form/User/UserPositionForm.php

namespace App\Form\User;

use App\Controller\_Utils\Attributes;
use App\Entity\User\UserPosition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPositionForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jobPosition', ChoiceType::class, [
                'choices' => $this->jobs(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserPosition::class,
        ]);
    }
}
