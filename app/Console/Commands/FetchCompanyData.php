<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanyWebFetchService;
use Illuminate\Console\Command;

class FetchCompanyData extends Command
{
    protected $signature = 'company:fetch 
                            {company_id? : UUID of the company to fetch}
                            {--name= : Search and fetch by company name (creates new if not exists)}
                            {--all : Fetch all companies with missing domain data}
                            {--force : Force refresh even if data exists}';

    protected $description = 'Fetch public web data for a company (website, LinkedIn, Kununu)';

    public function handle(CompanyWebFetchService $fetchService): int
    {
        if ($this->option('all')) {
            return $this->fetchAllCompanies($fetchService);
        }

        if ($name = $this->option('name')) {
            return $this->fetchByName($fetchService, $name);
        }

        if ($companyId = $this->argument('company_id')) {
            return $this->fetchById($fetchService, $companyId);
        }

        $this->error('Please provide a company_id, --name="Company Name", or --all');
        return Command::FAILURE;
    }

    private function fetchById(CompanyWebFetchService $fetchService, string $companyId): int
    {
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Company not found: {$companyId}");
            return Command::FAILURE;
        }

        return $this->fetchCompany($fetchService, $company);
    }

    private function fetchByName(CompanyWebFetchService $fetchService, string $name): int
    {
        $this->info("Searching for website of: {$name}");
        
        // First just search without creating
        $website = $fetchService->findCompanyWebsite($name);
        
        if ($website) {
            $this->info("Found website: {$website}");
        } else {
            $this->warn("No website found");
        }

        // Check if company exists
        $company = Company::where('name', 'like', "%{$name}%")->first();
        
        if (!$company) {
            if (!$this->confirm("Company not in database. Create a new one?")) {
                return Command::SUCCESS;
            }
            
            // Need a user - just show what we found
            $this->warn("Cannot create company without owner_user_id.");
            $this->info("Run this after creating a company with the website: {$website}");
            return Command::SUCCESS;
        }

        // Update website if found
        if ($website && empty($company->website)) {
            $company->update(['website' => $website]);
            $this->info("Updated company website");
        }

        return $this->fetchCompany($fetchService, $company);
    }

    private function fetchAllCompanies(CompanyWebFetchService $fetchService): int
    {
        $companies = Company::whereNotNull('name')
            ->where('name', '!=', '')
            ->get();

        $this->info("Fetching data for {$companies->count()} companies...");
        
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($companies as $company) {
            try {
                $fetchService->fetchAll($company, $this->option('force'));
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->warn("Failed: {$company->name} - {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Success: {$success}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    private function fetchCompany(CompanyWebFetchService $fetchService, Company $company): int
    {
        $this->info("Fetching data for: {$company->name}");
        
        $this->output->write('  Website... ');
        $results = $fetchService->fetchAll($company, $this->option('force'));
        $this->info('Done!');

        // Display results
        $this->newLine();
        $this->table(
            ['Source', 'Status', 'Details'],
            $this->formatResults($results)
        );

        // Show extracted data
        if (!empty($results['sources']['website']['extracted'])) {
            $this->newLine();
            $this->info('📋 Extracted from Impressum:');
            foreach ($results['sources']['website']['extracted'] as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
        }

        if (!empty($results['sources']['linkedin']['company_size'])) {
            $this->newLine();
            $this->info('💼 LinkedIn Data:');
            foreach (['company_size', 'headquarters', 'industry', 'founded', 'followers'] as $field) {
                if (!empty($results['sources']['linkedin'][$field])) {
                    $this->line("  {$field}: {$results['sources']['linkedin'][$field]}");
                }
            }
        }

        return Command::SUCCESS;
    }

    private function formatResults(array $results): array
    {
        $rows = [];

        // Website
        $website = $results['sources']['website'] ?? null;
        if ($website) {
            $pageCount = count($website['pages'] ?? []);
            $rows[] = ['Website', '✅', "{$pageCount} pages fetched"];
        } else {
            $rows[] = ['Website', '❌', 'Not found'];
        }

        // LinkedIn
        $linkedin = $results['sources']['linkedin'] ?? [];
        if (($linkedin['status'] ?? '') === 'success') {
            $details = $linkedin['company_size'] ?? 'Data fetched';
            $rows[] = ['LinkedIn', '✅', $details];
        } else {
            $rows[] = ['LinkedIn', '❌', $linkedin['status'] ?? 'Not found'];
        }

        // Kununu
        $kununu = $results['sources']['kununu'] ?? [];
        if (($kununu['status'] ?? '') === 'success') {
            $rating = isset($kununu['rating']) ? "Rating: {$kununu['rating']}/5" : 'Data fetched';
            $rows[] = ['Kununu', '✅', $rating];
        } else {
            $rows[] = ['Kununu', '❌', $kununu['status'] ?? 'Not found'];
        }

        return $rows;
    }
}
