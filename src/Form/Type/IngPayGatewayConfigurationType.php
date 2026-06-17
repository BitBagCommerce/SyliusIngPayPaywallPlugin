<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Form\Type;

use BitBag\SyliusIngPayPlugin\Enum\IngPayEnvironment;
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
                        'bitbag.ing_pay_plugin.configuration.production' => IngPayEnvironment::PRODUCTION_ENVIRONMENT,
                        'bitbag.ing_pay_plugin.configuration.sandbox' => IngPayEnvironment::SANDBOX_ENVIRONMENT,
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
            ])
        ;
    }
}
