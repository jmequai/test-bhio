<?php

declare(strict_types=1);

namespace App\Request\Api;

use Symfony\Component\Validator\Constraints\{
    Collection,
    Email,
    Length,
    Type,
};

/**
 *
 */
class AccountEventCreateRequest extends BaseRequest
{
    /**
     * @var int
     */
    public int $accountId;

    /**
     * @var string
     */
    public string $email;

    /**
     * @var string
     */
    public string $password;

    /**
     * @return Collection
     */
    public function rules(): Collection
    {
        return new Collection(
            [
                'accountId' => new Type('int'),
                'email' => new Email(),
                'password' => new Length(['min' => 6]),
            ]
        );
    }
}
