<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Services\CompanyProfileService;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function store(Request $request, string $companyId)
    {
        $data = $request->validate([
            'profile_type' => 'required|string|in:customer_profile,market_insights,positioning,domain_extract,competitive_analysis',
            'source_type' => 'required|string|in:ai_from_user_input,ai_from_domain,ai_mixed',
            'raw_text' => 'nullable|string',
            'ai_extracted_json' => 'nullable|array',
        ]);

        $profile = CompanyProfile::create(array_merge($data, [
            'company_id' => $companyId,
        ]));

        return response()->json($profile, 201);
    }

    public function index(Request $request, string $companyId)
    {
        $type = $request->query('type');
        $query = CompanyProfile::where('company_id', $companyId);
        if ($type) {
            $query->where('profile_type', $type);
        }
        $profiles = $query->orderBy('created_at', 'desc')->get();
        return response()->json($profiles);
    }

    public function prioritized(Request $request, string $companyId)
    {
        $type = $request->query('type');
        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData($companyId, $type);
        return response()->json($data);
    }
}
