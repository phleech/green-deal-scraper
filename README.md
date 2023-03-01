# Green Deal Scraper

A quick script to scrape business info from the public register for Green Deal Participants.

## Build & Run
`docker build -t scraper .`
`docker run -p 80:80 scraper`

## Output
|Name|Phone|Email|Address|Link|
|----|-----|-----|-------|----|
|ACME Ltd.|01234 567 890|dummy@acme.ltd|1 Acme Way, Barnsley, UK, BN1 1BN|https://gdorb.beis.gov.uk/provider-profile/?provider_id=1|
