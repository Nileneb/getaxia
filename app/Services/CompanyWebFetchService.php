<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service to fetch company information from public web sources.
 * 
 * This runs algorithmically without LLM calls - just web scraping and regex extraction.
 * The raw texts are stored for later LLM analysis.
 */
class CompanyWebFetchService
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const TIMEOUT = 15;
    private const MAX_CONTENT_LENGTH = 100000; // 100KB per page

    /**
     * Fetch all available public data for a company.
     * 
     * @param Company $company
     * @param bool $forceRefresh Skip cache and re-fetch
     * @return array Summary of what was fetched
     */
    public function fetchAll(Company $company, bool $forceRefresh = false): array
    {
        $results = [
            'company_id' => $company->id,
            'company_name' => $company->name,
            'fetched_at' => now()->toIso8601String(),
            'sources' => [],
        ];

        // 1. Search for main website if not set
        if (empty($company->website)) {
            $websiteResult = $this->findCompanyWebsite($company->name);
            if ($websiteResult) {
                $company->update(['website' => $websiteResult]);
            }
        }

        // 2. Fetch main website + subpages
        if ($company->website) {
            $results['sources']['website'] = $this->fetchWebsite($company);
        }

        // 3. Search and fetch LinkedIn
        $results['sources']['linkedin'] = $this->fetchLinkedIn($company);

        // 4. Search and fetch Kununu (employer reviews)
        $results['sources']['kununu'] = $this->fetchKununu($company);

        // 5. Store aggregated raw text as CompanyProfile
        $this->storeAsProfile($company, $results);

        return $results;
    }

    /**
     * Find company website via DuckDuckGo search.
     */
    public function findCompanyWebsite(string $companyName): ?string
    {
        $searchResults = $this->duckDuckGoSearch($companyName);
        
        if (empty($searchResults)) {
            return null;
        }

        // Find the most likely company website (not linkedin, kununu, etc.)
        $excludeDomains = ['linkedin.com', 'kununu.com', 'xing.com', 'facebook.com', 'twitter.com', 'instagram.com', 'youtube.com', 'wikipedia.org', 'bundesanzeiger.de'];
        
        foreach ($searchResults as $result) {
            $url = $result['url'] ?? '';
            $domain = parse_url($url, PHP_URL_HOST) ?? '';
            
            $isExcluded = false;
            foreach ($excludeDomains as $excluded) {
                if (Str::contains($domain, $excluded)) {
                    $isExcluded = true;
                    break;
                }
            }
            
            if (!$isExcluded && !empty($url)) {
                // Normalize to base domain
                return rtrim(parse_url($url, PHP_URL_SCHEME) . '://' . $domain, '/');
            }
        }

        return null;
    }

    /**
     * Fetch company website and key subpages.
     */
    private function fetchWebsite(Company $company): array
    {
        $baseUrl = rtrim($company->website, '/');
        $results = [
            'base_url' => $baseUrl,
            'pages' => [],
        ];

        // Pages to fetch
        $pagesToFetch = [
            'homepage' => '',
            'about' => ['/about', '/about-us', '/ueber-uns', '/unternehmen', '/company'],
            'team' => ['/team', '/about/team', '/ueber-uns/team', '/management'],
            'impressum' => ['/impressum', '/imprint', '/legal'],
            'contact' => ['/contact', '/kontakt'],
            'products' => ['/products', '/produkte', '/services', '/leistungen'],
        ];

        foreach ($pagesToFetch as $pageType => $paths) {
            if ($pageType === 'homepage') {
                $content = $this->fetchUrl($baseUrl);
                if ($content) {
                    $results['pages']['homepage'] = [
                        'url' => $baseUrl,
                        'content' => $this->extractReadableText($content),
                        'meta' => $this->extractMetaTags($content),
                    ];
                }
                continue;
            }

            // Try multiple path variants
            foreach ((array)$paths as $path) {
                $url = $baseUrl . $path;
                $content = $this->fetchUrl($url);
                if ($content) {
                    $results['pages'][$pageType] = [
                        'url' => $url,
                        'content' => $this->extractReadableText($content),
                    ];
                    break; // Found one, stop trying variants
                }
            }
        }

        // Extract structured data from impressum
        if (isset($results['pages']['impressum'])) {
            $results['extracted'] = $this->extractImpressumData($results['pages']['impressum']['content'] ?? '');
        }

        return $results;
    }

    /**
     * Fetch LinkedIn company page.
     */
    private function fetchLinkedIn(Company $company): array
    {
        // Search for LinkedIn page
        $searchResults = $this->duckDuckGoSearch($company->name . ' site:linkedin.com/company');
        
        $linkedinUrl = null;
        foreach ($searchResults as $result) {
            if (Str::contains($result['url'] ?? '', 'linkedin.com/company')) {
                $linkedinUrl = $result['url'];
                break;
            }
        }

        if (!$linkedinUrl) {
            return ['status' => 'not_found'];
        }

        $content = $this->fetchUrl($linkedinUrl);
        if (!$content) {
            return ['status' => 'fetch_failed', 'url' => $linkedinUrl];
        }

        $text = $this->extractReadableText($content);
        
        // Extract structured data from LinkedIn
        $extracted = [
            'url' => $linkedinUrl,
            'raw_text' => $text,
        ];

        // Try to extract specific fields
        if (preg_match('/Company size\s*([0-9,]+(?:\+)?\s*employees)/i', $text, $m)) {
            $extracted['company_size'] = trim($m[1]);
        }
        if (preg_match('/Headquarters\s*([^\n]+)/i', $text, $m)) {
            $extracted['headquarters'] = trim($m[1]);
        }
        if (preg_match('/Industry\s*([^\n]+)/i', $text, $m)) {
            $extracted['industry'] = trim($m[1]);
        }
        if (preg_match('/Founded\s*(\d{4})/i', $text, $m)) {
            $extracted['founded'] = trim($m[1]);
        }
        if (preg_match('/Type\s*([^\n]+)/i', $text, $m)) {
            $extracted['company_type'] = trim($m[1]);
        }
        if (preg_match('/([\d,]+)\s*followers/i', $text, $m)) {
            $extracted['followers'] = str_replace(',', '', $m[1]);
        }

        return ['status' => 'success'] + $extracted;
    }

    /**
     * Fetch Kununu employer page.
     */
    private function fetchKununu(Company $company): array
    {
        // Search for Kununu page
        $searchResults = $this->duckDuckGoSearch($company->name . ' site:kununu.com');
        
        $kununuUrl = null;
        foreach ($searchResults as $result) {
            if (Str::contains($result['url'] ?? '', 'kununu.com')) {
                $kununuUrl = $result['url'];
                break;
            }
        }

        if (!$kununuUrl) {
            return ['status' => 'not_found'];
        }

        $content = $this->fetchUrl($kununuUrl);
        if (!$content) {
            return ['status' => 'fetch_failed', 'url' => $kununuUrl];
        }

        $text = $this->extractReadableText($content);
        
        $extracted = [
            'url' => $kununuUrl,
            'raw_text' => Str::limit($text, 10000),
        ];

        // Extract rating if present
        if (preg_match('/(\d[,\.]\d)\s*(?:von|out of)\s*5/i', $text, $m)) {
            $extracted['rating'] = str_replace(',', '.', $m[1]);
        }

        return ['status' => 'success'] + $extracted;
    }

    /**
     * Perform DuckDuckGo search.
     */
    private function duckDuckGoSearch(string $query, int $maxResults = 5): array
    {
        try {
            // DuckDuckGo HTML search (no API key needed)
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get('https://html.duckduckgo.com/html/', [
                    'q' => $query,
                ]);

            if (!$response->successful()) {
                Log::warning('DuckDuckGo search failed', ['query' => $query, 'status' => $response->status()]);
                return [];
            }

            $html = $response->body();
            $results = [];

            // Extract results from DuckDuckGo HTML
            // Results are in <a class="result__a" href="...">
            preg_match_all('/<a[^>]+class="result__a"[^>]+href="([^"]+)"[^>]*>([^<]+)</i', $html, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                if (count($results) >= $maxResults) break;
                
                $url = $match[1];
                // DuckDuckGo uses redirect URLs, extract actual URL
                if (preg_match('/uddg=([^&]+)/', $url, $uddg)) {
                    $url = urldecode($uddg[1]);
                }
                
                $results[] = [
                    'title' => html_entity_decode($match[2]),
                    'url' => $url,
                ];
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('DuckDuckGo search error', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch a URL and return HTML content.
     */
    private function fetchUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $content = $response->body();
            
            // Limit content size
            if (strlen($content) > self::MAX_CONTENT_LENGTH) {
                $content = substr($content, 0, self::MAX_CONTENT_LENGTH);
            }

            return $content;

        } catch (\Exception $e) {
            Log::debug('URL fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Extract readable text from HTML.
     */
    private function extractReadableText(string $html): string
    {
        // Remove scripts and styles
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<noscript[^>]*>.*?<\/noscript>/is', '', $html);
        
        // Remove HTML tags
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        
        return trim($text);
    }

    /**
     * Extract meta tags from HTML.
     */
    private function extractMetaTags(string $html): array
    {
        $meta = [];

        // Title
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            $meta['title'] = html_entity_decode(trim($m[1]));
        }

        // Meta description
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']|<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\']/i', $html, $m)) {
            $meta['description'] = html_entity_decode(trim($m[1] ?: $m[2]));
        }

        // OG tags
        preg_match_all('/<meta[^>]+property=["\']og:([^"\']+)["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $meta['og_' . $match[1]] = html_entity_decode($match[2]);
        }

        return $meta;
    }

    /**
     * Extract structured data from German Impressum.
     */
    private function extractImpressumData(string $text): array
    {
        $data = [];

        // Company name patterns
        if (preg_match('/(?:Firma|Unternehmen|Betreiber)[:\s]+([^\n]+)/i', $text, $m)) {
            $data['legal_name'] = trim($m[1]);
        }

        // Address
        if (preg_match('/(\d{5})\s+([A-Za-zäöüÄÖÜß\s\-]+)/u', $text, $m)) {
            $data['postal_code'] = $m[1];
            $data['city'] = trim($m[2]);
        }

        // Handelsregister
        if (preg_match('/(?:HRB?|Handelsregister)[:\s]*(\d+)/i', $text, $m)) {
            $data['hrb_number'] = $m[1];
        }
        if (preg_match('/(?:Amtsgericht|Registergericht)[:\s]+([^\n,]+)/i', $text, $m)) {
            $data['registry_court'] = trim($m[1]);
        }

        // USt-IdNr
        if (preg_match('/(?:USt-?Id(?:Nr)?|VAT)[:\s]*([A-Z]{2}\d+)/i', $text, $m)) {
            $data['vat_id'] = $m[1];
        }

        // Geschäftsführer
        if (preg_match('/(?:Geschäftsführ(?:er|ung)|CEO|Managing Director)[:\s]+([^\n]+)/i', $text, $m)) {
            $data['managing_director'] = trim($m[1]);
        }

        // Email
        if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $text, $m)) {
            $data['email'] = $m[1];
        }

        // Phone
        if (preg_match('/(?:Tel(?:efon)?|Phone)[:\s]*([+\d\s\-\/()]+)/i', $text, $m)) {
            $data['phone'] = trim($m[1]);
        }

        return $data;
    }

    /**
     * Store fetched data as CompanyProfile.
     */
    private function storeAsProfile(Company $company, array $fetchResults): void
    {
        // Aggregate all raw text
        $allText = [];
        
        if (isset($fetchResults['sources']['website']['pages'])) {
            foreach ($fetchResults['sources']['website']['pages'] as $pageType => $page) {
                if (!empty($page['content'])) {
                    $allText[] = "=== {$pageType} ({$page['url']}) ===\n" . $page['content'];
                }
            }
        }

        if (isset($fetchResults['sources']['linkedin']['raw_text'])) {
            $allText[] = "=== LinkedIn ===\n" . $fetchResults['sources']['linkedin']['raw_text'];
        }

        if (isset($fetchResults['sources']['kununu']['raw_text'])) {
            $allText[] = "=== Kununu ===\n" . $fetchResults['sources']['kununu']['raw_text'];
        }

        $rawText = implode("\n\n", $allText);

        // Build extracted JSON
        $extractedJson = [
            'fetched_at' => $fetchResults['fetched_at'],
            'website' => [
                'url' => $fetchResults['sources']['website']['base_url'] ?? null,
                'meta' => $fetchResults['sources']['website']['pages']['homepage']['meta'] ?? null,
                'impressum' => $fetchResults['sources']['website']['extracted'] ?? null,
            ],
            'linkedin' => array_filter([
                'url' => $fetchResults['sources']['linkedin']['url'] ?? null,
                'company_size' => $fetchResults['sources']['linkedin']['company_size'] ?? null,
                'headquarters' => $fetchResults['sources']['linkedin']['headquarters'] ?? null,
                'industry' => $fetchResults['sources']['linkedin']['industry'] ?? null,
                'founded' => $fetchResults['sources']['linkedin']['founded'] ?? null,
                'company_type' => $fetchResults['sources']['linkedin']['company_type'] ?? null,
                'followers' => $fetchResults['sources']['linkedin']['followers'] ?? null,
            ]),
            'kununu' => array_filter([
                'url' => $fetchResults['sources']['kununu']['url'] ?? null,
                'rating' => $fetchResults['sources']['kununu']['rating'] ?? null,
            ]),
        ];

        // Upsert profile
        CompanyProfile::updateOrCreate(
            [
                'company_id' => $company->id,
                'profile_type' => 'domain_extract',
                'source_type' => 'ai_from_domain',
            ],
            [
                'raw_text' => $rawText,
                'ai_extracted_json' => $extractedJson,
            ]
        );
    }
}
