<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use Sendportal\Base\Adapters\MailgunMailAdapter;
use Sendportal\Base\Adapters\PostmarkMailAdapter;
use Sendportal\Base\Adapters\SendgridMailAdapter;
use Sendportal\Base\Adapters\SesMailAdapter;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;
use InvalidArgumentException;
use Tests\TestCase;

class MailAdapterFactoryTest extends TestCase
{
    /** @test */
    function can_build_a_mailgun_adapter()
    {
        // given
        $provider = factory(Provider::class)->make(['team_id' => null, 'type_id' => ProviderType::MAILGUN]);
        $adapterFactory = new MailAdapterFactory();

        // when
        $adapter = $adapterFactory->adapter($provider);

        // then
        $this->assertEquals(MailgunMailAdapter::class, get_class($adapter));
    }

    /** @test */
    function can_build_a_sendgrid_adapter()
    {
        // given
        $provider = factory(Provider::class)->make(['team_id' => null, 'type_id' => ProviderType::SENDGRID]);
        $adapterFactory = new MailAdapterFactory();

        // when
        $adapter = $adapterFactory->adapter($provider);

        // then
        $this->assertEquals(SendgridMailAdapter::class, get_class($adapter));
    }

    /** @test */
    function can_build_a_postmark_adapter()
    {
        // given
        $provider = factory(Provider::class)->make(['team_id' => null, 'type_id' => ProviderType::POSTMARK]);
        $adapterFactory = new MailAdapterFactory();

        // when
        $adapter = $adapterFactory->adapter($provider);

        // then
        $this->assertEquals(PostmarkMailAdapter::class, get_class($adapter));
    }

    /** @test */
    function can_build_an_ses_adapter()
    {
        // given
        $provider = factory(Provider::class)->make(['team_id' => null, 'type_id' => ProviderType::SES]);
        $adapterFactory = new MailAdapterFactory();

        // when
        $adapter = $adapterFactory->adapter($provider);

        // then
        $this->assertEquals(SesMailAdapter::class, get_class($adapter));
    }

    /** @test */
    function an_exception_is_thrown_when_building_an_unknown_adapater()
    {
        // given
        $provider = factory(Provider::class)->make(['team_id' => null, 'type_id' => 100]);
        $adapterFactory = new MailAdapterFactory();

        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        $adapterFactory->adapter($provider);
    }
}
