<?php

namespace Tests\Feature;

use App\Filament\Resources\Notices\NoticeResource;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminNoticeValidationTest extends TestCase
{
    public function test_official_public_notice_requires_source_check_date_and_deadline(): void
    {
        try {
            NoticeResource::validatePublication([
                'public_visible' => true,
                'is_demo' => false,
                'status' => 'open',
            ]);
            $this->fail('A validação deveria rejeitar o edital incompleto.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.official_page_url', $exception->errors());
            $this->assertArrayHasKey('data.source_checked_at', $exception->errors());
            $this->assertArrayHasKey('data.proposal_deadline', $exception->errors());
        }
    }

    public function test_external_notice_links_must_use_https(): void
    {
        $this->expectException(ValidationException::class);

        NoticeResource::validatePublication([
            'public_visible' => true,
            'is_demo' => false,
            'status' => 'closed',
            'official_page_url' => 'http://example.test/edital',
            'source_checked_at' => now(),
        ]);
    }
}
