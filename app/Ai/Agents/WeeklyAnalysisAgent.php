<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class WeeklyAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions for the agent.
     */
    public function instructions(): string
    {
        return 'You are an expert personal productivity coach for GoalPilot, a weekly goal and time management application. '
            .'Analyze the provided weekly goal allocations, planned time vs logged time, and goal progress. '
            .'Provide a constructive, encouraging, and highly actionable structured analysis. '
            .'Be objective yet supportive in your assessment, highlighting clear achievements, pinpointing areas for improvement, '
            .'and offering concrete, actionable recommendations for the user\'s next planning cycle.';
    }

    /**
     * Get the JSON schema definition for the structured output.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required(),
            'achievements' => $schema->array()->items($schema->string())->required(),
            'areas_for_improvement' => $schema->array()->items($schema->string())->required(),
            'actionable_recommendations' => $schema->array()->items($schema->string())->required(),
            'execution_score' => $schema->integer()->min(1)->max(10)->required(),
        ];
    }
}
