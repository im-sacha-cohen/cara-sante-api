<?php

namespace App\Entity;

use App\Repository\DetectionTestRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
setlocale(LC_TIME, 'fr_FR');

/**
 * @ORM\Entity(repositoryClass=DetectionTestRepository::class)
 */
class DetectionTest
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $testedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInvoiced = false;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="detectionTests", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $filledAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Patient::class, inversedBy="detectionTests", cascade={"persist"})
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=false)
     */
    private $patient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUpdating = false;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class)
     */
    private $updatingBy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isNegative;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startUpdating;

    public function jsonSerialize(): array {
        return array(
            'id' => $this->getId(),
            'ref' => $this->getRef(),
            'patient' => $this->getPatient()->jsonSerializeLight(),
            'testedAt' => $this->getTestedAt(),
            'isNegative' => $this->getIsNegative(),
            'frenchTestedAt' => utf8_encode(strftime('%A %d %B %G - %H:%M', strtotime(date_format($this->getTestedAt(), 'Y-m-d H:i:s')))),
            'isInvoiced' => $this->getIsInvoiced(),
            'filledAt' => $this->getFilledAt(),
            'filledAtFrench' => $this->getFilledAt() !== null ? strftime('%A %d %B %G à %H:%M', strtotime(date_format($this->getFilledAt(), 'Y-m-d H:i:s'))) : null,
            'patient' => $this->getPatient()->jsonSerializeLight(),
            'user' => $this->getUser() === null ? null : $this->getUser()->jsonSerializeLight(),
            'isUpdating' => $this->getIsUpdating(),
            'updatingBy' => $this->getUpdatingBy() !== null ? $this->getUpdatingBy()->jsonSerializeLight() : null
        );
    }

    public function jsonSerializeLight(): array {
        return array(
            'id' => $this->getId(),
            'ref' => $this->getRef(),
            'patient' => $this->getPatient()->jsonSerializeLight(),
            'testedAt' => $this->getTestedAt(),
            'isNegative' => $this->getIsNegative(),
            'frenchTestedAt' => utf8_encode(strftime('%A %d %B %G - %H:%M', strtotime(date_format($this->getTestedAt(), 'Y-m-d H:i:s')))),
            'isInvoiced' => $this->getIsInvoiced(),
            'filledAt' => $this->getFilledAt(),
            'filledAtFrench' => $this->getFilledAt() !== null ? strftime('%A %d %B %G à %H:%M', strtotime(date_format($this->getFilledAt(), 'Y-m-d H:i:s'))) : null,
            'patient' => $this->getPatient()->jsonSerializeLight(),
            'isUpdating' => $this->getIsUpdating(),
            'updatingBy' => $this->getUpdatingBy() !== null ? $this->getUpdatingBy()->jsonSerializeLight() : null
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getTestedAt(): ?DateTime
    {
        return $this->testedAt;
    }

    public function setTestedAt(DateTime $testedAt): self
    {
        $this->testedAt = $testedAt;

        return $this;
    }

    public function getIsInvoiced(): ?bool
    {
        return $this->isInvoiced;
    }

    public function setIsInvoiced(bool $isInvoiced): self
    {
        $this->isInvoiced = $isInvoiced;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilledAt(): ?DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(?DateTime $filledAt): self
    {
        $this->filledAt = $filledAt;

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

    public function getIsUpdating(): ?bool
    {
        return $this->isUpdating;
    }

    public function setIsUpdating(bool $isUpdating): self
    {
        $this->isUpdating = $isUpdating;

        return $this;
    }

    public function getUpdatingBy(): ?Users
    {
        return $this->updatingBy;
    }

    public function setUpdatingBy(?Users $updatingBy): self
    {
        $this->updatingBy = $updatingBy;

        return $this;
    }

    public function getIsNegative(): ?bool
    {
        return $this->isNegative;
    }

    public function setIsNegative(?bool $isNegative): self
    {
        $this->isNegative = $isNegative;

        return $this;
    }

    public function getStartUpdating(): ?\DateTimeInterface
    {
        return $this->startUpdating;
    }

    public function setStartUpdating(?\DateTimeInterface $startUpdating): self
    {
        $this->startUpdating = $startUpdating;

        return $this;
    }
}
