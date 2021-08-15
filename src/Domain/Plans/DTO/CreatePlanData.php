<?php

namespace Domain\Plans\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class CreatePlanData extends DataTransferObject
{
    public string $name;
    public string $price;
    public string $storage;
    public string $interval;
    public string $description;

    public static function fromRequest($request): self
    {
        return new self([
            'name'        => $request->input('name'),
            'price'       => $request->input('price'),
            'storage'     => $request->input('storage'),
            'interval'    => $request->input('interval'),
            'description' => $request->input('description'),
        ]);
    }
}
