<?php

namespace App\Event;


use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserCreatedEvent extends Event
{
	const NAME = 'user.created';

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
