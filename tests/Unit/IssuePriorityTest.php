<?php

namespace Tests\Unit;

use App\Models\IssueReport;
use Tests\TestCase;

class IssuePriorityTest extends TestCase
{
    public function test_priority_matches_resident_facing_categories(): void
    {
        $this->assertSame('Critical', (new IssueReport(['category' => 'Emergency']))->autoSetPriority());
        $this->assertSame('Critical', (new IssueReport(['category' => 'Security Concern']))->autoSetPriority());
        $this->assertSame('High', (new IssueReport(['category' => 'Drainage / Flooding']))->autoSetPriority());
        $this->assertSame('High', (new IssueReport(['category' => 'Road / Pavement']))->autoSetPriority());
        $this->assertSame('Medium', (new IssueReport(['category' => 'Garbage / Sanitation']))->autoSetPriority());
        $this->assertSame('Low', (new IssueReport(['category' => 'Noise Complaint']))->autoSetPriority());
        $this->assertSame('Low', (new IssueReport(['category' => 'Other']))->autoSetPriority());
    }

    public function test_unknown_category_defaults_to_low(): void
    {
        $this->assertSame('Low', (new IssueReport(['category' => 'Something Random']))->autoSetPriority());
    }
}
