# ELM Credential Package Plan

## Purpose

Extract European Digital Credential (ELM/EDC) document creation from the
Moodle plugin into a separate framework-independent PHP package. This package
owns the ELM data model, profile rules, identifiers, and JSON-LD serialization.

The package is deliberately separate from `isy-thl/dss-csc-signing`. It creates
unsigned serialized credential documents; the DSS/CSC signing package receives
those bytes later and must not need to know that the document is an ELM
credential.

## DSS/CSC handoff prerequisites

The signing boundary is published as `isy-thl/dss-csc-signing` `v0.1.2` and
Moodle consumes that exact release through its committed Composer lockfile.
The ELM package must therefore stop at deterministic unsigned document bytes:

- Build and validate ELM data without calling DSS, CSC, HTTP, OpenSSL signing,
  or application services.
- Pass the resulting JSON unchanged to the signing package; do not duplicate
  timestamping, certificate handling, signature algorithms, or validation
  policy in ELM.
- Keep Moodle badge/user/database/settings/file access in the mapper layer.
- Preserve strict JSON-object input, stable identifiers, language maps, date
  representation, and omission of unset optional fields.
- Treat QTSP, DSS, TSA, trusted-list, certificate ownership, renewal, and
  outage checks as deployment qualification evidence, not ELM model concerns.

The signing extraction also showed that secure defaults and explicit dependency
boundaries matter more than framework convenience: secrets and signatures must
never enter logs, transport must be HTTPS by default, and network access should
be injectable or absent from format-model tests.

## Target Package

Proposed Composer package:

```text
isy-thl/european-digital-credentials
```

Proposed standalone repository:

```text
https://github.com/ild-thl/european-digital-credentials
```

The PHP namespace and final repository name should be confirmed before the
first package commit. The package must not depend on Moodle, Laravel, database
globals, Moodle settings, Moodle exceptions, or Moodle debugging functions.

## Current assessment and remaining gaps

The standalone package now contains the main credential graph exercised by the
representative unsigned credential: credential, issuer, subject, display,
awarding, achievements, qualifications, activities, assessments, outcomes,
entitlements, identifiers, notes, credits, and achievement/activity/assessment
graph relations. It also has deterministic UTC serialization, top-level
document preflight validation, typed validation errors, Composer quality gates,
CI, and focused PHPUnit coverage.

The package is not yet ready for publication or Moodle integration. The
legacy-derived concept rules are now enforced for the fields currently present
in the package: credential profile, language, country, education credit,
assessment, verification, entitlement, media encoding/file type, EQF/NQF, and
ISCED-F education subjects. The package intentionally uses ordinary typed
`Concept` values plus field-owned scheme assertions instead of empty subclasses
for each legacy concept class; NQF uses the dynamic QDR scheme family.

Remaining concept/model gaps include achievement-specification `dcType`,
learning setting, mode, status, target groups, learning-outcome ESCO skills
and reusability, accreditation controlled fields, entitlement occupation
limits, and application-specific qualification-code schemes. Broader gaps are
controlled vocabulary snapshots/resources and membership validation against
loaded snapshots, deeper profile validation, semantic fixture comparison, any
remaining fields needed by the Moodle badge path, a Moodle-side mapper with
integration tests, and clean-install, signer-handoff, and release
qualification checks.

The graph remains intentionally incremental: unsupported profile entities must
fail visibly rather than being silently accepted or emitted as unvalidated
generic claims.

## Boundaries

### ELM package owns

- ELM credential entities and value objects.
- ELM controlled concepts, concept schemes, identifiers, and vocabulary
  contracts.
- Required and optional ELM profile fields.
- JSON-LD context, type, schema, and field names.
- Localized values, dates, issuer, subject, display, achievement, and awarding
  data.
- Deterministic serialization and format validation.

### Vocabulary and resource boundary

Vocabulary support has two distinct responsibilities:

1. **Validation:** a supplied concept must have the expected URI, scheme,
   notation, language map, and membership in the allowed concept scheme for
   the relevant ELM field.
2. **Discovery:** an application or frontend must retrieve available concepts
   for a relevant scheme, including stable identifiers, labels, notation,
   scheme identity, and enough metadata to present a selection list.

