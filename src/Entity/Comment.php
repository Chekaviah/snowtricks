<?php

namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\Trick;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Comment
 *
 * @author Mathieu GUILLEMINOT <guilleminotm@gmail.com>
 *
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
	/**
	 * @var int
     *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var \DateTime
     *
	 * @ORM\Column(name="date", type="datetime")
	 */
	private $date;

	/**
	 * @var string
     *
	 * @ORM\Column(name="content", type="text")
	 */
	private $content;

	/**
     * @var User
     *
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
     * @var Trick
     *
	 * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="comments")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $trick;

    /**
     * Comment constructor.
     */
	public function __construct()
	{
		$this->date = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate(): DateTime
	{
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content)
	{
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent(): ?string
	{
		return $this->content;
	}

	/**
	 * @param Trick $trick
	 */
	public function setTrick(Trick $trick)
	{
		$this->trick = $trick;
	}

	/**
	 * @return Trick
	 */
	public function getTrick(): ?Trick
	{
		return $this->trick;
	}

	/**
	 * @param User $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}
}
