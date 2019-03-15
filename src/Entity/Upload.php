<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class Upload {
	/**
	 * @Assert\NotBlank(message="File should not be blank.")
	 * @Assert\File(
	 *     mimeTypes={"image/jpeg", "image/png", "image/gif", "application/x-gzip", "application/zip"},
	 *     maxSize="1074000000"
	 * )
	 *
	 * @var UploadedFile
	 */
	private $file;

	public function getFile (): ?UploadedFile {
		return $this->file;
	}

	public function setFile ( UploadedFile $file = NULL ): self {
		$this->file = $file;

		return $this;
	}
}
