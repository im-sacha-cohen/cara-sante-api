<?php

namespace App\Entity;

use IntlDateFormatter;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ["ROLE_USER"];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=DetectionTest::class, mappedBy="user")
     */
    private $detectionTests;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="users")
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="createdBy")
     */
    private $users;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFirstConnection = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDesactivated = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $desactivatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class)
     */
    private $desactivatedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\OneToMany(targetEntity=UserExport::class, mappedBy="dataFrom", orphanRemoval=true)
     */
    private $userExports;

    public function __construct()
    {
        $this->detectionTests = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->userExports = new ArrayCollection();
    }

    public function jsonSerialize(): array {
        $lastLoginFrench = $this->getLastLogin() === null ? : IntlDateFormatter::formatObject($this->getLastLogin(), IntlDateFormatter::RELATIVE_MEDIUM, 'fr');

        return array(
            'id' => $this->getId(),
            'ref' => $this->getRef(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'mail' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'roles' => $this->getRoles(),
            'createdAt' => date_format($this->getCreatedAt(), 'd/m/Y H:s'),
            'createdAtFrench' => $this->getCreatedAt() !== null ? IntlDateFormatter::formatObject($this->getCreatedAt(), IntlDateFormatter::RELATIVE_MEDIUM, 'fr') : null,
            'createdBy' => $this->getCreatedBy() !== null ? $this->getCreatedBy()->jsonSerializeLight() : null,
            'isFirstConnection' => $this->getIsFirstConnection(),
            'isDesactivated' => $this->getIsDesactivated(),
            'desactivatedAt' => $this->getDesactivatedAt(),
            'desactivatedBy' => $this->getDesactivatedBy() === null ? null : $this->getDesactivatedBy()->jsonSerializeLight(),
            'lastLoginFrench' => $this->getLastLogin() !== null ? $lastLoginFrench : null,
            'detectionTests' => $this->getDetectionTestsSerialized(),
            'totalInvoiced' => $this->calculateTotalInvoiced(),
            'fullName' => $this->getFirstName() . ' ' . $this->getLastName()
        );
    }

    public function jsonSerializeLight(): array {
        $lastLoginFrench = $this->getLastLogin() === null ? : IntlDateFormatter::formatObject($this->getLastLogin(), IntlDateFormatter::RELATIVE_MEDIUM, 'fr');

        return array(
            'id' => $this->getId(),
            'ref' => $this->getRef(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'mail' => $this->getEmail(),
            'roles' => $this->getRoles(),
            'isFirstConnection' => $this->getIsFirstConnection(),
            'isDesactivated' => $this->getIsDesactivated(),
            'desactivatedAt' => $this->getDesactivatedAt(),
            'lastLoginFrench' => $this->getLastLogin() !== null ? $lastLoginFrench : null,
            'fullName' => $this->getFirstName() . ' ' . $this->getLastName()
        );
    }

    public function jsonSerializeUltraLight(): array {
        return array(
            'id' => $this->getId(),
            'ref' => $this->getRef(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'fullName' => $this->getFirstName() . ' ' . $this->getLastName()
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string|null $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = ucfirst(strtolower($firstName));

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = strtoupper($lastName);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|DetectionTest[]
     */
    public function getDetectionTests(): Collection
    {
        return $this->detectionTests;
    }

    public function getDetectionTestsSerialized(): array {
        $detectionTests = $this->getDetectionTests();
        $detectionTestsSerialized = [];

        foreach($detectionTests as $detectionTest) {
            $detectionTestsSerialized[] = $detectionTest->jsonSerializeLight();
        }

        return $detectionTestsSerialized;
    }

    public function addDetectionTest(DetectionTest $detectionTest): self
    {
        if (!$this->detectionTests->contains($detectionTest)) {
            $this->detectionTests[] = $detectionTest;
            $detectionTest->setUser($this);
        }

        return $this;
    }

    public function removeDetectionTest(DetectionTest $detectionTest): self
    {
        if ($this->detectionTests->removeElement($detectionTest)) {
            // set the owning side to null (unless already changed)
            if ($detectionTest->getUser() === $this) {
                $detectionTest->setUser(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string|null $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedBy(): ?self
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?self $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCreatedBy($this);
        }

        return $this;
    }

    public function removeUser(self $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCreatedBy() === $this) {
                $user->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getIsFirstConnection(): ?bool
    {
        return $this->isFirstConnection;
    }

    public function setIsFirstConnection(bool $isFirstConnection): self
    {
        $this->isFirstConnection = $isFirstConnection;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string|null $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getIsDesactivated(): ?bool
    {
        return $this->isDesactivated;
    }

    public function setIsDesactivated(bool $isDesactivated): self
    {
        $this->isDesactivated = $isDesactivated;

        return $this;
    }

    public function getDesactivatedAt(): ?\DateTimeInterface
    {
        return $this->desactivatedAt;
    }

    public function setDesactivatedAt(?\DateTimeInterface $desactivatedAt): self
    {
        $this->desactivatedAt = $desactivatedAt;

        return $this;
    }

    public function getDesactivatedBy(): ?self
    {
        return $this->desactivatedBy;
    }

    public function setDesactivatedBy(?self $desactivatedBy): self
    {
        $this->desactivatedBy = $desactivatedBy;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return int
     */
    public function calculateTotalInvoiced(): int {
        $detectionTests = $this->getDetectionTestsSerialized();
        $invoiced = 0;

        if (count($detectionTests) > 0) {
            foreach($detectionTests as $detectionTest) {
                if ($detectionTest['isInvoiced']) {
                    $invoiced = $invoiced + 1;
                }
            }
        }

        return $invoiced;
    }

    /**
     * @return Collection<int, UserExport>
     */
    public function getUserExports(): Collection
    {
        return $this->userExports;
    }

    public function addUserExport(UserExport $userExport): self
    {
        if (!$this->userExports->contains($userExport)) {
            $this->userExports[] = $userExport;
            $userExport->setDataFrom($this);
        }

        return $this;
    }

    public function removeUserExport(UserExport $userExport): self
    {
        if ($this->userExports->removeElement($userExport)) {
            // set the owning side to null (unless already changed)
            if ($userExport->getDataFrom() === $this) {
                $userExport->setDataFrom(null);
            }
        }

        return $this;
    }
}
