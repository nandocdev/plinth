<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;

/**
 * Regla arquitectural: ningun modelo dentro de app/Tenant/ puede definir
 * la propiedad $connection de forma explicita.
 *
 * Razon: stancl/tenancy conmuta automaticamente la conexion activa al
 * ejecutar tenancy()->initialize($tenant). Si un modelo hardcodea
 * $connection, la conmutacion no tiene efecto y el modelo consultara
 * la base de datos equivocada, provocando fuga de datos entre tenants
 * o errores de "tabla no encontrada".
 */
it('ningun modelo tenant define $connection explicitamente', function (): void {
   // Navegar hasta la raiz del proyecto sin depender de base_path()
   $basePath   = realpath(__DIR__ . '/../../../');
   $tenantPath = $basePath . '/app/Tenant';

   /** @var string[] $violators */
   $violators = [];

   $files = Finder::create()
      ->files()
      ->name('*.php')
      ->path('Models/')
      ->in($tenantPath);

   foreach ($files as $file) {
      // Derivar el FQCN a partir de la ruta del archivo
      $relativePath = ltrim(
         str_replace($basePath, '', $file->getRealPath()),
         DIRECTORY_SEPARATOR
      );

      // app/Tenant/SomeModule/Models/Foo.php -> App\Tenant\[Bundle]\SomeModule\Models\Foo
      $fqcn = str_replace(
         ['/', '.php'],
         ['\\', ''],
         ucfirst($relativePath)
      );

      if (! class_exists($fqcn)) {
         continue;
      }

      $reflection = new ReflectionClass($fqcn);

      // Solo nos interesan subclases de Eloquent Model
      if (! $reflection->isSubclassOf(Model::class)) {
         continue;
      }

      // Verificar si la propiedad 'connection' esta declarada en ESTA clase
      // (no heredada de Model o de otro padre)
      if ($reflection->hasProperty('connection')) {
         $declaring = $reflection->getProperty('connection')
            ->getDeclaringClass()
            ->getName();

         if ($declaring === $fqcn) {
            $violators[] = $fqcn;
         }
      }
   }

   expect($violators)->toBeEmpty(
      'Los siguientes modelos tenant definen $connection explicitamente, ' .
         'lo que anula el switching automatico de stancl/tenancy y puede causar ' .
         'fuga de datos entre tenants: ' . implode(', ', $violators)
   );
});

it('ningun modelo tenant sobreescribe getConnectionName sin delegar a tenancy', function (): void {
   $basePath   = realpath(__DIR__ . '/../../../');
   $tenantPath = $basePath . '/app/Tenant';

   /** @var string[] $violators */
   $violators = [];

   $files = Finder::create()
      ->files()
      ->name('*.php')
      ->path('Models/')
      ->in($tenantPath);

   foreach ($files as $file) {
      $relativePath = ltrim(
         str_replace($basePath, '', $file->getRealPath()),
         DIRECTORY_SEPARATOR
      );

      $fqcn = str_replace(
         ['/', '.php'],
         ['\\', ''],
         ucfirst($relativePath)
      );

      if (! class_exists($fqcn)) {
         continue;
      }

      $reflection = new ReflectionClass($fqcn);

      if (! $reflection->isSubclassOf(Model::class)) {
         continue;
      }

      if ($reflection->hasMethod('getConnectionName')) {
         $declaring = $reflection->getMethod('getConnectionName')
            ->getDeclaringClass()
            ->getName();

         // Si el metodo esta declarado en la propia clase tenant (no en Model)
         // es una violacion potencial de la regla de tenancy
         if ($declaring === $fqcn) {
            $violators[] = $fqcn;
         }
      }
   }

   expect($violators)->toBeEmpty(
      'Los siguientes modelos tenant sobreescriben getConnectionName(), ' .
         'lo que puede anular el switching automatico de stancl/tenancy: ' .
         implode(', ', $violators)
   );
});
