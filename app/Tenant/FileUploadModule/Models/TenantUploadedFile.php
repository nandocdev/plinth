<?php

declare(strict_types=1);

namespace App\Tenant\FileUploadModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantUploadedFile extends Model {
   protected $table = 'tenant_uploaded_files';

   protected $fillable = [
      'tenant_id',
      'uploaded_by_user_id',
      'disk',
      'folder',
      'original_name',
      'stored_name',
      'stored_path',
      'mime_type',
      'size_bytes',
   ];
}
