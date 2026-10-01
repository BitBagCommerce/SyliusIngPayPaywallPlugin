<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Form\Type;

use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class IngPayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'environment',
                ChoiceType::class,
                [
                'choices' => [
                    'bitbag.ing_pay_plugin.configuration.production' => IngPayApiInterface::PRODUCTION_ENVIRONMENT,
                    'bitbag.ing_pay_plugin.configuration.sandbox' => IngPayApiInterface::SANDBOX_ENVIRONMENT,
                ],
                'label' => 'bitbag.ing_pay_plugin.configuration.environment',
                ],
            )
            ->add('merchant_id', TextType::class, [
                'label' => 'bitbag.ing_pay_plugin.configuration.merchant_id',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'bitbag.ing_pay_plugin.configuration.merchant_id.not_blank',
                            'groups' => ['sylius'],
                        ],
                    ),
                ],
            ])
            ->add('service_id', TextType::class, [
                'label' => 'bitbag.ing_pay_plugin.configuration.service_id',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'bitbag.ing_pay_plugin.configuration.service_id.not_blank',
                            'groups' => ['sylius'],
                        ],
                    ),
                ],
            ])
            ->add('service_key', TextType::class, [
                'label' => 'bitbag.ing_pay_plugin.configuration.service_key',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'bitbag.ing_pay_plugin.configuration.service_key.not_blank',
                            'groups' => ['sylius'],
                        ],
                    ),
                ],
            ])
            ->add('authorization_token', TextType::class, [
                'label' => 'bitbag.ing_pay_plugin.configuration.authorization_token',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'bitbag.ing_pay_plugin.configuration.authorization_token.not_blank',
                            'groups' => ['sylius'],
                        ],
                    ),
                ],
            ]);
    }
}
