<?php

namespace App\DTOs\Identity;

use Illuminate\Http\UploadedFile;

class AvatarData
{
    public function __construct(
        public readonly string $type, // 'custom' | 'icon'
        public readonly ?string $source, // String para iconos
        public readonly ?UploadedFile $file, // Archivo para custom
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            type: $request->validated('avatar_type'),
            source: $request->validated('avatar_source'),
            file: $request->file('avatar_file'),
        );
    }
}