The package must provide framework-neutral typed concept and scheme records,
validation contracts, and an injectable vocabulary resource/provider boundary.
It must not require live HTTP during pure model construction or serialization.
An HTTP-backed provider is nevertheless required for discovery in real
applications and may live in this package or a companion resource adapter,
provided transport remains injected and bounded.

The design must support:

- a deterministic local provider for shipped profile resources and tests;
- an HTTP provider for authoritative EU vocabulary endpoints;
- a cache decorator or application-owned cache keyed by scheme, language,
  resource version, and relevant request parameters;
- explicit failure and freshness behavior when a remote source is unavailable
  or returns malformed RDF/XML or JSON-LD.

The legacy Moodle implementation already demonstrates the need for retrieval
and caching: it fetches RDF/XML scheme data, derives concept identifiers and
labels, and caches the resulting concepts. That behavior is an architectural
requirement, but Moodle cache APIs and HTTP clients remain outside the pure ELM
model. The package must expose enough metadata and interfaces for Moodle and a
future trainer-facing credential builder to reuse the same vocabulary service
rather than reimplementing scheme browsing.

Live vocabulary retrieval must never be hidden inside a constructor,
serializer, or validation call without an explicit provider. Validation of a
concept against a previously loaded, trusted scheme snapshot must remain
deterministic and testable offline.

### Moodle plugin retains

- Badge and user database lookup.
- Badge awarding-criteria interpretation.
- Moodle settings and file-storage access.
- Mapping Moodle records into ELM package builders or DTOs.
- Moodle permissions, capabilities, events, language strings, and errors.
- Calling the ELM serializer and passing the resulting document to the
  published DSS/CSC signing package.

### DSS/CSC package remains unchanged

- DSS timestamping and JAdES orchestration.
- CSC authorization, hash signing, certificate discovery, and revocation.
- Certificate and signature validation.
- HTTP transport, secrets, polling, and signing exceptions.

## Current Source Inventory

The current Moodle implementation is under `classes/credential/` and includes
the credential model, base entities, concepts, localized strings, dates,
issuer, subject, display, achievement, awarding, and supporting entities.
`credential::fromBadge()` currently mixes ELM construction with Moodle globals,
Moodle settings, database access, and Moodle debugging. That method should be
split at the package boundary:

```text
Moodle badge/user/settings
    -> Moodle mapper
    -> ELM builder or DTOs
    -> deterministic ELM JSON-LD
    -> DSS/CSC signer
```

## Invariants

### Model and profile

1. Required ELM fields are enforced before serialization.
2. ELM credential type, JSON-LD context, schema, and profile identifiers are
   stable and documented.
3. Issuer and subject values are represented according to the ELM profile.
4. Optional fields are absent when unset rather than serialized as accidental
   nulls, unless the profile explicitly requires null.
5. Unsupported or malformed controlled-vocabulary values are rejected visibly.
6. Controlled concepts are validated against the allowed scheme and accepted
  values can be enumerated for UI selection.

### Serialization

1. Serialization is deterministic for the same object state.
2. Localized values retain their language tags and language-map structure.
3. Date serialization uses one documented timezone and representation.
4. Equivalent expiration and valid-until values serialize consistently.
5. Identifiers use documented stable formats.
6. Serialization never includes private keys, client secrets, access tokens,
   signing values, or transport configuration.
7. The package returns unsigned serialized document bytes and does not call DSS,
   CSC, HTTP, or cryptographic signing APIs.
8. Vocabulary retrieval and caching are explicit dependencies and never hidden
  side effects of model serialization.

### Integration

1. A plain PHP application can build the same ELM JSON without Moodle.
2. Moodle mapping tests prove badge and user data are transferred correctly.
3. The resulting JSON can be passed unchanged to
   `isy-thl/dss-csc-signing`.
4. Delivery details are modeled separately from the core credential document.

## Implementation Steps

### Step 1: Freeze current ELM behavior

- Capture representative non-secret credential JSON fixtures.
- Inventory all entities, concepts, required fields, and current identifier
  formats.
- Record current date, language, issuer, subject, display, achievement, and
  awarding behavior.
- Identify behavior that is accidental, Moodle-specific, or incompatible with
  the ELM profile.

**Exit criteria:** current output and intended profile behavior are documented
by fixtures and focused tests.

### Step 2: Define package contracts and value objects

- Create the standalone Composer package.
- Define builders or DTOs for credential, issuer, subject, display, dates, and
  profile-specific data.
