<?php

declare(strict_types=1);

return [
   'default_country' => env('CHECKOUT_DEFAULT_COUNTRY', 'US'),

   'latam_countries' => [
      'AR',
      'BO',
      'BR',
      'CL',
      'CO',
      'CR',
      'DO',
      'EC',
      'GT',
      'HN',
      'MX',
      'NI',
      'PA',
      'PE',
      'PY',
      'SV',
      'UY',
   ],

   'allowed_methods' => [
      'latam' => ['card', 'transfer', 'cash', 'wallet'],
      'global' => ['card', 'wallet'],
   ],

   'observed_method_order' => [
      'default' => ['card', 'wallet', 'transfer', 'cash'],
      'BR' => ['transfer', 'card', 'wallet', 'cash'],
      'AR' => ['wallet', 'card', 'cash', 'transfer'],
      'MX' => ['card', 'wallet', 'transfer', 'cash'],
   ],

   'method_catalog' => [
      'card' => [
         'label' => 'Tarjeta',
         'description' => 'Aprobacion inmediata en la mayoria de casos.',
         'provider_by_context' => [
            'latam' => 'dlocal',
            'global' => 'stripe',
         ],
         'manual_confirmation_required' => false,
         'status_message' => 'El pago se confirma automaticamente cuando el proveedor responde.',
      ],
      'wallet' => [
         'label' => 'Wallet',
         'description' => 'Pago express desde tu billetera digital.',
         'provider_by_context' => [
            'latam' => 'paypal',
            'global' => 'paypal',
         ],
         'manual_confirmation_required' => false,
         'status_message' => 'La confirmacion suele ser inmediata, pero puede tardar unos segundos.',
      ],
      'transfer' => [
         'label' => 'Transferencia',
         'description' => 'Puede requerir validacion adicional del banco.',
         'provider_by_context' => [
            'latam' => 'dlocal',
            'global' => 'dlocal',
         ],
         'manual_confirmation_required' => true,
         'status_message' => 'Tu pago puede quedar pendiente hasta que la transferencia sea validada.',
      ],
      'cash' => [
         'label' => 'Pago en efectivo',
         'description' => 'Genera un ticket para pago presencial.',
         'provider_by_context' => [
            'latam' => 'dlocal',
            'global' => 'dlocal',
         ],
         'manual_confirmation_required' => true,
         'status_message' => 'El estado pasara a pagado cuando recibamos la confirmacion del punto de cobro.',
      ],
   ],
];
