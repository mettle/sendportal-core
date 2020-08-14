# Sendportal Core

## 1.0.7 - 2020-08-14

- Add ability to test an e-mail service ([#62](https://github.com/mettle/sendportal-core/pull/62))
- Fix behaviour that removed subscribers on segment update ([#61](https://github.com/mettle/sendportal-core/pull/61))
- Add ability to delete a subscriber ([#60](https://github.com/mettle/sendportal-core/pull/60))

## 1.0.6 - 2020-08-07

- Add GH action for php-cs ([#35](https://github.com/mettle/sendportal-core/pull/35))
- Add Campaign Endpoints to API ([#40](https://github.com/mettle/sendportal-core/pull/40))
- Uniform mail adapters send method ([#51](https://github.com/mettle/sendportal-core/pull/51))
- Add Sqlite support and defaults the test suite to use the sqlite database ([#52](https://github.com/mettle/sendportal-core/pull/52))
- Fix count unique opens per period in Postgres ([#53](https://github.com/mettle/sendportal-core/pull/53))
- Bump elliptic from 6.5.2 to 6.5.3 ([#55](https://github.com/mettle/sendportal-core/pull/55))
- add from name to outgoing emails ([#56](https://github.com/mettle/sendportal-core/pull/56))
- add mailjet support ([#57](https://github.com/mettle/sendportal-core/pull/57))

## 1.0.5 - 2020-08-03

- bump lodash version ([#44](https://github.com/mettle/sendportal-core/pull/44))
- fix primary key when chunking on segments ([#54](https://github.com/mettle/sendportal-core/pull/54))

## 1.0.4 - 2020-07-21

- fix SES adapter ([#42](https://github.com/mettle/sendportal-core/pull/42))

## 1.0.3 - 2020-07-11

- added CHANGELOG.md ([#39](https://github.com/mettle/sendportal-core/pull/39))
- added API for templates ([#38](https://github.com/mettle/sendportal-core/pull/38))
- add validation for deleting templates that are in use ([#37](https://github.com/mettle/sendportal-core/pull/37))
- add Template API endpoints ([#38](https://github.com/mettle/sendportal-core/pull/38))
- added ability to delete segments ([#36](https://github.com/mettle/sendportal-core/pull/36))
- handle null campaign content ([#34](https://github.com/mettle/sendportal-core/pull/34))

## 1.0.2 - 2020-06-22

- changed setup command success outputs to use info instead of line [#33](https://github.com/mettle/sendportal-core/pull/33))
- increased content length in the campaign and template tables ([#32](https://github.com/mettle/sendportal-core/pull/32))
- replaced relative URLs with route helpers when linking to the dashboard ([#29](https://github.com/mettle/sendportal-core/pull/29))
- renamed and cleaned up model factories ([#31](https://github.com/mettle/sendportal-core/pull/31))

## 1.0.1 - 2020-06-11

- fixed dashboard subscriber growth chart in PostgreSQL environments ([#15](https://github.com/mettle/sendportal-core/pull/15))
- apply correct namespace to layouts that are extended in subscriber/unsubscribe views ([#16](https://github.com/mettle/sendportal-core/pull/16))
- remove deprecated helper methods ([#17](https://github.com/mettle/sendportal-core/pull/17))
- fix database constraint error when dispatching campaigns in environments that use the SES provider ([#18](https://github.com/mettle/sendportal-core/pull/18))
- fix subscriber email validation rule that was failing in PostgreSQL environments ([#19](https://github.com/mettle/sendportal-core/pull/19))
- fix behaviour surrounding the dispatch of scheduled campaigns ([#20](https://github.com/mettle/sendportal-core/pull/20))
- fix registration toggle ([#21](https://github.com/mettle/sendportal-core/pull/21))
- fix bold text rendering in subscriber/unsubscribe views ([#27](https://github.com/mettle/sendportal-core/pull/27))

## 1.0.0 - 2020-06-09

- initial release
