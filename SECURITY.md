# Security Policy

## Supported versions

Security fixes are currently provided for the latest public release of Meta Pixel Bridge.

| Version | Supported |
|---|---|
| 1.0.1 | ✅ |
| 1.0.0 | ❌ |

Users are encouraged to update to the latest available release before reporting an issue.

---

## Reporting a security vulnerability

Please do **not** open a public GitHub issue for vulnerabilities that could expose:

- customer data,
- store data,
- authentication information,
- administrative access,
- sensitive configuration,
- security tokens,
- server information,
- or another user's private information.

Instead, report the issue privately by email:

**info@craftin.lv**

Please use a subject such as:

```text
[SECURITY] Meta Pixel Bridge vulnerability report
```

Include as much useful information as possible:

- Meta Pixel Bridge version
- PrestaShop version
- PHP version, if relevant
- affected file or component
- description of the vulnerability
- steps required to reproduce it
- expected behaviour
- actual behaviour
- potential security impact
- screenshots or logs, when useful

Please remove passwords, API credentials, customer information and other unrelated sensitive data before sending logs or screenshots.

---

## Responsible disclosure

Please allow reasonable time to investigate and, when necessary, prepare a fix before publicly disclosing a vulnerability.

Security reports submitted in good faith are welcome.

The maintainer may:

1. confirm receipt of the report,
2. request additional technical details,
3. reproduce and assess the issue,
4. prepare a patch,
5. publish an updated release,
6. document the security fix when appropriate.

No guaranteed response or resolution time is provided.

---

## Scope

Security issues directly related to Meta Pixel Bridge are in scope.

Examples include:

- unintended disclosure of sensitive information,
- unsafe handling of configuration values,
- unauthorized administrative actions,
- cross-site scripting introduced by the module,
- CSRF vulnerabilities in module administration,
- injection vulnerabilities,
- insecure server-side endpoints,
- consent bypass caused by the module,
- unintended loading of Meta Pixel before configured consent is granted.

Issues caused entirely by unrelated third-party modules, themes, hosting configuration, PrestaShop core, browsers or Meta services are generally outside the project's scope.

---

## Privacy-related issues

Meta Pixel Bridge is designed to support consent-controlled Meta Pixel loading.

If you discover that Meta Pixel Bridge sends a browser tracking event despite the configured consent state being denied, please treat this as a security/privacy issue and report it privately.

When reporting such an issue, include:

```text
Consent source
Consent configuration
Expected consent state
Actual consent state
Browser console output
Network request details
```

Do not include real customer information.

---

## Secrets and credentials

Meta Pixel Bridge v1.0.1 does not require a Meta access token or Conversions API secret.

The configured Pixel / Dataset ID is not treated as a secret.

Future releases that introduce server-side integrations may require additional credential-handling rules.

Never publish real access tokens, API secrets, passwords or administrative credentials in GitHub issues.

---

## Dependencies

Meta Pixel Bridge currently has no external runtime package manager dependency for its browser Pixel functionality.

Security issues in:

- PrestaShop,
- PHP,
- the web server,
- the browser,
- Meta Pixel itself,
- or third-party consent managers

should generally be reported to the corresponding project or vendor unless Meta Pixel Bridge specifically introduces or exposes the vulnerability.

---

## Security updates

Security-related fixes may be published as patch releases, for example:

```text
1.0.1 → 1.0.2
```

Users should review the release notes and update promptly when a release contains a security fix.

---

## Author and contact

Meta Pixel Bridge is developed and maintained by:

**CraftIN / QvarcY (kas.id.lv)**

Website:
https://kas.id.lv/

CraftIN:
https://craftin.lv/

GitHub:
https://github.com/QvarcY

Security contact:
info@craftin.lv