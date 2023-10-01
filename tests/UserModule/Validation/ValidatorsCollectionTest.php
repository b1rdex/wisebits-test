<?php

namespace App\Tests\UserModule\Validation;

use App\UserModule\Dto\UserViewDto;
use App\UserModule\UserRepositoryRead;
use App\UserModule\Validation\ValidatorsCollection;
use PHPUnit\Framework\TestCase;

class ValidatorsCollectionTest extends TestCase
{
    private UserRepositoryRead $userReadRepository;

    protected function setUp(): void
    {
        $this->userReadRepository = $this->createMock(UserRepositoryRead::class);

        $this->userReadRepository
            ->method('findBy')
            ->willReturn([]);
    }

    public function testGetNameValidators(): void
    {
        $collection = new ValidatorsCollection($this->userReadRepository);
        $nameValidators = $collection->getValidators()['name'];

        // Validate alphanumerics
        $this->assertTrue($nameValidators['Must consist of a-z and 0-9 only']('Test1234'));
        $this->assertFalse($nameValidators['Must consist of a-z and 0-9 only']('Test!1234'));

        // Validate length
        $this->assertTrue($nameValidators['Must be more than 8 characters']('Test123456'));
        $this->assertFalse($nameValidators['Must be more than 8 characters']("Test123"));

        // Check against banned words
        $this->assertFalse($nameValidators['Must not use banned words']('some'));
        $this->assertFalse($nameValidators['Must not use banned words']('banned'));
        $this->assertFalse($nameValidators['Must not use banned words']('words'));
        $this->assertTrue($nameValidators['Must not use banned words']('allowedWord'));

        // Validate uniqueness
        $this->assertTrue($nameValidators['Must be unique']("TestUnique", null));
    }

    public function testGetEmailValidators(): void
    {
        $collection = new ValidatorsCollection($this->userReadRepository);
        $emailValidators = $collection->getValidators()['email'];

        // Validate email format
        $this->assertTrue($emailValidators['Must be valid email address']('test@example.com'));
        $this->assertFalse($emailValidators['Must be valid email address']('test@example'));

        // Check against banned domains
        $this->assertFalse($emailValidators['Must not use banned domains']('test@some.com'));
        $this->assertFalse($emailValidators['Must not use banned domains']('test@banned.com'));
        $this->assertFalse($emailValidators['Must not use banned domains']('test@domains.com'));
        $this->assertTrue($emailValidators['Must not use banned domains']('test@alloweddomain.com'));

        // Validate uniqueness
        $this->assertTrue($emailValidators['Must be unique']('unique@example.com', null));
    }

    public function testGetDeletedValidators(): void
    {
        $collection = new ValidatorsCollection($this->userReadRepository);
        $deletedValidators = $collection->getValidators()['deleted'];

        // Validate 'deleted' is greater than 'created'
        $now = new \DateTimeImmutable();

        $future = $now->modify('+1 day');
        $this->assertTrue($deletedValidators['Must be greater than created']($future, null, ['created' => $now]));
        $this->assertTrue($deletedValidators['Must be greater than created']($future, new UserViewDto(1, '', '', $now, null, null), []));

        $past = $now->modify('-1 day');
        $this->assertFalse($deletedValidators['Must be greater than created']($past, null, ['created' => $now]));
        $this->assertFalse($deletedValidators['Must be greater than created']($past, new UserViewDto(1, '', '', $now, null, null), []));
    }
}
