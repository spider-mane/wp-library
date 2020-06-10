<?php

namespace WebTheory\TaxRoles;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;

class StructuralTaxonomyDataManager implements FieldDataManagerInterface
{
    /**
     * @var StructuralTaxonomy
     */
    protected $structuralTaxonomy;

    /**
     *
     */
    public function __construct(StructuralTaxonomy $structuralTaxonomy)
    {
        $this->structuralTaxonomy = $structuralTaxonomy;
    }

    /**
     *
     */
    public function getCurrentData(ServerRequestInterface $request)
    {
        $this->structuralTaxonomy;
    }

    /**
     *
     */
    public function handleSubmittedData(ServerRequestInterface $request, $data): bool
    {
        $this->structuralTaxonomy;
    }
}
