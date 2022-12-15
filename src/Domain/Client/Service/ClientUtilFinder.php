<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientDropdownValuesData;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;

class ClientUtilFinder
{
    public function __construct(
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
    ) {
    }

    /**
     * Find all dropdown values for a client.
     *
     * @return ClientDropdownValuesData
     */
    public function findClientDropdownValues(): ClientDropdownValuesData
    {
        return new ClientDropdownValuesData(
            $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName(),
            $this->userNameAbbreviator->abbreviateUserNames($this->userFinderRepository->findAllUsers()),
        );
    }
}
