<?php

namespace App\Entity;

use App\Repository\FileHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Logging\OpenTestReporting\Status;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileHistoryRepository::class)]
class FileHistory
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?string $fileName = null;
    #[ORM\Column]
    private ?string $fileSize = null;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploaded_at = null;

    #[ORM\Column(type: "string", enumType: Status::class)]
    private Status $status;
    #[ORM\ManyToOne(targetEntity: File::class, fetch: 'EAGER', inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $file = null;
    public function getId(): ?Uuid
    {
        return $this->id;
    }
    public function getFileName(): ?string
    {
        return $this->fileName;
    }
    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }
    public function getFileSize(): ?string{
        return $this->fileSize;
    }
    public function setFileSize(string $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }
    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploaded_at;
    }
    public function setUploadedAt(\DateTimeImmutable $uploaded_at): static
    {
        $this->uploaded_at = $uploaded_at;
        return $this;
    }
    public function getStatus(): Status
    {
        return $this->status;
    }
    public function setStatus(Status $status): static
    {
        $this->status = $status;
        return $this;
    }
    public function getFile(): ?File
    {
        return $this->file;
    }
    public function setFile(?File $file): static
    {
        $this->file = $file;
        return $this;
    }
}
