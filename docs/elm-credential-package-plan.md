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

## Target Package

Composer package:

```text
isy-thl/european-learning-model
```

Published standalone repository:

```text
https://github.com/ild-thl/php-european-learning-model
```

The package is released as `v0.1.0` with the
`IsyThl\EuropeanLearningModel` namespace. It does not depend on Moodle,
Laravel, database globals, Moodle settings, Moodle exceptions, or Moodle
debugging functions.

## Current assessment and remaining gaps

The standalone package now contains the main credential graph exercised by the
representative unsigned credential: credential, issuer, subject, display,
awarding, achievements, qualifications, activities, assessments, outcomes,
entitlements, identifiers, notes, credits, and achievement/activity/assessment
graph relations. It also has deterministic UTC serialization, top-level
document preflight validation, typed validation errors, Composer quality gates,
CI, and focused PHPUnit coverage.

The package is published and integrated into the Moodle badge creation path.
The legacy-derived concept rules are now enforced for the fields currently present
in the package: credential profile, language, country, education credit,
assessment, verification, entitlement, media encoding/file type, EQF/NQF, and
ISCED-F education subjects. The package intentionally uses ordinary typed
`Concept` values plus field-owned scheme assertions instead of empty subclasses
for each legacy concept class; NQF uses the dynamic QDR scheme family.

Achievement-specification `dcType`, learning setting, mode, status, target
groups, learning-outcome ESCO skills and reusability, entitlement occupation
limits, and accreditation decision, status, EQF, ISCED-F, ATU, and
credential-type concept fields are represented and scheme-checked in the
package. Remaining concept/model gaps are application-specific
qualification-code schemes. Broader gaps are controlled vocabulary
snapshots/resources and membership validation against loaded snapshots, deeper
profile validation, semantic fixture comparison, broader Moodle
issuer/media/delivery mapping, an explicit signer-handoff integration test,
and final release qualification checks.

The current mapper slice is covered by Docker PHPUnit with `OK (2 tests, 6
assertions)`. The obsolete `classes/credential/` implementation and
`tests/credential_test.php` have been removed because no active Moodle code
depends on them.

The graph remains intentionally incremental: unsupported profile entities must
fail visibly rather than being silently accepted or emitted as unvalidated
generic claims.

Vocabulary qualification now has deterministic parser coverage for every
registered scheme identifier and an opt-in live test (`ELM_LIVE_VOCABULARY_TESTS=1`).
Large authority/QDR/ISCED-F/DCF resources are classified as search-required
and are protected from unbounded snapshot enrichment. ESCO skills and
occupations use the documented paged ESCO search API through the package's
HAL response parser; transport and endpoint configuration remain
application-owned.

### Concept and vocabulary audit

The September 2026 audit found that the concept work is substantially
converted, but not yet clean or complete:

- The Moodle implementation contains 27 concept-related classes, including
  the base concept, scheme, and vocabulary abstractions. The package registry
  contains 24 scheme identifiers.
- Most legacy schemes are now represented by package-owned `Concept` values and
  field-level scheme assertions: language, country, credential profile, EQF,
  QDR/NQF, learning setting/activity/assessment, ISCED-F, entitlement,
  occupations, ESCO skills, accreditation decision/status, ATU, content
  encoding/file type, education credit, learning opportunity, skill reuse,
  supervision/verification, and target groups.
- DCF skills are not fully converted. The legacy
  `LearningOutcome::relatedSkills` path uses `dcf_skills_concept`, while the
  package currently accepts generic concepts there without asserting the DCF
  scheme.
- `ElmVocabularySchemes::ACCREDITATION` is registered but is not used as a
  controlled vocabulary by a package field or legacy concept class. It should
  be removed from the vocabulary registry or explicitly classified as
  metadata/entity-type information.
- Legacy static convenience factories such as language lookup by code,
  generic credential profile, content-type constants, base64 encoding, and
  entitlement presets have not been reproduced as package vocabulary classes.
  They should become explicit application mappings or small package-owned
  factories only where the behavior is format-owned.
- The package still contains 63 copied files below `src/Legacy/`. Every one
  contains Moodle/framework coupling such as `MOODLE_INTERNAL`, Moodle
  namespaces, globals, database access, cURL, or debugging. This directory is
  ignored and excluded from quality gates, but it violates the standalone
  package boundary and must be removed from the package source tree.
