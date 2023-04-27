<?php

namespace App\Entity;

use IntlDateFormatter;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserExportRepository;

/**
 * @ORM\Entity(repositoryClass=UserExportRepository::class)
 */
class UserExport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $requestedBy;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="userExports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataFrom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $requestedAt;

    /**
     * @ORM\Column(type="text")
     */
    private $filePath;

    public function jsonSerialize(): array {
        $requestedAt = $this->getRequestedAt() === null ? : IntlDateFormatter::formatObject($this->getRequestedAt(), IntlDateFormatter::RELATIVE_MEDIUM, 'fr');

        return array(
            'id' => $this->getId(),
            'requestedBy' => $this->getRequestedBy()->jsonSerializeUltraLight(),
            'dataFrom' => $this->getDataFrom()->jsonSerializeUltraLight(),
            'requestedAt' => $requestedAt,
            'filePath' => $this->getFilePath()
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedBy(): ?Users
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(?Users $requestedBy): self
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }

    public function getDataFrom(): ?Users
    {
        return $this->dataFrom;
    }

    public function setDataFrom(?Users $dataFrom): self
    {
        $this->dataFrom = $dataFrom;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }
}