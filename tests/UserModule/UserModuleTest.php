<?php

namespace App\Tests\UserModule;

use App\UserModule\Dto\UserCreateDto;
use App\UserModule\Dto\UserUpdateDto;
use App\UserModule\Event\UserUpdatedEvent;
use App\UserModule\Exception\UserNotFoundException;
use App\UserModule\Exception\ValidationException;
use App\UserModule\Impl\InMemory\Hydrator;
use App\UserModule\Impl\InMemory\Memory;
use App\UserModule\Impl\InMemory\UserRepositoryRead;
use App\UserModule\Impl\InMemory\UserRepositoryWrite;
use App\UserModule\Validation\UserValidator;
use App\UserModule\Validation\ValidatorsCollection;
use DateTimeImmutable;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class UserModuleTest extends TestCase
{
    private UserRepositoryRead $sutRead;
    private UserRepositoryWrite $sutWrite;
    /** @var MockObject&EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $memory = new Memory([
            12 => ['name' => 'name8chars1', 'email' => 'email1@example.com', 'created' => new DateTimeImmutable(), 'deleted' => null, 'notes' => 'notes 1'],
            13 => ['name' => 'name8chars2', 'email' => 'email2@example.com', 'created' => new DateTimeImmutable(), 'deleted' => null, 'notes' => 'notes 2'],
        ]);
        $hydrator = new Hydrator();
        $read = new UserRepositoryRead($memory, $hydrator);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $validatorsCollection = new ValidatorsCollection($read);
        $validator = new UserValidator($validatorsCollection);
        $write = new UserRepositoryWrite($memory, $this->eventDispatcher, $validator, $hydrator);

        $this->sutRead = $read;
        $this->sutWrite = $write;
    }

    public function testReadNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->sutRead->find(15);
    }

    public function testReadFound(): void
    {
        $result = $this->sutRead->find(12);
        $this->assertSame('name8chars1', $result->name);
        $result = $this->sutRead->find(13);
        $this->assertSame('name8chars2', $result->name);
    }

    /**
     * @dataProvider provideCreate
     */
    public function testCreate(
        bool $isSuccess,
        UserCreateDto $data,
    ): void {
        if (!$isSuccess) {
            $this->expectException(ValidationException::class);
        }
        $result = $this->sutWrite->create($data);
        if ($isSuccess) {
            $this->assertSame($data->name, $result->name);
            $this->assertSame($data->email, $result->email);
            $this->assertEquals($data->created, $result->created);
            $this->assertEquals($data->deleted, $result->deleted);
            $this->assertSame($data->notes, $result->notes);
        }
    }

    /**
     * @return iterable<array{bool, UserCreateDto}>
     */
    public function provideCreate(): iterable
    {
        yield 'failure: duplicate name' => [false, new UserCreateDto('name8chars1', 'email@example.com', new DateTimeImmutable(), null, 'notes')];
        yield 'failure: duplicate email' => [false, new UserCreateDto('name8charsunique', 'email2@example.com', new DateTimeImmutable(), null, 'notes')];
        yield 'failure: wrong deleted date' => [false, new UserCreateDto('name8chars111', 'email111@example.com', new DateTimeImmutable(), new DateTimeImmutable('-1 day'), 'notes')];

        yield 'success 1' => [true, new UserCreateDto('name8charsxxx', 'emailxxx@example.com', new DateTimeImmutable(), null, 'notes')];
        yield 'success 2' => [true, new UserCreateDto('name8charsyyy', 'emailyyy@example.com', new DateTimeImmutable(), new DateTimeImmutable('+1 day'), 'notes')];
    }

    /**
     * @dataProvider provideUpdate
     */
    public function testUpdate(
        bool $isSuccess,
        int $id,
        UserUpdateDto $data,
    ): void {
        if (!$isSuccess) {
            $this->expectException(ValidationException::class);
        } else {
            $this->eventDispatcher->expects($this->once())->method('dispatch')->with(new IsInstanceOf(UserUpdatedEvent::class));
        }
        $result = $this->sutWrite->update($id, $data);
        if ($isSuccess) {
            $changes = $data->getChanges();
            foreach ($changes as $key => $value) {
                if ($key === 'name') {
                    $this->assertSame($value, $result->name);
                } elseif ($key === 'email') {
                    $this->assertSame($value, $result->email);
                } elseif ($key === 'created') {
                    $this->assertEquals($value, $result->created);
                } elseif ($key === 'deleted') {
                    $this->assertEquals($value, $result->deleted);
                } elseif ($key === 'notes') {
                    $this->assertSame($value, $result->notes);
                }
            }
        }
    }

    /**
     * @return iterable<array{bool, int, UserUpdateDto}>
     */
    public function provideUpdate(): iterable
    {
        yield 'failure: duplicate name' => [false, 13, (new UserUpdateDto())->withName('name8chars1')];
        yield 'failure: duplicate email' => [false, 13, (new UserUpdateDto())->withEmail('email1@example.com')];
        yield 'failure: wrong deleted date' => [false, 13, (new UserUpdateDto())->withDeleted(new DateTimeImmutable('-1 day'))];

        yield 'success 1' => [true, 13, (new UserUpdateDto())->withName('name8charsxxx')];
        yield 'success 2' => [true, 13, (new UserUpdateDto())->withEmail('email1xxx@example.com')];
        yield 'success 3' => [true, 13, (new UserUpdateDto())->withDeleted(new DateTimeImmutable('+1 sec'))->withNotes('deleted by admin')];

        yield 'success 4 same name and email' => [true, 13, (new UserUpdateDto())->withName('name8chars2')->withEmail('email2@example.com')->withDeleted(null)];
    }
}
