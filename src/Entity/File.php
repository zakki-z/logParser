<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $uploaded_at = null;

    #[ORM\Column]
    private ?int $file_size = null;
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'file')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user= null;

    public function getId(): ?int
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
    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploaded_at;
    }

    public function setUploadedAt(\DateTimeImmutable $uploaded_at): static
    {
        $this->uploaded_at = $uploaded_at;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(int $file_size): static
    {
        $this->file_size = $file_size;

        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
