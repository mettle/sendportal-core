# Sendportal

## 1.0.2 - 2020-06-22

- increased content length in the campaign and template tables
- replaced relative URLs with route helpers when linking to the dashboard

## 1.0.1 - 2020-06-11

- fixed dashboard subscriber growth chart in PostgreSQL environments
- apply correct namespace to layouts that are extended in subscriber/unsubscribe views
- remove deprecated helper methods
- fix database constraint error when dispatching campaigns in environments that use the SES provider
- fix subscriber email validation rule that was failing in PostgreSQL environments
- fix behaviour surrounding the dispatch of scheduled campaigns
- fix bold text rendering in subscriber/unsubscribe views

## 1.0.0 - 2020-06-09

- initial release