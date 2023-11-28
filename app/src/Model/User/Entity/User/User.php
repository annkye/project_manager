<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;



class User
{
    private const STATUS_WAIT = 'wait';
    private const STATUS_ACTIVE = 'active';

    /**
     * @var Id
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var Email|null
     */
    private $email;
    /**
     * @var string|null
     */
    private $passwordHash;

    /**
     * @var string|null
     */
    private $confirmToken;

    /**
     * @var ResetToken|null
     */
    private $resetToken;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Role
     */
    private $role;

    public function __construct(Id $id, \DateTimeImmutable $date, Email $email, string $hash, string $token)
    {
        $this->id = $id;
        $this->date = $date;
        $this->email = $email;
        $this->role = Role::user();
        $this->passwordHash = $hash;
        $this->confirmToken = $token;
        $this->status = self::STATUS_WAIT;
    }

    public function confirmSignUp(): void
    {
        if (!$this->IsWait()){
            throw new \DomainException('Такой пользователь уже подтверждён.');
        }

        $this->status = self::STATUS_ACTIVE;
        $this->confirmToken = null;
    }

    public function requestPasswordReset(ResetToken $token, \DateTimeImmutable $date): void
    {
        if (!$this->isActive()){
            throw new \DomainException('Пользователь не подтверждён.');
        }
        if (!$this->email){
            throw new \DomainException('Email не определён.');
        }
        if ($this->resetToken && !$this->resetToken->isExpiredTo($date)){
            throw new \DomainException('Сброс пароля уже запрошен.');
        }
        $this->resetToken = $token;
    }

    public function passwordReset(\DateTimeImmutable $date, string $hash): void
    {
        if (!$this->resetToken){
            throw new \DomainException('Сброс пароля не запрошен.');
        }
        if ($this->resetToken->isExpiredTo($date)){
            throw new \DomainException('Сброс пароля истёк.');
        }
        $this->passwordHash = $hash;
        $this->resetToken = null;
    }

    public function changeRole(Role $role): void
    {
        if ($this->role->isEqual($role)){
            throw new \DomainException('Такая роль уже присвоена.');
        }

        $this->role = $role;
    }

    public function isWait (): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive (): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getId (): Id
    {
        return $this->id;
    }

    public function getDate (): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getEmail (): Email
    {
        return $this->email;
    }

    public function getPasswordHash (): string
    {
        return $this->passwordHash;
    }

    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    public function getResetToken(): ?ResetToken
    {
        return $this->resetToken;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}