<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Livewire\Forms;

use App\Tenant\GovernanceContext\LandingBuilderModule\DTOs\LandingContentData;
use Livewire\Form;

final class LandingBuilderForm extends Form {
   public string $siteName = '';
   public string $cta = '';
   public string $primaryColor = '#2563eb';
   public string $status = 'draft';

   /** @return array<string, list<string>> */
   public function rules(): array {
      return [
         'siteName' => ['required', 'string', 'max:120'],
         'cta' => ['required', 'string', 'max:60'],
         'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
         'status' => ['required', 'in:draft,published'],
      ];
   }

   public function toData(): LandingContentData {
      return LandingContentData::fromArray([
         'site_name' => trim($this->siteName),
         'default_cta' => trim($this->cta),
         'primary_color' => trim($this->primaryColor),
         'status' => $this->status,
      ]);
   }
}
