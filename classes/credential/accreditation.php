<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\eqf_concept;
use local_isycredentials\credential\concept\accreditation_status_concept;
use local_isycredentials\credential\concept\iscedf_concept;
use local_isycredentials\credential\concept\credential_type_concept;
use local_isycredentials\credential\legal_identifier;
use local_isycredentials\credential\identifier;
use local_isycredentials\credential\web_resource;
use local_isycredentials\credential\organisation;
use local_isycredentials\credential\qualification;
use local_isycredentials\credential\concept\atu_concept;
use local_isycredentials\credential\concept\accreditation_decision_concept;

/**
 * Class accreditation
 * 
 * The quality assurance or licensing of an organisation or a qualification. An accreditation instance can be used to specify information about: (1) the quality assurance and/or licensing of an organisation, (2) the quality assurance and/or licensing of an organisation with respect to a specific qualification.
 *
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#accreditation 
 */
class accreditation extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'Accreditation';

    /**
     * A Unix timestamp representig the date of formal issuance of the resource.
     */
    public ?string $dateIssued = null;

    /**
     * A Unix timestamp representig the date on which the resource was changed.
     */
    public ?string $dateModified = null;

    /**
     * A Unix timestamp representig the date (often a range) of validity of a resource.
     */
    public ?string $dateValid = null;

    /**
     * An account of the resource.
     */
    public ?array $description = null;

    /**
     * A name given to the resource.
     */
    public array $title;

    /**
     * The European Qualifications Framework levels for which an organisation is accredited to provide studies. If provided, the value must come from the European Qualifications Framework list (http://publications.europa.eu/resource/dataset/european-qualification-framework)., The European Qualification Framework level for which the accreditation is valid.It should be provided using EQF controlled vocabulary.
     * 
     * @var eqf_concept[]|null
     */
    public ?array $accreditedForEQFLevel = null;

    /**
     * The field of education for which the accreditation is valid., The field of education for which an organisation is accredited to provide studies. If provided, the value must come from the International Standard Classification of Education http://publications.europa.eu/resource/dataset/international-education-classification).
     *
     * @var iscedf_concept[]|null
     */
    public ?array $accreditedForThematicArea = null;

    /**
     * The administrative territories in which the accreditation decision is valid. If provided, the value must come from the Administrative territorial unit Named Authority list (http://publicati ons.europa.eu/resource/dataset/atu)., The jurisdiction for which the entitlement is valid (the region or country)., The jurisdiction for which the accreditation is valid.
     *
     * @var atu_concept[]|null
     */
    public ?array $accreditedInJurisdiction = null;

    /**
     * The legal person who is issuing the accreditation decision., The Quality Assuring Authority.(i.e., assurer).
     */
    public organisation $accreditingAgent;

    /**
     * The decision issued by the quality assuring authority. The value provided should come from a controlled vocabulary (e.g. http://publications.europa.eu/resource/dataset/accreditation-decision)., The Quality Decision issued by the Quality Assuring Authority.
     */
    public ?accreditation_decision_concept $decision = null;

    /**
     * A unix timestamp representing the the date when the accreditation decision expires or has expired., The date when the accreditation expires or was expired.
     */
    public ?string $expiryDate = null;

    /**
     * A homepage for some thing.
     *
     * @var web_resource[]|null
     */
    public ?array $homepage = null;

    /**
     * Links a resource to an adms:Identifier class.
     * 
     * @var identifier[]|legal_identifier[]|null
     */
    public ?array $identifier = null;

    /**
     * The landing page of the accreditation.
     *
     * @var web_resource[]|null
     */
    public ?array $landingPage = null;

    /**
     * The credential type for which the accreditation is valid. If provided, the value must come from the Credential type controlled vocabulary (http://publications.europa.eu/resource/dataset/credential)., The credential type for which the accreditation is valid. It MUST be provided using the Credential Type controlled vocabulary.
     *
     * @var credential_type_concept[]|null
     */
    public ?array $limitCredentialType = null;

    /**
     * The legal person whose activities are being accredited., The organisation whose activities are being accredited.
     *
     * @var organisation[]|null
     */
    public ?array $organisation = null;

    /**
     * The qualification that was accredited., A specification of an assessment and validation process which is obtained when a competent authority determines that an individual has achieved learning outcomes to given standards that has been accredited.
     */
    public ?qualification $qualificationAccredited = null;

    /**
     * A public web document containing the report of the accreditation.
     */
    public ?web_resource $report = null;

    /**
     * A Unix timestamp representing the date when the accreditation has to be re-viewed., The date when the accreditation has to be reviewed.
     */
    public ?string $reviewDate = null;

    /**
     * The publication status of the quality assurance or licensing of an organisation or a qualification. If provided, the value must come from the Accredication status controlled vocabulary (http://publications.europa.eu/resource/dataset/accreditation-status)., The status. It can be the status of the verification check, Entitlement specification etc
     */
    public ?accreditation_status_concept $status = null;

    public function __construct(array $title, string $type, organisation $accreditingAgent) {
        $this->title = $title;
        $this->type = $type;
        $this->accreditingAgent = $accreditingAgent;
    }

    public function withDateIssued(string $dateIssued): self {
        $this->dateIssued = $dateIssued;
        return $this;
    }

    public function withDateModified(string $dateModified): self {
        $this->dateModified = $dateModified;
        return $this;
    }

    public function withDateValid(string $dateValid): self {
        $this->dateValid = $dateValid;
        return $this;
    }

    public function withDescription(array $description): self {
        $this->description = $description;
        return $this;
    }

    public function withAccreditedForEQFLevel(array $accreditedForEQFLevel): self {
        // Check if the array contains only eqf_concept objects
        foreach ($accreditedForEQFLevel as $eqf) {
            if (!($eqf instanceof eqf_concept)) {
                throw new \InvalidArgumentException('The accreditedForEQFLevel array must contain only eqf_concept objects');
            }
        }
        $this->accreditedForEQFLevel = $accreditedForEQFLevel;
        return $this;
    }

    public function withAccreditedForThematicArea(array $accreditedForThematicArea): self {
        // Check if the array contains only iscedf_concept objects
        foreach ($accreditedForThematicArea as $iscedf) {
            if (!($iscedf instanceof iscedf_concept)) {
                throw new \InvalidArgumentException('The accreditedForThematicArea array must contain only iscedf_concept objects');
            }
        }
        $this->accreditedForThematicArea = $accreditedForThematicArea;
        return $this;
    }

    public function withAccreditedInJurisdiction(array $accreditedInJurisdiction): self {
        // Check if the array contains only atu_concept objects
        foreach ($accreditedInJurisdiction as $atu) {
            if (!($atu instanceof atu_concept)) {
                throw new \InvalidArgumentException('The accreditedInJurisdiction array must contain only atu_concept objects');
            }
        }
        $this->accreditedInJurisdiction = $accreditedInJurisdiction;
        return $this;
    }

    public function withDecision(accreditation_decision_concept $decision): self {
        $this->decision = $decision;
        return $this;
    }

    public function withExpiryDate(string $expiryDate): self {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    public function withHomepage(array $homepage): self {
        // Check if the array contains only web_resource objects
        foreach ($homepage as $webResource) {
            if (!($webResource instanceof web_resource)) {
                throw new \InvalidArgumentException('The homepage array must contain only web_resource objects');
            }
        }
        $this->homepage = $homepage;
        return $this;
    }

    public function withIdentifier(array $identifier): self {
        // Check if the array contains only identifier objects
        foreach ($identifier as $id) {
            if (!($id instanceof identifier || $id instanceof legal_identifier)) {
                throw new \InvalidArgumentException('The identifier array must contain only identifier or legal_identifier objects');
            }
        }
        $this->identifier = $identifier;
        return $this;
    }

    public function withLandingPage(array $landingPage): self {
        // Check if the array contains only web_resource objects
        foreach ($landingPage as $webResource) {
            if (!($webResource instanceof web_resource)) {
                throw new \InvalidArgumentException('The landingPage array must contain only web_resource objects');
            }
        }
        $this->landingPage = $landingPage;
        return $this;
    }

    public function withLimitCredentialType(array $limitCredentialType): self {
        // Check if the array contains only credential_type_concept objects
        foreach ($limitCredentialType as $credentialType) {
            if (!($credentialType instanceof credential_type_concept)) {
                throw new \InvalidArgumentException('The limitCredentialType array must contain only credential_type_concept objects');
            }
        }
        $this->limitCredentialType = $limitCredentialType;
        return $this;
    }

    public function withOrganisation(array $organisation): self {
        // Check if the array contains only organisation objects
        foreach ($organisation as $org) {
            if (!($org instanceof organisation)) {
                throw new \InvalidArgumentException('The organisation array must contain only organisation objects');
            }
        }
        $this->organisation = $organisation;
        return $this;
    }

    public function withQualificationAccredited(qualification $qualificationAccredited): self {
        $this->qualificationAccredited = $qualificationAccredited;
        return $this;
    }

    public function withReport(web_resource $report): self {
        $this->report = $report;
        return $this;
    }

    public function withReviewDate(string $reviewDate): self {
        $this->reviewDate = $reviewDate;
        return $this;
    }

    public function withStatus(accreditation_status_concept $status): self {
        $this->status = $status;
        return $this;
    }

    public function toArray(): array {
        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'accreditingAgent' => $this->accreditingAgent->toArray(),
        ];

        if ($this->dateIssued) {
            $data['dateIssued'] = $this->dateIssued;
        }

        if ($this->dateModified) {
            $data['dateModified'] = $this->dateModified;
        }

        if ($this->dateValid) {
            $data['dateValid'] = $this->dateValid;
        }

        if ($this->description) {
            $data['description'] = $this->description;
        }

        if ($this->accreditedForEQFLevel) {
            $data['accreditedForEQFLevel'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->accreditedForEQFLevel);
        }

        if ($this->accreditedForThematicArea) {
            $data['accreditedForThematicArea'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->accreditedForThematicArea);
        }

        if ($this->accreditedInJurisdiction) {
            $data['accreditedInJurisdiction'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->accreditedInJurisdiction);
        }

        if ($this->decision) {
            $data['decision'] = $this->decision;
        }

        if ($this->expiryDate) {
            $data['expiryDate'] = $this->expiryDate;
        }

        if ($this->homepage) {
            $data['homepage'] = $this->homepage;
        }

        if ($this->identifier) {
            $data['identifier'] = $this->identifier;
        }

        if ($this->landingPage) {
            $data['landingPage'] = $this->landingPage;
        }

        if ($this->limitCredentialType) {
            $data['limitCredentialType'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->limitCredentialType);
        }

        if ($this->additionalNotes) {
            $data['additionalNote'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->additionalNotes);
        }

        if ($this->organisation) {
            $data['organisation'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->organisation);
        }

        if ($this->qualificationAccredited) {
            $data['qualificationAccredited'] = $this->qualificationAccredited->toArray();
        }

        if ($this->report) {
            $data['report'] = $this->report->toArray();
        }

        if ($this->reviewDate) {
            $data['reviewDate'] = $this->reviewDate;
        }

        if ($this->status) {
            $data['status'] = $this->status;
        }

        if ($this->supplementaryDocuments) {
            $data['supplementaryDocuments'] = array_map(function ($item) {
                return $item->toArray();
            }, $this->supplementaryDocuments);
        }

        return $data;
    }
}