- Define typed package exceptions for invalid profile data.
- Keep constructors and serializers independent of Moodle classes.

**Exit criteria:** package tests can build credentials using only plain PHP
values and fake-free deterministic serialization.

### Step 3: Move reusable ELM entities

- Move or rewrite the reusable model and concept classes into the package.
- Remove database, settings, globals, and debugging calls from package code.
- Preserve public ELM field names and documented identifier behavior.
- Keep format-specific validation next to the format model.

**Exit criteria:** the package can create valid unsigned ELM JSON without
Moodle bootstrap.

### Step 3a: Define controlled vocabulary and discovery

- Specify typed concept and concept-scheme records with stable URI, notation,
  label, scheme, language, and source metadata.
- Identify the controlled schemes required by the current Moodle badge path
  and distinguish profile-owned concepts from application configuration.
- Define a package-neutral vocabulary provider/resource contract for loading a
  scheme snapshot and looking up a concept by URI or notation.
- Implement an offline/local provider for deterministic tests and shipped
  profile resources.
- Implement or specify an injectable HTTP provider for authoritative RDF/XML
  or JSON-LD vocabulary endpoints, with bounded response handling and typed
  failures.
- Add a cache decorator or document the application cache contract, including
  cache keys, TTL/freshness, invalidation, language variants, and stale-data
  behavior.
- Add tests for membership acceptance/rejection, scheme browsing, language
  maps, malformed remote data, provider failures, and cache hit/miss behavior.

**Exit criteria:** a plain PHP consumer can enumerate allowed concepts for a
scheme and validate a selected concept offline from a trusted snapshot, while
HTTP and caching remain injectable and absent from pure model tests.

### Step 4: Build the Moodle mapper

- Replace `credential::fromBadge()` with a Moodle-side mapper.
- Read badge, user, awarding criteria, settings, and files in the mapper.
- Pass plain values or package DTOs into the ELM package.
- Preserve Moodle error handling and existing delivery-details behavior.

**Exit criteria:** Moodle credential tests produce equivalent output through the
mapper, with no Moodle code inside the ELM package.

### Step 5: Add package and integration tests

- Add required-field and profile validation tests.
- Add deterministic serialization fixtures.
- Add localized-string and date tests.
- Add optional-field and identifier tests.
- Add Moodle mapper tests with controlled badge and user fixtures.
- Verify the serialized result can enter the DSS/CSC signer unchanged.
- Add semantic fixture comparison tests that distinguish equivalent ELM data
  from intentional legacy wrapper, date, context, and shape differences.

**Exit criteria:** package tests run without Moodle, and Moodle integration
tests cover the mapping boundary.

### Step 6: Publish and integrate

- Add package README, changelog, license, CI, and Composer metadata.
- Test a clean Composer install and publish a first tagged release.
- Add the package as a plugin-local Composer dependency.
- Remove duplicated ELM implementation only after the replacement passes the
  Moodle test suite.
- Verify the vocabulary provider can serve both Moodle mapper choices and a
  future trainer-facing credential builder without duplicating concept lookup
  or caching logic.

**Exit criteria:** a plain PHP consumer and Moodle both use the published ELM
package, while DSS/CSC signing remains a separate dependency.

## Explicit Non-Goals

- No DSS or CSC protocol implementation.
- No JAdES signature generation.
- No private-key or certificate handling.
- No Moodle database or settings access in the package.
- No mandatory live HTTP access during model construction, validation from a
  trusted snapshot, or serialization.
- No Open Badge model in the first release.
- No generic credential abstraction that hides ELM profile rules.

## Definition of Done

- The package installs independently through Composer.
- No package source references Moodle or Laravel.
- ELM model, profile, identifier, localization, date, and serialization rules
  have focused automated tests.
- Controlled vocabulary membership and browseable scheme resources have typed
  contracts, offline tests, and explicit HTTP/cache integration boundaries.
- The required vocabulary schemes for the Moodle badge path are identified,
  with source, version, language, and freshness decisions documented.
- Profile validation rejects representative invalid nested documents before
  signing handoff.
- Serialization is deterministic and contains no signing secrets.
- Moodle uses a thin mapper rather than owning the ELM model implementation.
- A plain PHP application can build equivalent ELM JSON.
- The output can be handed to `isy-thl/dss-csc-signing` without format-specific
  changes in the signing package.
