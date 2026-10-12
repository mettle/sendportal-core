<p align="center"><img src="https://sendportal.io/img/sendportal.png" width="250"></p>


Modern open-source self-hosted email marketing.

- [Website](https://sendportal.io)
- [Documentation (V4)](https://github.com/mettle/sendportal-docs/tree/fb25c19f5b7ee1d03de27d149589e430cc526707/v4)

## Introduction

The core functionality of SendPortal is contained within the [SendPortal Core](https://github.com/mettle/sendportal-core) package. If you would like to add SendPortal to an existing application, you only require this package.

## Features
SendPortal includes subscriber and list management, email campaigns, message tracking, reports and multiple workspaces/domains in a modern, flexible and scalable application.

SendPortal integrates with [Amazon SES](https://aws.amazon.com/ses), [Postmark](https://postmarkapp.com), [Sendgrid](https://sendgrid.com), [Mailgun](https://www.mailgun.com/), [Mailjet](https://www.mailjet.com) and [Resend](https://resend.com).

The [SendPortal](https://github.com/mettle/sendportal) application acts as a wrapper around SendPortal Core. This will allow you to run your own copy of SendPortal as a stand-alone application, including user authentication and multiple workspaces.

## Installation

SendPortal can be installed as a stand-alone application (i.e. including everything you need to run SendPortal), or as a package inside an existing Laravel application.

The V4 guides below are hosted in the documentation repository while sendportal.io is being updated.

If you would like to install SendPortal as a stand-alone application, please follow the [installation guide](https://github.com/mettle/sendportal-docs/blob/fb25c19f5b7ee1d03de27d149589e430cc526707/v4/installation.md).

If you would like to add SendPortal to an existing application, please follow the [package installation guide](https://github.com/mettle/sendportal-docs/blob/fb25c19f5b7ee1d03de27d149589e430cc526707/v4/package-installation.md).

If you are upgrading from SendPortal V3, please follow the [upgrade guide](https://github.com/mettle/sendportal-docs/blob/fb25c19f5b7ee1d03de27d149589e430cc526707/v4/upgrade-guide.md).

## Requirements
SendPortal V4 requires:

- PHP 8.3+
- Laravel 12+
- MySQL (≥ 5.7) or PostgreSQL (≥ 10)

If you are on PHP 8.2 or Laravel 10/11, please use [SendPortal V3](https://github.com/mettle/sendportal-core/releases/tag/v3.0.0)

If you are on an earlier version of PHP (7.3+) or Laravel (8+), please use [SendPortal V2](https://github.com/mettle/sendportal-core/releases/tag/v2.0.5)

