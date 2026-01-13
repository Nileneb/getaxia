<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyProfile;

class CompanyProfileService
{
    public function getCompanyProfileData(string $companyId, string $profileType): ?array
    {
        $company = Company::find($companyId);
        if (!$company) {
            return null;
        }

        // Prefer user-provided fields based on profileType mapping
        $userField = $this->mapUserField($profileType);
        if ($userField && !empty($company->{$userField})) {
            return [
                'source' => 'user',
                'raw_text' => is_string($company->{$userField}) ? $company->{$userField} : null,
                'ai_extracted_json' => is_array($company->{$userField}) ? $company->{$userField} : null,
            ];
        }

        // AI from user input
        $profile = CompanyProfile::where('company_id', $companyId)
            ->where('profile_type', $profileType)
            ->where('source_type', 'ai_from_user_input')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($profile) {
            return [
                'source' => 'ai_from_user_input',
                'raw_text' => $profile->raw_text,
                'ai_extracted_json' => $profile->ai_extracted_json,
            ];
        }

        // AI from domain
        $profile = CompanyProfile::where('company_id', $companyId)
            ->where('profile_type', $profileType)
            ->where('source_type', 'ai_from_domain')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($profile) {
            return [
                'source' => 'ai_from_domain',
                'raw_text' => $profile->raw_text,
                'ai_extracted_json' => $profile->ai_extracted_json,
            ];
        }

        return null;
    }

    private function mapUserField(string $profileType): ?string
    {
        return match ($profileType) {
            'customer_profile' => 'user_description',
            'market_insights' => 'user_market_info',
            'positioning' => 'user_positioning',
            'competitive_analysis' => 'user_competitive_notes',
            default => null,
        };
    }
}
