# Sendportal Core

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