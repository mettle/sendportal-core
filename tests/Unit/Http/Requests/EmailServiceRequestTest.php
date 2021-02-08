<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Sendportal\Base\Http\Requests\EmailServiceRequest;
use Sendportal\Base\Models\EmailServiceType;
use Tests\TestCase;

class EmailServiceRequestTest extends TestCase
{
    use WithFaker;

    /** @var EmailServiceRequest */
    protected $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = new EmailServiceRequest();
    }

    /** @test */
    public function it_should_fail_validation_if_the_name_of_the_email_service_is_not_provided()
    {
        // given
        $this->request->merge([
            'type_id' => EmailServiceType::SES
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('name'));
    }

    /** @test */
    public function it_should_fail_validation_if_the_id_of_the_email_service_is_not_provided()
    {
        // given
        $this->request->merge([
            'name' => 'Test'
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('type_id'));
        self::assertEquals(1, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_fail_validation_if_key_or_secret_or_region_or_configuration_set_name_are_not_provided_for_the_ses_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::SES
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.key'));
        self::assertTrue($validator->getMessageBag()->has('settings.secret'));
        self::assertTrue($validator->getMessageBag()->has('settings.region'));
        self::assertTrue($validator->getMessageBag()->has('settings.configuration_set_name'));
        self::assertEquals(4, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_pass_validation_for_the_ses_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::SES,
            'settings' => [
                'key' => Str::random(),
                'secret' => Str::random(),
                'region' => 'us-east1',
                'configuration_set_name' => 'test'
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertTrue($validator->passes());
    }

    /** @test */
    public function it_should_fail_validation_if_the_key_is_not_provided_for_the_sendgrid_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::SENDGRID
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.key'));
        self::assertEquals(1, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_pass_validation_for_the_sendgrid_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::SENDGRID,
            'settings' => [
                'key' => Str::random()
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertTrue($validator->passes());
    }

    /** @test */
    public function it_should_fail_validation_if_the_key_is_not_provided_for_the_postmark_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::POSTMARK
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.key'));
        self::assertEquals(1, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_pass_validation_for_the_postmark_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::POSTMARK,
            'settings' => [
                'key' => Str::random()
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertTrue($validator->passes());
    }

    /** @test */
    public function it_should_fail_validation_if_key_or_domain_or_zone_are_not_provided_for_the_mailgun_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILGUN,
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.key'));
        self::assertTrue($validator->getMessageBag()->has('settings.domain'));
        self::assertTrue($validator->getMessageBag()->has('settings.zone'));
        self::assertEquals(3, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_fail_validation_if_the_provided_zone_is_not_valid_for_the_mailgun_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILGUN,
            'settings' => [
                'key' => Str::random(),
                'domain' => $this->faker->url,
                'zone' => 'JP'
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.zone'));
        self::assertEquals(1, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_pass_validation_for_the_mailgun_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILGUN,
            'settings' => [
                'key' => Str::random(),
                'domain' => $this->faker->url,
                'zone' => 'EU'
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertTrue($validator->passes());
    }

    /** @test */
    public function it_should_fail_validation_if_key_or_secret_or_zone_are_not_provided_for_the_mailjet_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILJET,
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.key'));
        self::assertTrue($validator->getMessageBag()->has('settings.secret'));
        self::assertTrue($validator->getMessageBag()->has('settings.zone'));
        self::assertEquals(3, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_fail_validation_if_the_provided_zone_is_not_valid_for_the_mailjet_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILJET,
            'settings' => [
                'key' => Str::random(),
                'secret' => Str::random(),
                'zone' => 'JP'
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertFalse($validator->passes());
        self::assertTrue($validator->getMessageBag()->has('settings.zone'));
        self::assertEquals(1, $validator->getMessageBag()->count());
    }

    /** @test */
    public function it_should_pass_validation_for_the_mailjet_email_service()
    {
        // given
        $this->request->merge([
            'name' => 'Test',
            'type_id' => EmailServiceType::MAILJET,
            'settings' => [
                'key' => Str::random(),
                'secret' => Str::random(),
                'zone' => 'Default'
            ]
        ]);

        $validator = $this->getValidator();

        // then
        self::assertTrue($validator->passes());
    }

    protected function getValidator(): ValidatorContract
    {
        return Validator::make($this->request->all(), $this->request->rules(), $this->request->messages());
    }
}