- The all-scheme parser test uses the same synthetic RDF shape for every
  scheme. It proves parser consistency, not authoritative vocabulary
  compatibility. Real fixtures or bounded endpoint qualification are still
  needed for each retrieval mode.

### Next vocabulary and structure tasks

The next implementation pass should address these tasks in order:

1. Remove the copied Moodle tree from the standalone package and keep legacy
   comparison code only in the Moodle repository or a separate migration
   fixture location. Restore lint and static analysis coverage for all package
   source files.
2. Add explicit DCF skill support to the package model and tests, including
   scheme membership and serialization of `relatedSkills`.
3. Add a vocabulary definition/coverage test that classifies every registered
   scheme as model-enforced, search-backed, snapshot-backed, or intentional
   metadata-only. Resolve the unused `ACCREDITATION` entry rather than leaving
   it ambiguous.
4. Compare each legacy convenience factory and special retrieval rule with the
   new provider API. Preserve only format-owned behavior in the package; keep
   Moodle competency/database lookup, cache APIs, and badge mappings in the
   Moodle adapter.
5. Replace the synthetic all-scheme parser-only confidence with focused
   authoritative fixtures and bounded live checks for the schemes that support
   them. Large vocabularies must remain paged and must never be fully loaded
   during validation.
6. Continue the project-structure cleanup by keeping ELM model/value objects,
   vocabulary discovery/parsing, profile validation, serialization, and Moodle
   mapping as separate ownership areas without introducing framework types into
   the package.

Completion criteria for this work are: no Moodle references below package
`src/`, every registered scheme has an explicit ownership classification, DCF
skills are validated, legacy format-owned factories have deliberate
replacements or documented removal decisions, and each supported retrieval
mode has a representative parsing test.

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

Badge construction is now owned by `classes/badge_credential_mapper.php`.
It injects database and configuration boundaries and delegates ELM model
construction to the published package:

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

### Step 4: Build the Moodle mapper (complete for the badge path)

- Replace `credential::fromBadge()` with a Moodle-side mapper. Complete.
- Read badge, user, awarding criteria, settings, and files in the mapper.
- Pass plain values or package DTOs into the ELM package.
- Preserve Moodle error handling and existing delivery-details behavior.

**Exit criteria:** the focused Docker mapper test produces validated ELM output
through the mapper, with no Moodle code inside the ELM package. Issuer,
media/file, and full delivery mapping remain follow-up work.

### Step 5: Add package and integration tests (current slice complete)

- Add required-field and profile validation tests.
- Add deterministic serialization fixtures.
- Add localized-string and date tests.
- Add optional-field and identifier tests.
- Add Moodle mapper tests with controlled badge and user fixtures.
- Verify the serialized result can enter the DSS/CSC signer unchanged.
- Add semantic fixture comparison tests that distinguish equivalent ELM data
  from intentional legacy wrapper, date, context, and shape differences.

**Exit criteria:** package tests run without Moodle, and the current Moodle
integration test covers the badge mapping boundary. Full DSS/CSC handoff and
semantic fixture comparison remain open.

### Step 6: Publish and integrate (package integration complete)

- Add package README, changelog, license, CI, and Composer metadata.
- Test a clean Composer install and publish a first tagged release. Complete
  as `isy-thl/european-learning-model:v0.1.0`.
- Add the package as a plugin-local Composer dependency. Complete.
- Remove duplicated ELM implementation only after the replacement passes the
  Moodle test suite. Complete for the removed legacy credential tree and test.
- Verify the vocabulary provider can serve both Moodle mapper choices and a
  future trainer-facing credential builder without duplicating concept lookup
  or caching logic.

**Exit criteria:** a plain PHP consumer and Moodle both use the published ELM
package, while DSS/CSC signing remains a separate dependency. Remaining
release and mapping gaps are listed in the current assessment above.

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
- The obsolete Moodle credential implementation and test have been removed.
- A plain PHP application can build equivalent ELM JSON.
- The output can be handed to `isy-thl/dss-csc-signing` without format-specific
  changes in the signing package.
