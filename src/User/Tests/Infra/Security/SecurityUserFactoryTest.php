<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\User\Infra\InMemory\Repository\UserRepository;
use MsgPhp\User\Infra\PHPUnit\UserEntityTrait;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Infra\Security\SecurityUserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

final class SecurityUserFactoryTest extends TestCase
{
    use UserEntityTrait;

    public function testIsAuthenticated()
    {
        $this->assertFalse($this->createFactory()->isAuthenticated());
        $this->assertFalse($this->createFactory('foo@bar.baz')->isAuthenticated());
        $this->assertFalse($this->createFactory('foo@bar.baz', ['ROLE_USER'])->isAuthenticated());
        $this->assertFalse($this->createFactory(new SecurityUser($this->createUser('foo@bar.baz')))->isAuthenticated());
        $this->assertTrue($this->createFactory(new SecurityUser($this->createUser('foo@bar.baz')), ['ROLE_USER'])->isAuthenticated());
    }

    public function testGetUserId()
    {
        $factory = $this->createFactory(new SecurityUser($user = $this->createUser('foo@bar.baz')), ['ROLE_USER']);

        $this->assertSame($user->getId(), $factory->getUserId());
    }

    public function testGetUserIdWithNoToken()
    {
        $this->expectException(TokenNotFoundException::class);

        $this->createFactory()->getUserId();
    }

    public function testGetUserIdWithUnauthenticatedToken()
    {
        $this->expectException(AuthenticationExpiredException::class);

        $this->createFactory('foo@bar.baz')->getUserId();
    }

    public function testGetUserIdWithUnsupportedUser()
    {
        $this->expectException(UnsupportedUserException::class);

        $this->createFactory('foo@bar.baz', ['ROLE_USER'])->getUserId();
    }

    public function testGetUser()
    {
        $repository = new UserRepository([$user = $this->createUser('foo@bar.baz')]);

        $this->assertSame($user, $this->createFactory(new SecurityUser($user), ['ROLE_USER'], $repository)->getUser());
    }

    public function testGetUnknownUser()
    {
        $this->expectException(UsernameNotFoundException::class);

        $this->createFactory(new SecurityUser($user = $this->createUser('foo@bar.baz')), ['ROLE_USER'])->getUser();
    }

    private function createFactory($securityUser = null, array $roles = [], UserRepository $repository = null): SecurityUserFactory
    {
        $storage = new TokenStorage();

        if (null !== $securityUser) {
            $storage->setToken(new UsernamePasswordToken($securityUser, '', 'provider_key', $roles));
        }

        return new SecurityUserFactory($storage, $repository ?? new UserRepository([]));
    }
}
