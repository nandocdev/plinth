<?php

declare(strict_types=1);

return [
   'queue' => env('TENANT_PROVISIONING_HOOKS_QUEUE', 'provisioning'),

   'hooks' => [
      'terraform' => [
         'driver' => 'terraform',
         'enabled' => env('TENANT_PROVISIONING_TERRAFORM_ENABLED', false),
         'timeout' => (int) env('TENANT_PROVISIONING_TERRAFORM_TIMEOUT', 900),
         'working_directory' => env('TENANT_PROVISIONING_TERRAFORM_WORKDIR'),
         'command' => [
            env('TENANT_PROVISIONING_TERRAFORM_BIN', 'terraform'),
            'apply',
            '-auto-approve',
            '-input=false',
            '-var=tenant_id={tenant_id}',
            '-var=tenant_domain={tenant_domain}',
            '-var=tenant_region={tenant_region}',
            '-var=tenant_database={tenant_database}',
         ],
         'environment' => [
            'TF_IN_AUTOMATION' => '1',
            'PLINTH_TENANT_ID' => '{tenant_id}',
            'PLINTH_TENANT_DOMAIN' => '{tenant_domain}',
            'PLINTH_TENANT_REGION' => '{tenant_region}',
            'PLINTH_TENANT_DATABASE' => '{tenant_database}',
            'PLINTH_TENANT_CONTEXT' => '{tenant_context_json}',
         ],
      ],
      'ansible' => [
         'driver' => 'ansible',
         'enabled' => env('TENANT_PROVISIONING_ANSIBLE_ENABLED', false),
         'timeout' => (int) env('TENANT_PROVISIONING_ANSIBLE_TIMEOUT', 900),
         'working_directory' => env('TENANT_PROVISIONING_ANSIBLE_WORKDIR'),
         'command' => [
            env('TENANT_PROVISIONING_ANSIBLE_BIN', 'ansible-playbook'),
            env('TENANT_PROVISIONING_ANSIBLE_PLAYBOOK', 'provision-tenant.yml'),
            '--extra-vars',
            'tenant_id={tenant_id} tenant_domain={tenant_domain} tenant_region={tenant_region} tenant_database={tenant_database}',
         ],
         'environment' => [
            'ANSIBLE_FORCE_COLOR' => 'false',
            'PLINTH_TENANT_ID' => '{tenant_id}',
            'PLINTH_TENANT_DOMAIN' => '{tenant_domain}',
            'PLINTH_TENANT_REGION' => '{tenant_region}',
            'PLINTH_TENANT_DATABASE' => '{tenant_database}',
            'PLINTH_TENANT_CONTEXT' => '{tenant_context_json}',
         ],
      ],
   ],
];
