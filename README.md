# Send CSP Violation to Google Analytics and by email
With a fully encrypted website not only a heavy SEO and trust benefit is gained. In addition a website can be monitored against fraudulent or even malicious content. Whilst, this solution does not provide any protection, turning all website visitors into an active radar enables a website owner to get aware of CSP-Violations at the moment's notice.

## What is CSP (Content Security Policy)?
A in browser security system to prevent attacks like XSS (Cross Site Scripting) or others like implementing malware. It's major purpose is to whitelist assets such as images, JavaScript, CSS, fonts etc. to being loaded from desired domains. In addition certain obvious functions like eval, inlining or data can be stopped from being executed too.

Check out the following resources if this topic is new:
1. [CSP Quick Reference Guide](https://content-security-policy.com/)
2. [Content Security Policy in Wikipedia](https://de.wikipedia.org/wiki/Content_Security_Policy)
3. [CSP on Google's Web Fundamentals](https://developers.google.com/web/fundamentals/security/csp/)
4. [OWASP CSP Wiki](https://www.owasp.org/index.php/Content_Security_Policy)
5. [CSP Policy Generator from Report URI](https://report-uri.io/home/generate)
6. [SP Policy Generator from CSP is awesome](http://cspisawesome.com/)
7. [CSP Validator](https://cspvalidator.org/#url=https://cspvalidator.org/)
8. [CSP Validator from Google](https://csp-evaluator.withgoogle.com/)

## What does the script do?
1. Upon a browser (a real end user) recognizes a CSP violation, it automatically sends an XHR to the report URI defined in the CSP-Response Header
2. The file, if found and access rights are correct, receives it
3. Cookies are processed to extract the Google Analytics UA-ID
4. If no UA-ID was found a fallback ID (must been defined) is used
5. Extract requesting domain from URI (Note: Could be used for switch/case lookup of UA-ID)
6. Generate and send Google Analytics event
7. Send email for immediate notifications with CSP-Violation
8. Write CSP-Violation into local log

## CSP use cases
1. Immediately getting aware of violations against Content Security Policy
2. Analyzing impact of blocked content on the customer experience
3. Remain informed on a multiple author website if some implement insecure content

## CSP Response header implementation
<script src="https://gist.github.com/mikeg-de/e0eab64217d6c2c51a9dc890a6e107de.js"></script>

### Prerequisites
1. A fully encrypted website
2. FTP-Access to website
3. CSP-response headers been send

Note: Upon first implementing a CSP I highly recommend to set CSP-Response headers to [report only](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy-Report-Only)!

### Installing
1. Save csp-report-file.php into website root
2. Modify sender and recipient address as well as the [Google Analytics ID](https://support.google.com/analytics/answer/7372977?hl=en)

Note: If the PHP-file is not saved in the root directory adjust the Report-URI in the CSP-Header accordingly!

## Running the tests
Forcibly violate a CSP-Policy by i.e. integrating an iframe form YouTube.

## Versioning
0.1 Initialization

## To be done
1. Setting up centralized logging
2. Send an event to each recognized Google Analytics Property by creating a loop

## Authors
**Mike Wiegand** - [atMedia Online Marketing](atmedia-marketing.com)

See also the list of [Acknowledgments](#cknowledgments) where their work greatly contributed to this project.

## License
This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments
* [chrisblakley/server-side-ga-events.php](https://gist.github.com/chrisblakley/e1f3d79b6cecb463dd8a)
* [Gearside Design](https://gearside.com/using-server-side-google-analytics-sending-pageviews-event-tracking/)
* [Google Analytics – Send Server Side Events](https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event)
