<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class AiResponseValidator
{
    /**
     * Validate todo analysis response
     */
    public function validateTodoAnalysis(array $response): bool
    {
        // Check required fields
        if (!isset($response['overall_score'], $response['evaluations'])) {
            throw ValidationException::withMessages([
                'response' => 'Missing required fields: overall_score or evaluations'
            ]);
        }

        // Validate overall score
        if ($response['overall_score'] < 0 || $response['overall_score'] > 100) {
            throw ValidationException::withMessages([
                'overall_score' => 'Score must be between 0 and 100'
            ]);
        }

        // Validate each evaluation
        foreach ($response['evaluations'] as $index => $eval) {
            $this->validateEvaluation($eval, $index);
        }

        return true;
    }

    /**
     * Validate single evaluation
     */
    protected function validateEvaluation(array $eval, int $index): void
    {
        $required = ['task_index', 'score', 'color', 'reasoning'];
        
        foreach ($required as $field) {
            if (!isset($eval[$field])) {
                throw ValidationException::withMessages([
                    "evaluation.$index.$field" => "Missing required field: $field"
                ]);
            }
        }

        // Validate score
        if ($eval['score'] < 0 || $eval['score'] > 100) {
            throw ValidationException::withMessages([
                "evaluation.$index.score" => 'Score must be between 0 and 100'
            ]);
        }

        // Validate color
        if (!in_array($eval['color'], ['green', 'yellow', 'orange'])) {
            throw ValidationException::withMessages([
                "evaluation.$index.color" => 'Color must be green, yellow, or orange'
            ]);
        }
    }

    /**
     * Validate company extraction response
     */
    public function validateCompanyExtraction(array $response): bool
    {
        $allowedFields = ['name', 'business_model', 'team_cofounders', 'team_employees', 
                         'user_position', 'customer_profile', 'market_insights', 'website'];
        
        $allowedModels = ['b2b_saas', 'b2c', 'marketplace', 'agency', 'other'];

        if (isset($response['business_model']) && 
            $response['business_model'] !== null && 
            !in_array($response['business_model'], $allowedModels)) {
            throw ValidationException::withMessages([
                'business_model' => 'Invalid business model'
            ]);
        }

        return true;
    }

    /**
     * Validate goals extraction response
     */
    public function validateGoalsExtraction(array $response): bool
    {
        if (!isset($response['goals']) && !isset($response['standalone_kpis'])) {
            throw ValidationException::withMessages([
                'response' => 'Must contain either goals or standalone_kpis'
            ]);
        }

        return true;
    }

    /**
     * Check if reasoning is specific enough
     */
    public function checkReasoningSpecificity(string $reasoning): array
    {
        $hasNumbers = preg_match('/\d+/', $reasoning);
        $hasPercentage = preg_match('/\d+%/', $reasoning);
        $hasMoneySymbol = preg_match('/[€$£]/', $reasoning);
        $hasSpecificImpact = $hasNumbers || $hasPercentage || $hasMoneySymbol;

        $genericPhrases = [
            'important for growth',
            'helps the business',
            'good for the team',
            'strategic value',
            'improves things',
            'better experience',
        ];

        $isGeneric = false;
        foreach ($genericPhrases as $phrase) {
            if (str_contains(strtolower($reasoning), $phrase)) {
                $isGeneric = true;
                break;
            }
        }

        return [
            'is_specific' => $hasSpecificImpact && !$isGeneric,
            'has_numbers' => $hasNumbers,
            'is_generic' => $isGeneric,
            'quality_score' => $hasSpecificImpact && !$isGeneric ? 100 : ($hasNumbers ? 60 : 20),
        ];
    }

    /**
     * Validate score consistency
     */
    public function validateScoreConsistency(array $evaluation): array
    {
        $warnings = [];

        // High score but drop/delegate action
        if ($evaluation['score'] >= 70 && in_array($evaluation['action_recommendation'] ?? '', ['drop', 'delegate'])) {
            $warnings[] = 'High score (70+) but action is drop/delegate - inconsistent';
        }

        // Low score but keep action
        if ($evaluation['score'] < 40 && ($evaluation['action_recommendation'] ?? '') === 'keep') {
            $warnings[] = 'Low score (<40) but action is keep - should delegate or drop';
        }

        // High score but no KPI link
        if ($evaluation['score'] >= 70 && empty($evaluation['kpi_name'])) {
            $warnings[] = 'High score but no KPI link - should connect to specific metric';
        }

        // Delegate without target role
        if (($evaluation['action_recommendation'] ?? '') === 'delegate' && empty($evaluation['delegation_target_role'])) {
            $warnings[] = 'Delegate action but no target role specified';
        }

        return [
            'is_consistent' => empty($warnings),
            'warnings' => $warnings,
        ];
    }

    /**
     * Enhance response quality
     */
    public function enhanceQuality(array $response): array
    {
        if (!isset($response['evaluations'])) {
            return $response;
        }

        foreach ($response['evaluations'] as &$eval) {
            // Check reasoning quality
            $reasoningCheck = $this->checkReasoningSpecificity($eval['reasoning'] ?? '');
            $eval['reasoning_quality'] = $reasoningCheck['quality_score'];
            
            // Check consistency
            $consistencyCheck = $this->validateScoreConsistency($eval);
            $eval['is_consistent'] = $consistencyCheck['is_consistent'];
            
            if (!empty($consistencyCheck['warnings'])) {
                $eval['warnings'] = $consistencyCheck['warnings'];
            }
        }

        return $response;
    }
}

