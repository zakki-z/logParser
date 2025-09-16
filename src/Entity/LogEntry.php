<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[ORM\Entity]
#[ORM\Table(name: 'log_entries')]
class LogEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100)]
    private ?string $channel = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $information = null;

    #[ORM\ManyToOne(targetEntity: File::class, fetch: 'LAZY', inversedBy: 'logEntries')]
    #[ORM\JoinColumn(nullable: true)]
    private ?File $file = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): static
    {
        $this->information = $information;
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
